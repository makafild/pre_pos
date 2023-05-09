<?php

use core\Packages\gis\src\controllers\AreaPackageController;
use Core\Packages\gis\src\controllers\GisPackageController;
use core\Packages\gis\src\controllers\PointPackageController;
use core\Packages\gis\src\controllers\RoutePackageController;

$prefix = config('core.prefix') . '/gis';

Route::group(['prefix' => $prefix], function () use ($prefix) {
    Route::group(['middleware' => ['jwt']], function () {
        Route::get('/countries', [GisPackageController::class, 'countries'])->name('gis.countries.list');
        Route::get('/provinces', [GisPackageController::class, 'provinces'])->name('gis.provinces.list');
        Route::get('/cities', [GisPackageController::class, 'cities'])->name('gis.cities.list');
        Route::group(['middleware' => ['acl']], function () {
            Route::group(['prefix' => "/areas"], function () {
                Route::get('/list', [GisPackageController::class, 'areas_list'])->name('gis.areas.list');
                Route::get('/{id}', [GisPackageController::class, 'areas_show'])->name('gis.areas.show');
                Route::put('/{id}', [GisPackageController::class, 'areas_update'])->name('gis.areas.update');
                Route::get('/', [GisPackageController::class, 'areas_index'])->name('gis.areas.index');
                Route::post('/', [GisPackageController::class, 'areas_store'])->name('gis.areas.store');
                Route::delete('/', [GisPackageController::class, 'areas_destroy'])->name('gis.areas.destroy');
            });
            Route::group(['prefix' => "/routes"], function () {
                Route::get('/list', [GisPackageController::class, 'routes_list'])->name('gis.routes.list');
                Route::get('/{id}', [GisPackageController::class, 'routes_show'])->name('gis.routes.show');
                Route::put('/{id}', [GisPackageController::class,  'routes_update'])->name('gis.routes.update');
                Route::get('/', [GisPackageController::class, 'routes_index'])->name('gis.routes.index');
                Route::post('/', [GisPackageController::class, 'routes_store'])->name('gis.routes.store');
                Route::delete('/', [GisPackageController::class, 'routes_destroy'])->name('gis.routes.destroy');
            });
            Route::group(['prefix' => "/points"], function () {
                Route::get('/{id}', [GisPackageController::class, 'points_show'])->name('gis.points.show');
                Route::put('/{id}', [GisPackageController::class, 'points_update'])->name('gis.points.update');
                Route::get('/', [GisPackageController::class, 'points_list'])->name('gis.points.list');
                Route::post('/', [GisPackageController::class, 'points_store'])->name('gis.points.store');
                Route::delete('/', [GisPackageController::class, 'points_destroy'])->name('gis.points.destroy');
            });
            Route::group(['prefix' => "/route_delivery"], function () {
                Route::get('/', [GisPackageController::class, 'delivery_index'])->name('gis.delivery.index');
                Route::get('/customerInRoute', [GisPackageController::class, 'Customer_inroute'])->name('gis.delivery.inRoute');
                Route::get('/add_customer_at_route', [GisPackageController::class, 'add_customer_at_route'])->name('gis.delivery.add_customer_at_route');
                Route::get('/{id}', [GisPackageController::class, 'delivery_show'])->name('gis.delivery.show');
                Route::put('/{id}', [GisPackageController::class, 'delivery_update'])->name('gis.delivery.update');
                Route::post('/', [GisPackageController::class, 'delivery_store'])->name('gis.delivery.store');
                Route::delete('/', [GisPackageController::class, 'delivery_destroy'])->name('gis.delivery.destroy');

            });


        });
    });
});
