<?php
namespace Reactphp\X\LaravelLog\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger as Monolog;
use Monolog\Processor\PsrLogMessageProcessor;
use React\Filesystem\Factory;
use Reactphp\X\LaravelLog\Handler\StreamHandler;
use Reactphp\X\LaravelLog\Handler\RotatingFileHandler;

class ServerServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        $this->app->singleton("reactphp.filesystem", function ($app) {
            return Factory::create();
        });
    }

    public function boot()
    {
        $this->app['log']->extend('single', function($app, $config){
            return new Monolog($this->parseChannel($config), [
                $this->prepareHandler(
                    new StreamHandler(
                        $config['path'], $this->level($config),
                        $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
                    ), $config
                ),
            ], $config['replace_placeholders'] ?? false ? [new PsrLogMessageProcessor()] : []);
        });
        $this->app['log']->extend('daily', function($app, $config){
            return new Monolog($this->parseChannel($config), [
                $this->prepareHandler(new RotatingFileHandler(
                    $config['path'], $config['days'] ?? 7, $this->level($config),
                    $config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
                ), $config),
            ], $config['replace_placeholders'] ?? false ? [new PsrLogMessageProcessor()] : []);
        });
    }

}