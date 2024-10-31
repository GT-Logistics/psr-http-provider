<?php

namespace Gtlogistics\PsrHttpProvider;

use Barryvdh\Debugbar\LaravelDebugbar;
use Gtlogistics\PsrHttpProvider\Logger\PsrHttpDebugbarLoggerPlugin;
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
use Webmozart\Assert\Assert;

class PsrHttpServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel.php', 'http');

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
                $canProfile = config('http.debugbar.profile');
                $canLogBodies = config('http.debugbar.log_bodies');

                Assert::boolean($canProfile);
                Assert::boolean($canLogBodies);

                $middlewares = [];
                if ($canProfile) {
                    $middlewares[] = new PsrHttpDebugbarProfilerPlugin($app->get(LaravelDebugbar::class));
                }
                if ($canLogBodies) {
                    $maxBodySize = config('http.debugbar.max_body_size');

                    Assert::integer($maxBodySize);
                    Assert::greaterThanEq($maxBodySize, 0);

                    $middlewares[] = new PsrHttpDebugbarLoggerPlugin($app->get(LaravelDebugbar::class), $maxBodySize);
                }

                if (count($middlewares) === 0) {
                    return $client;
                }

                return new PluginClient($client, $middlewares);
            });
        }
    }
}
