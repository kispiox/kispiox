<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Demo/RoutingProvider.php
* @copyright Copyright (c) 2016 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Demo;

use MattFerris\Provider\ProviderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

class RoutingProvider implements ProviderInterface
{
    public function provides($consumer)
    {
        // redirect to the Kispiox demo page
        $consumer->any('/', function (ServerRequestInterface $request) {
            $uri = $request->getUri();
            $uri = $uri->withPath($uri->getPath().'/public/demo');
            $response = new TextResponse('', 302, ['Location' => (string)$uri]);
            return $response;
        });
    }
}

