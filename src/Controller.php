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

use Interop\Container\ContainerInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;
use RuntimeException;
use InvalidArgumentException;

/**
 * A utility class to provide some nice shortcuts for working with requests and
 * responses.
 */
class Controller
{
    /**
     * @var ContainerInterface The current dependency injection container
     */
    protected $container;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface The current request instance
     */
    protected $request;

    /**
     * @param ContainerInterface $container The current dependency injection container
     */
    public function __construct(ContainerInterface $container)
    {
        if (!$container->has('Request')) {
            throw new RuntimeException('container does not contain a request instance');
        }

        $this->container = $container;
        $this->request = $container->get('Request');
    }

    /**
     * Return the current dependency injection container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Return the current request instance.
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Generate an empty response.
     *
     * @param int $status Status code for response
     * @param array $headers Array of headers for response
     * @return EmptyResponse
     */
    public function emptyResponse($status = 200, $headers = [])
    {
        return new EmptyResponse($status, $headers);
    }

    /**
     * Generate a response with HTML content.
     *
     * @param string|\Psr\Http\Message\StreamInterface $html HTML for the response
     * @param int $status Status code for response
     * @param array $headers Array of headers for response
     * @return HtmlResponse
     */
    public function htmlResponse($html, $status = 200, $headers = [])
    {
        return new HtmlResponse($html, $status, $headers);
    }

    /**
     * Generate a response with JSON content.
     *
     * @param mixed $data Data to covert to JSON
     * @param int $status Status code for response
     * @param array $headers Array of headers for response
     * @return JsonResponse
     */
    public function jsonResponse($data, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Generate a redirect response.
     *
     * @param string|UriInterface $uri URI for the redirect
     * @param int $status Status code for response
     * @param array $headers Array of headers for response
     * @return RedirectResponse
     */
    public function redirectResponse($uri, $status = 302, $headers = [])
    {
        return new RedirectResponse($uri, $status, $headers);
    }

    /**
     * Generate a response with text content.
     *
     * @param string|\Psr\Http\Message\StreamInterface $text Text for the response
     * @param int $status Status code for response
     * @param array $headers Array of headers for response
     * @return TextResponse
     */
    public function textResponse($text, $status = 302, $headers = [])
    {
        return new TextResponse($text, $status, $headers);
    }

    /**
     * Generate a request for internal redirection.
     *
     * @param string|UriInterface $uri URI for the redirect
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function internalRedirect($uri)
    {
        if (is_string($uri)) {

            // make a new URI instance, then make a new request
            $uri = $this->request->getUri()->withPath($uri);
            return $this->request->withUri($uri);

        } elseif ($uri instanceof UriInterface) {

            // get a new request with the specified URI instance
            return $this->request->withUri($uri);

        } else {

            // bad $uri value
            throw new InvalidArgumentException(
                'URI should be a string or instance of Psr\Http\Message\UriInterface'
            );

        }
    }

    /**
     * Render a view template and return the contents.
     *
     * @param string $name The view template name
     * @param array $variables The variables for the view template
     * @return string The value of the rendered template
     */
    public function render($name, array $variables)
    {
        return $this->container->get('ViewRenderer')->render($name, $variables);
    }
}

