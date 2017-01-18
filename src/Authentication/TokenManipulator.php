<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Authentication/TokenManipulator.php
* @copyright Copyright (c) 2017 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox\Authentication;

use MattFerris\Configuration\ConfigurationInterface;
use MattFerris\Auth\ResponseInterface;
use MattFerris\Auth\Response;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use RuntimeException;

class TokenManipulator
{
    /**
     * @var ConfigurationInterface
     */
    protected $config;

    /**
     * @param ConfigurationInterface $config
     */
    public function __construct(ConfigurationInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param ResponseInterface $response
     * @return Response
     * @throws RuntimeException If app.auth.key hasn't been set
     */
    public function manipulate(ResponseInterface $response)
    {
        // don't process invalid responses
        if (!$response->isValid()) {
            return null;
        }

        $tokenBuilder = new Builder();

        if ($this->config->has('app.auth.duration')) {
            $tokenBuilder
                ->setIssuedAt(time())
                ->setExpiration(time() + $this->config->get('app.auth.duration'));
        }

        foreach ($response->getAttributes() as $key => $val) {
            $tokenBuilder->set($key, $val);
        }

        if (!$this->config->has('app.auth.key')) {
            throw new RuntimeException('app.auth.key is not set');
        }

        $token = $tokenBuilder
            ->sign(new Sha256(), $this->config->get('app.auth.key'))
            ->getToken();

        return new Response($response->isValid(), ['token' => $token]);
    }
}

