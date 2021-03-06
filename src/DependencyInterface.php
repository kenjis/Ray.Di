<?php
/**
 * This file is part of the Ray package.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

interface DependencyInterface
{
    /**
     * Inject dependencies into dependent objects
     *
     * @param Container $container
     *
     * @return mixed
     */
    public function inject(Container $container);

    /**
     * Register dependency to container
     *
     * @param DependencyInterface[] $container
     * @param Bind                  $bind
     */
    public function register(array &$container, Bind $bind);

    /**
     * Set scope
     *
     * @param string $scope
     *
     * @return mixed
     */
    public function setScope($scope);
}
