<?php

use Kispiox\Controller;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;

class ControllerTest extends PHPUnit_Framework_TestCase
{
    public function makeContainer($request = null)
    {
        if ($request === null) {
            $request = $this->createMock(ServerRequestInterface::class);
        }
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('Request')
            ->willReturn(true);
        $container->expects($this->once())
            ->method('get')
            ->with('Request')
            ->willReturn($request);
        return $container;
    }

    public function testConstruct()
    {
        $controller = new Controller($this->makeContainer());
        $this->assertInstanceOf(ContainerInterface::class, $controller->getContainer());
        $this->assertInstanceOf(ServerRequestInterface::class, $controller->getRequest());
    }

    /**
     * @depends testConstruct
     * @expectedException RuntimeException
     * @expectedExceptionMessage container does not contain a request instance
     */
    public function testConstructWithContainerWithoutRequest()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('Request')
            ->willReturn(false);
        $controller = new Controller($container);
    }

    /**
     * @depends testConstruct
     */
    public function testEmptyResponse()
    {
        $controller = new Controller($this->makeContainer());
        $this->assertInstanceOf(EmptyResponse::class, $controller->emptyResponse());
    }

    /**
     * @depends testConstruct
     */
    public function testHtmlResponse()
    {
        $controller = new Controller($this->makeContainer());
        $this->assertInstanceOf(HtmlResponse::class, $controller->htmlResponse(''));
    }

    /**
     * @depends testConstruct
     */
    public function testJsonResponse()
    {
        $controller = new Controller($this->makeContainer());
        $this->assertInstanceOf(JsonResponse::class, $controller->jsonResponse([]));
    }

    /**
     * @depends testConstruct
     */
    public function testRedirectResponse()
    {
        $controller = new Controller($this->makeContainer());
        $this->assertInstanceOf(RedirectResponse::class, $controller->redirectResponse(''));
    }

    /**
     * @depends testConstruct
     */
    public function testTextResponse()
    {
        $controller = new Controller($this->makeContainer());
        $this->assertInstanceOf(TextResponse::class, $controller->textResponse(''));
    }

    /**
     * @depends testConstruct
     */
    public function testInternalRedirectWithString()
    {
        $uriA = $this->createMock(UriInterface::class);
        $uriB = $this->createMock(UriInterface::class);

        $uriB->expects($this->once())
            ->method('withPath')
            ->with('path')
            ->willReturn($uriA);

        $requestA = $this->createMock(ServerRequestInterface::class);
        $requestB = $this->createMock(ServerRequestInterface::class);

        $requestB->expects($this->once())
            ->method('getUri')
            ->willReturn($uriB);

        $requestB->expects($this->once())
            ->method('withUri')
            ->with($uriA)
            ->willReturn($requestA);

        $container = $this->makeContainer($requestB);
        $controller = new Controller($container);

        $this->assertEquals($controller->internalRedirect('path'), $requestA);
    }

    /**
     * @depends testInternalRedirectWithString
     */
    public function testInternalRedirectWithUriInterface()
    {
        $uri = $this->createMock(UriInterface::class);

        $requestA = $this->createMock(ServerRequestInterface::class);
        $requestB = $this->createMock(ServerRequestInterface::class);

        $requestB->expects($this->once())
            ->method('withUri')
            ->with($uri)
            ->willReturn($requestA);

        $container = $this->makeContainer($requestB);
        $controller = new Controller($container);

        $this->assertEquals($controller->internalRedirect($uri), $requestA);
    }

    /**
     * @depends testInternalRedirectWithString
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage URI should be a string or instance of Psr\Http\Message\UriInterface
     */
    public function testInternalRedirectWithBadUri()
    {
        $controller = new Controller($this->makeContainer());
        $controller->internalRedirect(null);
    }
}

