<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Component/DefaultComponent.php
* @copyright Copyright (c) 2016 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Component;

use Kispiox\Controller;
use MattFerris\Application\Component;
use MattFerris\Di\ContainerInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response\SapiEmitter;
use RuntimeException;

class DefaultComponent extends Component
{

    /**
     * Bootstrap an HTTP request/response cycle
     *
     * @param \MattFerris\Di\ContainerInterface $container
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

