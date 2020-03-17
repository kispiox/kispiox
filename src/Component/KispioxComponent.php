<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Component/KispioxComponent.php
* @copyright Copyright (c) 2016 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Component;

use Kispiox\Controller;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response\SapiEmitter;
use MattFerris\Application\Component\ComponentInterface;
use MattFerris\Application\Component\EventsHelper;
use MattFerris\Application\Component\EventLoggerHelper;
use MattFerris\Events\DispatcherInterface;
use MattFerris\Events\LoggerInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

class KispioxComponent extends Component
{
    /**
     * @var MattFerris\Events\DispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var MattFerris\Events\LoggerInterface
     */
    protected $eventLogger;


    /**
     * @param Psr\Container\ContainerInterface $container
     **/
    public function __construct(
        LoggerInterface $eventLogger,
        EventDispatcher $eventDispatcher
    )
    {
        $this->eventLogger = $eventLogger;
        $this->eventDispatcher = $eventDispatcher;
    }


    public function load()
    {
        (new EventsHelper($this->eventDispatcher, 'Kispiox'))->execute()
        (new EventLoggerHelper($this->eventLogger, 'Kixpiox'))->execute();
    }


    /**
     * Bootstrap an HTTP request/response cycle
     *
     * @param Psr\Container\ContainerInterface $container
     */
    static public function run(ContainerInterface $container)
    {
        $request = ServerRequestFactory::fromGlobals();
        $container->set('Request', $request);
        $response = $container->get('HttpDispatcher')->dispatch($request);

        if (is_null($response)) {
            $controller = new Controller($container);
            $response = new TextResponse('Internal server error. Dispatcher returned null response.', 500);
        }

        (new SapiEmitter())->emit($response);
    }
}

