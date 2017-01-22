<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Authentication/UsernamePasswordHandler.php
* @copyright Copyright (c) 2017 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Authentication;

use MattFerris\Auth\RequestInterface;
use MattFerris\Auth\Response;

class UsernamePasswordHandler
{
    /**
     * @var array
     */
    protected $users;

    /**
     * @param ConfigurationInterface $config
     */
    public function __construct(array $users)
    {
        $this->users = $users;
    }

    /**
     * @return string
     */
    public function handleUsernamePassword(UsernamePasswordRequest $request)
    {
        $valid = false;

        $user = $request->getUsername();
        $pass = $request->getPassword();

        if (isset($this->users[$user]) && password_verify($pass, $this->users[$user])) {
            $valid = true;
        }

        return new Response($valid, ['user' => $user]);
    }
}

