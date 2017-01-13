<?php

use Kispiox\Component\ServicesProvider;
use MattFerris\Di\ContainerInterface;
use MattFerris\Configuration\Configuration;
use MattFerris\Configuration\LocatorInterface;
use MattFerris\Configuration\Resources\FileResource;
use MattFerris\Events\Dispatcher;

class ServicesProviderTest extends PHPUnit_Framework_TestCase
{
    public function makeConfiguration($data, $extended = true)
    {
        $configuration = $this->createMock(Configuration::class);

        $configuration->expects($this->once())
            ->method('newInstance')
            ->willReturn($configuration);

        $configuration->expects($this->once())
            ->method('load')
            ->with(['services.yaml', 'services.dist.yaml']);

        if ($extended) {
            $resource = $this->createMock(FileResource::class);
            $resource->expects($this->once())
                ->method('getPath')
                ->willReturn('private/config/services.yaml');

            $locator = $this->createMock(LocatorInterface::class);
            $locator->expects($this->once())
                ->method('locate')
                ->with('services.yaml')
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

        return new ServicesProvider($container, $configuration);
    }

    public function testConstruct()
    {
        $config = $this->makeConfiguration([], false);
        $this->makeProvider([], null, $config);
    }

    /**
     * @depends testConstruct
     */
    public function testNullValueForServices()
    {
        $services = null;
        $container = $this->createMock(ContainerInterface::class);
        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /**
     * @depends testConstruct
     */
    public function testServiceWithNoArgs()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo'
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', [])
            ->willReturn(new stdClass);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /**
     * @depends testConstruct
     * @expectedException RuntimeException
     * @expectedExceptionMessage no "class" in service "FooService" defined in private/config/services.yaml
     */
    public function testServiceWithNoClass()
    {
        $services = [
            'FooService' => []
        ];

        $container = $this->createMock(ContainerInterface::class);

        $provider = $this->makeProvider($services);
        $provider->provides($container);
    }

    /**
     * @depends testServiceWithNoArgs
     */
    public function testServiceWithArgs()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'args' => [ 'foo' => 'bar' ]
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', $services['FooService']['args'])
            ->willReturn(new stdClass);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /**
     * @depends testServiceWithNoArgs
     * @expectedException RuntimeException
     * @expectedExceptionMessage invalid type for "args", expecting array in service "FooService" defined in private/config/services.yaml
     */
    public function testServiceWithBadArgs()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'args' => 'nogood'
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /*
     * Setters
     */

    /**
     * @depends testServiceWithNoArgs
     * @expectedException RuntimeException
     * @expectedExceptionMessage invalid type for "setters", expecting array in service "FooService" defined in private/config/services.yaml
     */
    public function testServiceWithBadSetters()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'setters' => 'nogood'
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', [])
            ->willReturn(new stdClass);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /**
     * @depends testServiceWithNoArgs
     */
    public function testServiceWithSetterWithNoArgs()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'setters' => [
                    [ 'name' => 'setFoo' ]
                ]
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', [])
            ->willReturn(new stdClass);

        $container->expects($this->once())
            ->method('injectMethod')
            ->with($this->isInstanceOf('stdClass'), 'setFoo', []);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /**
     * @depends testServiceWithNoArgs
     * @expectedException RuntimeException
     * @expectedExceptionMessage no "name" for setter #0 in service "FooService" defined in private/config/services.yaml
     */
    public function testServiceWithSetterWithNoName()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'setters' => [
                    []
                ]
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', [])
            ->willReturn(new stdClass);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /**
     * @depends testServiceWithSetterWithNoArgs
     */
    public function testServiceWithSetterWithArgs()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'setters' => [
                    [ 'name' => 'setFoo', 'args' => ['foo' => 'bar'] ]
                ]
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', [])
            ->willReturn(new stdClass);

        $container->expects($this->once())
            ->method('injectMethod')
            ->with($this->isInstanceOf('stdClass'), 'setFoo', $services['FooService']['setters'][0]['args']);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /**
     * @depends testServiceWithSetterWithArgs
     * @expectedException RuntimeException
     * @expectedExceptionMessage invalid type for "args", expecting array for setter #0 in service "FooService" defined in private/config/services.yaml
     */
    public function testServiceWithSetterWithBadArgs()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'setters' => [
                    [ 'name' => 'setFoo', 'args' => 'nogood' ]
                ]
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', [])
            ->willReturn(new stdClass);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }


    /*
     * Events
     */

    /**
     * @depends testServiceWithNoArgs
     * @expectedException RuntimeException
     * @expectedExceptionMessage invalid type for "events", expecting array in service "FooService" defined in private/config/services.yaml
     */
    public function testServiceWithBadEvents()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'events' => 'nogood'
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', [])
            ->willReturn(new stdClass);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /**
     * @depends testServiceWithNoArgs
     */
    public function testServiceWithEvent()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'events' => [
                    [ 'name' => 'FooEvent', 'listener' => 'onFoo' ]
                ]
            ]
        ];

        $obj = new StubListener();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', [])
            ->willReturn($obj);

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher->expects($this->once())
            ->method('addListener')
            ->with('FooEvent', [$obj, 'onFoo']);

        $container->expects($this->once())
            ->method('get')
            ->with('EventDispatcher')
            ->willReturn($dispatcher);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /**
     * @depends testServiceWithEvent
     * @expectedException RuntimeException
     * @expectedExceptionMessage no "name" for event #0 in service "FooService" defined in private/config/services.yaml
     */
    public function testServiceWithEventWithNoName()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'events' => [
                    []
                ]
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', [])
            ->willReturn(new stdClass);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }

    /**
     * @depends testServiceWithEvent
     * @expectedException RuntimeException
     * @expectedExceptionMessage no "listener" for event #0 in service "FooService" defined in private/config/services.yaml
     */
    public function testServiceWithEventWithNoListener()
    {
        $services = [
            'FooService' => [
                'class' => 'Foo',
                'events' => [
                    [ 'name' => 'FooEvent' ]
                ]
            ]
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('injectConstructor')
            ->with('Foo', [])
            ->willReturn(new stdClass);

        $provider = $this->makeProvider($services, $container);
        $provider->provides($container);
    }
}

class StubListener
{
    public function onFoo() {}
}

