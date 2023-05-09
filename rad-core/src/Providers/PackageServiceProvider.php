<?php

namespace Core\System\Providers;

use Illuminate\Support\ServiceProvider;


/**
 * Class PackageServiceProvider
 * @package App\Providers
 */
class PackageServiceProvider extends ServiceProvider
{
    private $packages = [];

    /**
     * PackageServiceProvider constructor.
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(\Illuminate\Foundation\Application $app)
    {
        parent::__construct($app);
        foreach (config('core.packages', []) as $package) {
            $this->packages[] = new $package($app);
        }
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->packages as $package) {
            $package->boot();
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->packages as $package) {
            $package->register();
        }
    }
}
