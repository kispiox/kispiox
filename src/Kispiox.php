<?php

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

        $app = new Application($di, $components);
        $app->run($config->get('app.run'));
    }
}

