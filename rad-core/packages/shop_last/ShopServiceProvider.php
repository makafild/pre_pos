<?php

namespace Core\Packages\shop;

use Illuminate\Support\ServiceProvider;
use Core\System\Providers\PackableServiceProvider;

class ShopServiceProvider extends PackableServiceProvider
{
    protected $DIR = __DIR__;
    /**
     * Register services.
     *
     * @return void
     */
    /**
     * @var string
     */
    protected $NAMESPACE = 'Core\Packages\shop\src\controllers';

    public function register()
    {

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom($this->DIR.'/database'.DIRECTORY_SEPARATOR.'migrations');
    }
}
