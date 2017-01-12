<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Component/RoutingProvider.php
* @copyright Copyright (c) 2016 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Component;

use RuntimeException;

class RoutingProvider extends ConfigProvider
{
    /**
     * @var string The config file to load
     */
    protected $file = [ 'routes.yaml', 'routes.dist.yaml' ];

    /**
     * Configure the http dispatcher with routes defined in a config file.
     *
     * @param \MattFerris\Http\Routing\DispatcherInterface $consumer The http dispatcher instance
     * @throws \RuntimeException If a route definition contains missing or broken values
     */
    public function provides($consumer)
    {
        $file = $this->getConfigFilePath();

        foreach ($this->config->get() as $name => $route) {

            // if no path is defined, throw an exception
            if (!isset($route['path'])) {
                throw new RuntimeException(
                    'no "path" in route "'.$name.'" defined in '.$file
                );
            }

            // if no action is defined, throw an exception
            if (!isset($route['action'])) {
                throw new RuntimeException(
                    'no "action" in route "'.$name.'" defined in '.$file
                );
            }

            $path = $route['path'];
            $action = $route['action'];
            $method = null;
            $headers = [];
            $defaults = [];

            if (isset($route['method'])) {
                // if method isn't a string, throw an exception
                if (!is_string($route['method'])) {
                    throw new RuntimeException(
                        'invalid "method", expecting string in route "'.$name.'" defined in '.$file
                    );
                }

                $method = $route['method'];
            }

            if (isset($route['headers'])) {
                // if method isn't an array, throw an exception
                if (!is_array($route['headers'])) {
                    throw new RuntimeException(
                        'invalid "headers", expecting array in route "'.$name.'" defined in '.$file
                    );
                }

                $headers = $route['headers'];
            }

            if (isset($route['defaults'])) {
                // if method isn't a string, throw an exception
                if (!is_array($route['defaults'])) {
                    throw new RuntimeException(
                        'invalid "defaults", expecting array in route "'.$name.'" defined in '.$file
                    );
                }

                $defaults = $route['defaults'];
            }

            $consumer->route($path, $action, $method, $headers, $defaults, $name);

        } // foreach
    }
}

