<?php

namespace Gtlogistics\PsrHttpProvider\Logger;

use Barryvdh\Debugbar\LaravelDebugbar;
use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PsrHttpDebugbarLoggerPlugin implements Plugin
{
    private LaravelDebugbar $debugbar;

    private int $maxSize;

    private Plugin\RequestSeekableBodyPlugin $requestSeekableBodyPlugin;

    private Plugin\ResponseSeekableBodyPlugin $responseSeekableBodyPlugin;

    public function __construct(LaravelDebugbar $debugbar, string $maxSize)
    {
        $this->debugbar = $debugbar;
        $this->maxSize = $maxSize;
        $this->requestSeekableBodyPlugin = new Plugin\RequestSeekableBodyPlugin();
        $this->responseSeekableBodyPlugin = new Plugin\ResponseSeekableBodyPlugin();
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $this->makeRequestSeekable(
            $request,
            fn (RequestInterface $request) => $this->makeResponseSeekable(
                $request,
                fn (RequestInterface $request) => $this->logRequest(
                    $request,
                    $next,
                ),
                $first,
            ),
            $first,
        );
    }

    /**
     * @param callable(RequestInterface): Promise $next
     * @param callable(RequestInterface): Promise $first
     */
    private function makeRequestSeekable(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $this->requestSeekableBodyPlugin->handleRequest($request, $next, $first);
    }

    /**
     * @param callable(RequestInterface): Promise $next
     * @param callable(RequestInterface): Promise $first
     */
    private function makeResponseSeekable(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $this->responseSeekableBodyPlugin->handleRequest($request, $next, $first);
    }

    /**
     * @param callable(RequestInterface): Promise $next
     */
    private function logRequest(RequestInterface $request, callable $next): Promise
    {
        $this->debugbar->addMessage($request, 'debug');
        if ($this->canDumpBody($request)) {
            $this->debugbar->addMessage($this->dumpBody($request), 'debug');
        }

        return $next($request)->then(function (ResponseInterface $response) {
            $this->debugbar->addMessage($response, 'debug');
            if ($this->canDumpBody($response)) {
                $this->debugbar->addMessage($this->dumpBody($response), 'debug');
            }

            return $response;
        });
    }

    private function canDumpBody(MessageInterface $message): bool
    {
        return $this->maxSize === 0 || $message->getBody()->getSize() <= $this->maxSize;
    }

    /**
     * @return mixed
     */
    private function dumpBody(MessageInterface $message)
    {
        $contentType = $message->getHeader('content-type')[0] ?? 'application/octet-stream';
        [/*$type*/, $subtype] = explode('/', $contentType);
        $body = (string) $message->getBody();

        if (function_exists('json_decode') && str_contains($subtype, 'json')) {
            try {
                return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
            }
        }

        return $body;
    }
}
