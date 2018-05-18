<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Kispiox.php
* @copyright Copyright (c) 2016 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox;

use MattFerris\Configuration\Configuration;
use MattFerris\Configuration\Locators\FileLocator;
use MattFerris\Configuration\Loaders\YamlLoader;
use MattFerris\Di\Di;
use MattFerris\Application\Application;

class Kispiox
{
    static public function start()
    {
        $config = new Configuration(new FileLocator([
            'private/config',
            'vendor/kispiox/kispiox/private/config'
        ]), new YamlLoader());

        $config->load(['kispiox.yaml', 'kispiox.dist.yaml']);
        $config->load(['app.yaml', 'app.dist.yaml'], 'app');

        $components = $config->get('kispiox.components');

        if ($config->has('app.components')) {
            $appComponents = $config->get('app.components');
            if (isset($appComponents) && is_array($appComponents[0])) {
                $components = array_merge($components, $appComponents);
            } else {
                $components[] = $appComponents;
            }
        }

        $di = new Di();
        $di->set('Config', $config);

        try {

            $run = $config->get('app.run');
            $app = new Application($di, $components);
            $app->run($run);

        } catch (\Exception $e) {

            if ($di->has('Logger')) {
                $di->get('Logger')->critical('encountered exception: '.(string)$e, ['exception' => $e]);
            } else {
                error_log((string)$e);
            }

        }
    }
}

