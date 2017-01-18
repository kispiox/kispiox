<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Component/AuthProvider.php
* @copyright Copyright (c) 2017 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Component;

use Kispiox\Authentication\UsernamePasswordRequest;
use Kispiox\Authentication\UsernamePasswordHandler;
use Kispiox\Authentication\TokenManipulator;

class AuthProvider extends ConfigProvider
{
    /**
     * @var string The file to load
     */
    protected $file = ['users.yaml'];

    /**
     * {@inheritDoc}
     */
    public function provides($consumer)
    {
        $handler = new UsernamePasswordHandler($this->config->get('users'));
        $manipulator = new TokenManipulator($this->container->get('Config'));
        $consumer
            ->handle(UsernamePasswordRequest::class, [$handler, 'handleUsernamePassword'])
            ->manipulate(UsernamePasswordRequest::class, [$manipulator, 'manipulate']);
    }
}

