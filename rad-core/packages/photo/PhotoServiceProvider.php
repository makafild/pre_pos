<?php

namespace Core\Packages\photo;

use Core\System\Providers\PackableServiceProvider;

class PhotoServiceProvider extends PackableServiceProvider
{
    /**
     * @var string
     */
    protected $DIR = __DIR__;


    /**
     * @var string
     */
    protected $NAMESPACE = 'Core\Packages\photo\src\controllers';

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
