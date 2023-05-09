<?php

namespace Core\System\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Class PackableServiceProvider
 * @package App\Providers
 */
abstract class PackableServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $DIR = __DIR__ ;

    /**
     * @var string
     */
    protected $NAMESPACE = 'App\Http\Controllers';

    /**
     * PackableServiceProvider constructor.
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(\Illuminate\Foundation\Application $app)
    {
        parent::__construct($app);
        $this->map();
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    abstract public function boot();



    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        if (file_exists($this->DIR . "/routes" . DIRECTORY_SEPARATOR . "api.php")) {
            Route::prefix('api')
                ->namespace($this->NAMESPACE)
//                ->middleware(['request_logger','cache_storage','captcha','graphQLResponse'])
                ->group($this->DIR . "/routes" . DIRECTORY_SEPARATOR . "api.php");
        }
    }

}
