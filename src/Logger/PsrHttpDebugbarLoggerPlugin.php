<?php

namespace Gtlogistics\PsrHttpProvider\Logger;

use Barryvdh\Debugbar\LaravelDebugbar;
use Http\Client\Common\Plugin;
use Http\CLient\Exception;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PsrHttpDebugbarLoggerPlugin implements Plugin
{
    private LaravelDebugbar $debugbar;

    public function __construct(LaravelDebugbar $debugbar)
    {
        $this->debugbar = $debugbar;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $this->debugbar->addMessage($request, 'debug');

        return $next($request)->then(function (ResponseInterface $response) {
            $this->debugbar->addMessage($response, 'debug');

            return $response;
        });
    }
}
