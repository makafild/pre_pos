<?php

namespace core\packages\tour_delivery;

use Illuminate\Support\ServiceProvider;
use Core\System\Providers\PackableServiceProvider;

class TourDeliveryServiceProvider extends PackableServiceProvider
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
    protected $NAMESPACE = 'Core\Packages\tour_delivery\src\controllers';
    
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
