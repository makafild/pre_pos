<?php

namespace Core\Packages\group;

use Core\System\Providers\PackableServiceProvider;

class GroupServiceProvider extends PackableServiceProvider
{
    /**
     * @var string
     */
    protected $DIR = __DIR__;


    /**
     * @var string
     */
    protected $NAMESPACE = 'Core\Packages\group\src\controllers';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //  $this->loadMigrationsFrom($this->DIR.'/database'.DIRECTORY_SEPARATOR.'migrations');
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
