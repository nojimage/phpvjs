<?php

/*
 * Copyright (c) 2023 nojimage
 */

namespace Nojimage\PHPvJS\Test\TestCase;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;
use Nojimage\PHPvJS\PHPvJSMiddleware;
use Nojimage\PHPvJS\VariableCarry;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PHPvJSMiddlewareTest extends TestCase
{
    /**
     * @var string
     */
    private $responseFilePath;

    public function setUp(): void
    {
        parent::setUp();
        $this->responseFilePath = dirname(__DIR__) . '/test_app/';
    }

    /**
     * @param string $filename the filename of response
     * @param string $contentType the content type of response
     * @return ResponseInterface
     */
    protected function getResponse(
        string $filename,
        string $contentType = 'text/html; charset=UTF-8'
    ): ResponseInterface {
        if (!file_exists($this->responseFilePath . $filename)) {
            throw new \RuntimeException('response file not found: ' . $filename);
        }

        $stream = (new StreamFactory())
            ->createStreamFromFile($this->responseFilePath . $filename);

        return (new Response())
            ->withBody($stream)
            ->withHeader('Content-Type', $contentType);
    }

    /**
     * @return void
     */
    public function testConstruct(): void
    {
        // default carry is VariableCarry
        $middleware = new PHPvJSMiddleware();
        $this->assertInstanceOf(VariableCarry::class, $middleware->getCarry());
    }

    /**
     * @return void
     */
    public function testProcess(): void
    {
        $request = new ServerRequest();
        $response = $this->getResponse('response.html');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (RequestInterface $request) use ($response) {
                self::assertInstanceOf(
                    VariableCarry::class,
                    $request->getAttribute(VariableCarry::class),
                    'request has carry instance'
                );

                return $response;
            });

        $carry = new VariableCarry();
        $carry->toJs('foo', 'bar');

        $middleware = new PHPvJSMiddleware($carry);
        $ret = $middleware->process($request, $handler);

        $this->assertStringContainsString(
            '<head><script>window["__phpvjs__"] = {"foo":"bar"};</script>',
            (string)$ret->getBody(),
            'insert variables to response body'
        );
    }

    /**
     * @return void
     */
    public function testProcessAsVariableHasDollar(): void
    {
        $request = new ServerRequest();
        $response = $this->getResponse('response.html');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $carry = new VariableCarry();
        $carry->toJs('foo', '$1');

        $middleware = new PHPvJSMiddleware($carry);
        $ret = $middleware->process($request, $handler);

        $this->assertStringContainsString(
            '<head><script>window["__phpvjs__"] = {"foo":"$1"};</script>',
            (string)$ret->getBody(),
            'insert variables to response body'
        );
    }
}
