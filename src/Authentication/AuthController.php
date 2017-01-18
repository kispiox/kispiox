<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Authentication/ProtectedController.php
* @copyright Copyright (c) 2017 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Authentication;

use Kispiox\Controller;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller
{
    /**
     * @param ServerRequestInterface $request
     */
    public function authAction(ServerRequestInterface $request)
    {
        $auth = $request->getHeaderLine('Authorization');
        list($type, $token) = explode($auth, ' ');
    }
}

