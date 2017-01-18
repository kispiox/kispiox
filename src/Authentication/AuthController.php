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
use Kispiox\Authentication\UsernamePasswordRequest;
use Psr\Http\Message\ServerRequestInterface;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

class AuthController extends Controller
{
    /**
     * @param string $body
     * @param int $code
     * @param array $headers
     * @return Psr\Http\Message\ResponseInterface
     */
    public function authResponse($body, $code = 200, array $headers = [])
    {
        return $this->textResponse($body, $code, $headers);
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function verifyAction(ServerRequestInterface $request)
    {
        $auth = $request->getHeaderLine('Authorization');
        list($type, $token) = explode($auth, ' ');

        if ($type === 'Bearer') {
            $token = (new Parser())->parse($token);

            $data = new ValidationData();
            if ($token->validate($data)) {
                return null;
            }
        }

        return $this->authResponse('not authenticated', 401);
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function authenticateAction(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();

        if (isset($data['user']) && isset($data['pass'])) {
            $authenticator = $this->container->get('Auth');
            $response = $authenticator->authenticate(
                new UsernamePasswordRequest($data['user'], $data['pass'])
            );

            if ($response->isValid()) {
                $attr = $response->getAttributes();
                return $this->authResponse($attr['token']);
            }
        }

        return $this->authResponse('authentication failed', 401);
    }
}

