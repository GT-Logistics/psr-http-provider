<?php

namespace Gtlogistics\PsrHttpProvider\Tests;

use Barryvdh\Debugbar;
use Gtlogistics\PsrHttpProvider\PsrHttpServiceProvider;
use Http\Client\Common\PluginClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Orchestra\Testbench\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\HttpClient\Psr18Client;

class PsrHttpServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            Debugbar\ServiceProvider::class,
            PsrHttpServiceProvider::class,
        ];
    }

    public function testRegister(): void
    {
        // PSR-17 Factories
        $uriFactory = $this->app->make(UriFactoryInterface::class);
        self::assertInstanceOf(UriFactoryInterface::class, $uriFactory);
        self::assertInstanceOf(Psr17Factory::class, $uriFactory);

        $streamFactory = $this->app->make(StreamFactoryInterface::class);
        self::assertInstanceOf(StreamFactoryInterface::class, $streamFactory);
        self::assertInstanceOf(Psr17Factory::class, $streamFactory);

        $uploadedFileFactory = $this->app->make(UploadedFileFactoryInterface::class);
        self::assertInstanceOf(UploadedFileFactoryInterface::class, $uploadedFileFactory);
        self::assertInstanceOf(Psr17Factory::class, $uploadedFileFactory);

        $requestFactory = $this->app->make(RequestFactoryInterface::class);
        self::assertInstanceOf(RequestFactoryInterface::class, $requestFactory);
        self::assertInstanceOf(Psr17Factory::class, $requestFactory);

        $serverRequestFactory = $this->app->make(ServerRequestFactoryInterface::class);
        self::assertInstanceOf(ServerRequestFactoryInterface::class, $serverRequestFactory);
        self::assertInstanceOf(Psr17Factory::class, $serverRequestFactory);

        $responseFactory = $this->app->make(ResponseFactoryInterface::class);
        self::assertInstanceOf(ResponseFactoryInterface::class, $responseFactory);
        self::assertInstanceOf(Psr17Factory::class, $responseFactory);

        // PSR-18 Client
        $client = $this->app->make(ClientInterface::class);
        self::assertInstanceOf(ClientInterface::class, $client);
        self::assertInstanceOf(PluginClient::class, $client);
    }
}
