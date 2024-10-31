<?php

namespace Gtlogistics\PsrHttpProvider;

use Barryvdh\Debugbar\LaravelDebugbar;
use Gtlogistics\PsrHttpProvider\Profiler\PsrHttpDebugbarProfilerPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class PsrHttpServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        // PSR-17 Factories
        $this->app->singleton(UriFactoryInterface::class, static function () {
            return Psr17FactoryDiscovery::findUriFactory();
        });
        $this->app->singleton(StreamFactoryInterface::class, static function () {
            return Psr17FactoryDiscovery::findStreamFactory();
        });
        $this->app->singleton(UploadedFileFactoryInterface::class, static function () {
            return Psr17FactoryDiscovery::findUploadedFileFactory();
        });
        $this->app->singleton(RequestFactoryInterface::class, static function () {
            return Psr17FactoryDiscovery::findRequestFactory();
        });
        $this->app->singleton(ServerRequestFactoryInterface::class, static function () {
            return Psr17FactoryDiscovery::findServerRequestFactory();
        });
        $this->app->singleton(ResponseFactoryInterface::class, static function () {
            return Psr17FactoryDiscovery::findResponseFactory();
        });

        // PSR-18 Client
        $this->app->singleton(ClientInterface::class, static function () {
            return Psr18ClientDiscovery::find();
        });

        // Register the profiler
        if ($this->app->has(LaravelDebugbar::class)) {
            $this->app->extend(ClientInterface::class, static function (ClientInterface $client, Application $app) {
                return new PluginClient($client, [
                    new PsrHttpDebugbarProfilerPlugin($app->get(LaravelDebugbar::class)),
                ]);
            });
        }
    }
}
