<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Component/ConfigHelper.php
* @copyright Copyright (c) 2016 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Component;

use MattFerris\Application\Component\ComponentInterface;
use MattFerris\Configuration\ConfigurationInterface;
use MattFerris\Configuration\Loaders\YamlLoader;
use Psr\Container\ContainerInterface;

abstract class ConfigHelper implements ProviderInterface
{
    /**
     * @var string The file to load
     */
    protected $file;

    /**
     * @var ContainerInterface The container instance
     */
    protected $container;

    /**
     * @var Configuration The configuration instance
     */
    protected $config;


    /**
     * @param ContainerInterface $container The container instance
     * @param Configuration $configuration The configuration instance
     */
    public function __construct(ContainerInterface $container, ConfigurationInterface $configuration)
    {
        $this->container = $container;

        if (!is_null($this->file)) {
            $this->config = $configuration->newInstance();
            $this->config->load($this->file);
        }
    }

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
}

