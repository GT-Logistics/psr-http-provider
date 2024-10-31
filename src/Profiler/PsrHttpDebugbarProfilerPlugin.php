<?php

namespace Gtlogistics\PsrHttpProvider\Profiler;

use Barryvdh\Debugbar\LaravelDebugbar;
use Http\Client\Common\Plugin;
use Http\CLient\Exception;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PsrHttpDebugbarProfilerPlugin implements Plugin
{
    private LaravelDebugbar $debugbar;

    public function __construct(LaravelDebugbar $debugbar)
    {
        $this->debugbar = $debugbar;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $url = $request->getUri()->__toString();
        $measure = "request-$url";

        $this->debugbar->startMeasure($measure, $url);

        return $next($request)->then(function (ResponseInterface $response) use ($measure) {
            $this->debugbar->stopMeasure($measure);

            return $response;
        }, function (Exception $e) use ($measure) {
            $this->debugbar->stopMeasure($measure);

            throw $e;
        });
    }
}
