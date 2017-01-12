<?php

use Kispiox\Component\RoutingProvider;
use MattFerris\Di\ContainerInterface;
use MattFerris\Configuration\Configuration;
use MattFerris\Configuration\LocatorInterface;
use MattFerris\Configuration\Resources\FileResource;
use MattFerris\Http\Routing\DispatcherInterface;

class RoutingProviderTest extends PHPUnit_Framework_TestCase
{
    public function makeConfiguration($data, $extended = true)
    {
        $configuration = $this->createMock(Configuration::class);

        $configuration->expects($this->once())
            ->method('newInstance')
            ->willReturn($configuration);

        $configuration->expects($this->once())
            ->method('load')
            ->with(['routes.yaml', 'routes.dist.yaml']);

        if ($extended) {
            $resource = $this->createMock(FileResource::class);
            $resource->expects($this->once())
                ->method('getPath')
                ->willReturn('private/config/routes.yaml');

            $locator = $this->createMock(LocatorInterface::class);
            $locator->expects($this->once())
                ->method('locate')
                ->with('routes.yaml')
                ->willReturn($resource);

            $configuration->expects($this->once())
                ->method('getLocator')
                ->willReturn($locator);

            $configuration->expects($this->once())
                ->method('get')
                ->willReturn($data);
        }

        return $configuration;
    }

    public function makeProvider($data = [], $container = null, $configuration = null)
    {
        if (is_null($container)) {
            $container = $this->createMock(ContainerInterface::class);
        }

        if (is_null($configuration)) {
            $configuration = $this->makeConfiguration($data, true);
        }

        return new RoutingProvider($container, $configuration);
    }

    public function testConstruct()
    {
        $config = $this->makeConfiguration([], false);
        $this->makeProvider([], null, $config);
    }

    /**
     * @depends testConstruct
     */
    public function testRoute()
    {
        $routes = [
            'foo' => [
                'path' => '/foo',
                'action' => 'class:action'
            ]
        ];

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('route')
            ->with($routes['foo']['path'], $routes['foo']['action'], null, [], [], 'foo');

        $provider = $this->makeProvider($routes);
        $provider->provides($dispatcher);
    }

    /**
     * @depends testRoute
     * @expectedException RuntimeException
     * @expectedExceptionMessage no "path" in route "foo" defined in private/config/routes.yaml
     */
    public function testRouteWithoutPath()
    {
        $routes = [
            'foo' => [
                'action' => 'class:action'
            ]
        ];

        $dispatcher = $this->createMock(DispatcherInterface::class);

        $provider = $this->makeProvider($routes);
        $provider->provides($dispatcher);
    }

    /**
     * @depends testRoute
     * @expectedException RuntimeException
     * @expectedExceptionMessage no "action" in route "foo" defined in private/config/routes.yaml
     */
    public function testRouteWithoutAction()
    {
        $routes = [
            'foo' => [
                'path' => '/foo'
            ]
        ];

        $dispatcher = $this->createMock(DispatcherInterface::class);

        $provider = $this->makeProvider($routes);
        $provider->provides($dispatcher);
    }

    /**
     * @depends testRoute
     */
    public function testRouteWithMethod()
    {
        $routes = [
            'foo' => [
                'path' => '/foo',
                'action' => 'class:action',
                'method' => 'GET'
            ]
        ];

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('route')
            ->with($routes['foo']['path'], $routes['foo']['action'], 'GET', [], [], 'foo');

        $provider = $this->makeProvider($routes);
        $provider->provides($dispatcher);
    }

    /**
     * @depends testRoute
     * @expectedException RuntimeException
     * @expectedExceptionMessage invalid "method", expecting string in route "foo" defined in private/config/routes.yaml
     */
    public function testRouteWithoutBadMethod()
    {
        $routes = [
            'foo' => [
                'path' => '/foo',
                'action' => 'class:action',
                'method' => []
            ]
        ];

        $dispatcher = $this->createMock(DispatcherInterface::class);

        $provider = $this->makeProvider($routes);
        $provider->provides($dispatcher);
    }

    /**
     * @depends testRoute
     */
    public function testRouteWithHeaders()
    {
        $routes = [
            'foo' => [
                'path' => '/foo',
                'action' => 'class:action',
                'headers' => [ 'Host' => 'example.com' ]
            ]
        ];

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('route')
            ->with($routes['foo']['path'], $routes['foo']['action'], null, $routes['foo']['headers'], [], 'foo');

        $provider = $this->makeProvider($routes);
        $provider->provides($dispatcher);
    }

    /**
     * @depends testRoute
     * @expectedException RuntimeException
     * @expectedExceptionMessage invalid "headers", expecting array in route "foo" defined in private/config/routes.yaml
     */
    public function testRouteWithoutBadHeaders()
    {
        $routes = [
            'foo' => [
                'path' => '/foo',
                'action' => 'class:action',
                'headers' => 'nogood'
            ]
        ];

        $dispatcher = $this->createMock(DispatcherInterface::class);

        $provider = $this->makeProvider($routes);
        $provider->provides($dispatcher);
    }

    /**
     * @depends testRoute
     */
    public function testRouteWithDefaults()
    {
        $routes = [
            'foo' => [
                'path' => '/foo',
                'action' => 'class:action',
                'defaults' => [ 'foo' => 'bar' ]
            ]
        ];

        $dispatcher = $this->createMock(DispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('route')
            ->with($routes['foo']['path'], $routes['foo']['action'], null, [], $routes['foo']['defaults'], 'foo');

        $provider = $this->makeProvider($routes);
        $provider->provides($dispatcher);
    }

    /**
     * @depends testRoute
     * @expectedException RuntimeException
     * @expectedExceptionMessage invalid "defaults", expecting array in route "foo" defined in private/config/routes.yaml
     */
    public function testRouteWithoutBadDefaults()
    {
        $routes = [
            'foo' => [
                'path' => '/foo',
                'action' => 'class:action',
                'defaults' => 'nogood'
            ]
        ];

        $dispatcher = $this->createMock(DispatcherInterface::class);

        $provider = $this->makeProvider($routes);
        $provider->provides($dispatcher);
    }
}

