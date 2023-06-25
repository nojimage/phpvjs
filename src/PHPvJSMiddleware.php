<?php

/*
 * Copyright (c) 2023 nojimage
 */

declare(strict_types=1);

namespace Nojimage\PHPvJS;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PHPvJS Middleware
 *
 * Pass PHP variables to JavaScript
 */
class PHPvJSMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
    /**
     * @var VariableCarryInterface
     */
    private $carry;

    /**
     * construct
     *
     * @param VariableCarryInterface|null $carry the carry instance
     */
    public function __construct(VariableCarryInterface $carry = null)
    {
        $this->carry = $carry ?? new VariableCarry();
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Set VariableCarry to request attribute
        $request = $request->withAttribute($this->carry->getAttributeName(), $this->carry);

        $response = $handler->handle($request);

        $script = $this->carry->renderScriptTag();

        // Check response content type and empty pass variables
        if (
            $script === '' ||
            !preg_match(
                '/\bx?html\b/',
                $response->getHeaderLine('Content-Type') ?? ''
            )
        ) {
            return $response;
        }

        // Inject to response body
        $body = $response->getBody();
        $streamClassName = get_class($body);
        $body->rewind();
        $content = $body->getContents();

        // Insert the script after the opening HEAD tag.
        $updatedContent = (string)preg_replace(
            '/(<html.*?>.*?<head.*?>)/is',
            '$1' . str_replace('$', '\$', $script),
            $content
        );

        // Return a new response with the updated stream.
        $newStream = new $streamClassName('php://memory', 'wb+');
        $newStream->write($updatedContent);
        $newStream->rewind();

        return $response->withBody($newStream);
    }

    /**
     * @return VariableCarryInterface
     */
    public function getCarry(): VariableCarryInterface
    {
        return $this->carry;
    }

    /**
     * @param VariableCarryInterface $carry
     */
    public function setCarry(VariableCarryInterface $carry): void
    {
        $this->carry = $carry;
    }
}
