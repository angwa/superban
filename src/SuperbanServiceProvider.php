<?php

namespace Superban;

use Illuminate\Support\ServiceProvider;

class SuperbanServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $config = realpath(__DIR__ . '/../config/superban.php');

        $this->publishes([
            $config => config_path('superban.php')
        ]);

        $this->registerMiddleware();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/superban.php', 'superban');
    }

    protected function registerMiddleware()
    {
        app('router')->aliasMiddleware('superban', \Superban\Middleware\SuperbanMiddleware::class);
    }
}
