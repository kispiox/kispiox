<?php

/**
 * Kispiox - A lightweight application framework
* www.bueller.ca/kispiox
*
* Controller.php
* @copyright Copyright (c) 2016 Matt Ferris
* @author Matt Ferris <matt@bueller.ca>
*
* Licensed under BSD 2-clause license
* www.bueller.ca/kispiox/license
*/

namespace Kispiox;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
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
     * Return a URI based on a route name
     *
     * @param string $name The name of the route
     * @param array $params Parameters for the URI
     * @param bool $absolute Whether or not to generate an absolute URI (default is false)
     * @return string The URI that was generated
     */
    public function generate($name, array $params = [], $absolute = false)
    {
        $config = $this->container->get('Config');

        $path = '';
        if ($config->has('app.basepath')) {
            $path = $config->get('app.basepath');
        }

        $path .= $this->container->get('HttpDispatcher')->generate($name, $params);

        if ($absolute === true) {
            $uristr = (string)$this->request->getUri();

            // get uri up to (but excluding) third slash (http://example.com)
            // it's safe to assume the third slash won't occur within the first 8 chars
            $part = substr($uristr, 0, strpos($uristr, '/', 8));
            $path = $part.$path;
        }

        return $path;
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

