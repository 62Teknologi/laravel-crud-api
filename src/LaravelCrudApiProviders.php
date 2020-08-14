<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelCrudApiProviders extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->router->group([
            'namespace' => 'EnamDuaTeknologi\LaravelCrudApi\Controllers',
        ], function ($router) {
            require 'routes/api.php';
        });
    }
}
