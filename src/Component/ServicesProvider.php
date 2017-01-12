<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Component/ServicesProvider.php
* @copyright Copyright (c) 2016 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Component;

use RuntimeException;

class ServicesProvider extends ConfigProvider
{
    /**
     * @var string The config file to load
     */
    protected $file = [ 'services.yaml', 'services.dist.yaml' ];

    /**
     * Get path to actual config file
     *
     * @return string The path to the file
     */
    public function getConfigFilePath()
    {
        $path = null;
        $locator = $this->config->getLocator();

        if (is_array($this->file)) {
            $res = null;
            foreach ($this->file as $file) {
                if (($res = $locator->locate($file)) !== false) {
                    break;
                }
            }
            $path = $res->getPath();
        } else {
            $path = $locator->locate($this->file)->getPath();
        }

        return $path;
    }

    /**
     * Configure the container with services defined in a config file.
     *
     * @param \MattFerris\Di\ContainerInterface $consumer The container instance
     * @throws \RuntimeException If the service definition contains missing or broken values
     */
    public function provides($consumer)
    {
        $file = $this->getConfigFilePath();

        foreach ($this->config->get() as $name => $def) {

            // if no class is defined, throw an exception
            if (!isset($def['class'])) {
                throw new RuntimeException(
                    'no "class" in service "'.$name.'" defined in '.$file
                );
            }

            $args = [];
            if (isset($def['args'])) {

                // if args are not an array, throw an exception
                if (!is_array($def['args'])) {
                    throw new RuntimeException(
                        'invalid type for "args", expecting array in service "'.$name.'" defined in '.$file
                    );
                }

                $args = $def['args'];
            }

            $instance = $consumer->injectConstructor($def['class'], $args);
            $consumer->set($name, $instance);

            // process any setters
            if (isset($def['setters'])) {

                // if setters is not an array, throw an exception
                if (!is_array($def['setters'])) {
                    throw new RuntimeException(
                        'invalid type for "setters", expecting array in service "'.$name.'" defined in '.$file
                    );
                }

                foreach ($def['setters'] as $i => $setter) {

                    // if no setter name defined, throw an exception
                    if (!isset($setter['name'])) {
                        throw new RuntimeException(
                            'no "name" for setter #'.$i.' in service "'.$name.'" defined in '.$file
                        );
                    }

                    $args = [];
                    if (isset($setter['args'])) {

                        // if args is not an array, throw an exception
                        if (!is_array($setter['args'])) {
                            throw new RuntimeException(
                                'invalid type for "args", expecting array for setter #'.$i.' in service "'.$name.'" defined in '.$file
                            );
                        }

                        $args = $setter['args'];
                    }

                    $this->container->injectMethod($instance, $setter['name'], $args);

                }
            }

            // subscribe to any specified events
            if (isset($def['events'])) {

                // if events is not an array , throw an exception
                if (!is_array($def['events'])) {
                    throw new RuntimeException(
                        'invalid type for "events", expecting array in service "'.$name.'" defined in '.$file
                    );
                }

                $dispatcher = $consumer->get('EventDispatcher');

                foreach ($def['events'] as $i => $event) {

                    // if no event name specified, throw an exception
                    if (!isset($event['name'])) {
                        throw new RuntimeException(
                            'no "name" for event #'.$i.' in service "'.$name.'" defined in '.$file
                        );
                    }

                    // if no event listener specified, throw an exception
                    if (!isset($event['listener'])) {
                        throw new RuntimeException(
                            'no "listener" for event #'.$i.' in service "'.$name.'" defined in '.$file
                        );
                    }

                    $dispatcher->addListener($event['name'], [$instance, $event['listener']]);

                }

            }

        } // foreach
    }
}

