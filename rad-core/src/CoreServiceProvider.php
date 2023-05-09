<?php

namespace Core\System;

use Core\System\Http\Middleware\Acl;
use Core\System\Http\Middleware\Jwt;
use Core\System\Http\Middleware\Cors;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Core\System\Providers\PackageServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->registerClassServices();
        $this->configLocalType();

        if (class_exists(Jwt::class)) {
            $router->aliasMiddleware('jwt', Jwt::class);
        }
        if (class_exists(Acl::class)) {
            $router->aliasMiddleware('acl', Acl::class);
        }
        if (class_exists(Cors::class)) {
            $router->aliasMiddleware('cors', Cors::class);
        }

    }

    public function registerClassServices()
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $this->app->register(PackageServiceProvider::class);

    }

    public function configLocalType()
    {
        $local = config('core.local');
        if ($local == 'fa') {
            App::setLocale(config('core.local'));
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $configs = [
            'core' => 'core.php'
        ];
        foreach ($configs as $key => $config) {
            $this->mergeConfigFrom(__DIR__ . '/../config/' . $config, $key);
        }
    }
}
