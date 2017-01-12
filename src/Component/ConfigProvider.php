<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Component/ConfigProvider.php
* @copyright Copyright (c) 2016 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Component;

use MattFerris\Provider\ProviderInterface;
use MattFerris\Di\ContainerInterface;
use MattFerris\Configuration\ConfigurationInterface;
use MattFerris\Configuration\Loaders\YamlLoader;

abstract class ConfigProvider implements ProviderInterface
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
}

