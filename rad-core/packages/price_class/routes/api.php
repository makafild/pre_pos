<?php

use Core\Packages\price_class\src\controllers\PriceClassPackageController;

$prefix = config('core.prefix') . '/price_class';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'PriceClassPackageController@states', 'as' => 'price_class.states']);

        Route::group(['middleware' => ['acl']], function () {

            Route::get('/list', ['uses' => 'PriceClassPackageController@list', 'as' => 'price_class.list']);
            Route::delete('/', ['uses' => 'PriceClassPackageController@destroy', 'as' => 'price_class.destroy']);
            Route::resource('/', \PriceClassPackageController::class,
                [
                    'names' => [
                        'index' => 'price_class.index',
                        'store' => 'price_class.store',
                        'show' => 'price_class.show',
                        'update' => 'price_class.update',
                    ]
                ])->parameters(['' => 'price_class']);

        });
    });
});
