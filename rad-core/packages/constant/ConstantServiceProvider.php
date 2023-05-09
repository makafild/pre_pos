<?php

namespace Core\Packages\constant;

use Core\System\Providers\PackableServiceProvider;

class ConstantServiceProvider extends PackableServiceProvider
{
    /**
     * @var string
     */
    protected $DIR = __DIR__;


    /**
     * @var string
     */
    protected $NAMESPACE = 'Core\Packages\constant\src\controllers';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom($this->DIR.'/database'.DIRECTORY_SEPARATOR.'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
