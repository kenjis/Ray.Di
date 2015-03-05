<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

use Ray\Aop\Compiler;
use Ray\Di\Exception\Untargetted;

class Injector implements InjectorInterface
{
    /**
     * @var string
     */
    private $classDir;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var bool
     */
    private $isUpdated = false;

    /**
     * @param AbstractModule $module
     * @param string         $classDir
     */
    public function __construct(AbstractModule $module = null, $classDir = null)
    {
        $this->classDir = $classDir ?: sys_get_temp_dir();
        $this->container =  $module ? $module->getContainer() : new Container;
        $this->container->weaveAspects(new Compiler($this->classDir));

        // builtin injection
        (new Bind($this->container, 'Ray\Di\InjectorInterface'))->toInstance($this);
    }

    /**
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        try {
            $instance = $this->container->getInstance($interface, $name);
        } catch (Untargetted $e) {
            $this->isUpdated = true;
            $this->bind($interface);
            $instance = $this->getInstance($interface, $name);
        }

        return $instance;
    }

    /**
     * @param string $class
     */
    private function bind($class)
    {
        $bind = new Bind($this->container, $class);
        /** @var $bound Dependency */
        $bound = $bind->getBound();
        $this->container->weaveAspect(new Compiler($this->classDir), $bound)->getInstance($class, Name::ANY);
    }

    /**
     * Return object graph updated
     *
     * @return bool
     */
    public function isUpdated()
    {
        return $this->isUpdated;
    }

    public function __wakeup()
    {
        $this->isUpdated = false;
        spl_autoload_register(
            function ($class) {
                $file = $this->classDir . DIRECTORY_SEPARATOR . $class . '.php';
                if (file_exists($file)) {
                    // @codeCoverageIgnoreStart
                    /** @noinspection PhpIncludeInspection */
                    include $file;
                    // @@codeCoverageIgnoreEnd
                }
            }
        );
    }
}
