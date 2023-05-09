<?php

use Core\Packages\customer\src\controllers\CustomerPackageController;

$prefix = config('core.prefix') . '/customer';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'CustomerPackageController@states', 'as' => 'customer.states']);

        Route::group(['middleware' => ['acl']], function () {
            Route::get('/CustomerInRoute', ['uses' => 'CustomerPackageController@CustomerInRoute', 'as' => 'CustomerInRoute']);
            Route::delete('/CustomerInRoute/delete', ['uses' => 'CustomerPackageController@CustomerInRouteDelete', 'as' => 'CustomerInRouteDelete']);
            Route::post('/CustomerInRoute/add', ['uses' => 'CustomerPackageController@CustomerInRouteAdd', 'as' => 'CustomerInRouteAdd']);
            Route::get('/export', ['uses' => 'CustomerPackageController@export', 'as' => 'customer.export']);
            Route::get('/sign', ['uses' => 'CustomerPackageController@sign', 'as' => 'customer.sign']);
            Route::get('/geo', ['uses' => 'CustomerPackageController@geo', 'as' => 'customer.geo']);


            Route::get('/list', ['uses' => 'CustomerPackageController@list', 'as' => 'customer.list']);
            Route::get('/visitors', ['uses' => 'CustomerPackageController@visitors', 'as' => 'customer.visitors']);
            Route::delete('/', ['uses' => 'CustomerPackageController@destroy', 'as' => 'customer.destroy']);
            Route::put('/states', ['uses' => 'CustomerPackageController@changeStates', 'as' => 'customer.states.change']);
            //Route::get('/kinds', ['uses' => 'CustomerPackageController@kinds', 'as' => 'customer.kinds']);
            Route::get('/ImportFromExcel', ['uses' => 'CustomerPackageController@ImportFromExcel', 'as' => 'customer.ImportFromExcel']);

            Route::put('/approve', ['uses' => 'CustomerPackageController@approveStates', 'as' => 'customer.state.approve']);
            Route::get('/routes', ['uses' => 'CustomerPackageController@routes', 'as' => 'customer.routes']);
            Route::resource('/', \CustomerPackageController::class,
                [
                    'names' => [
                        'index' => 'customer.index',
                        'store' => 'customer.store',
                        'show' => 'customer.show',
                        'update' => 'customer.update',
                    ]
                ])->parameters(['' => 'customer']);

            Route::get('/{id}/routes', ['uses' => 'CustomerPackageController@routes', 'as' => 'customer.routes']);

        });
    });
});
