<?php

use Core\Packages\slider\src\controllers\SliderPackageController;

$prefix = config('core.prefix') . '/slider';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'SliderPackageController@states', 'as' => 'slider.states']);

        Route::group(['middleware' => ['acl']], function () {

            Route::put('/changeStatus', ['uses' => 'SliderPackageController@changeStatus', 'as' => 'slider.changeStatus']);

            Route::resource('/', \SliderPackageController::class, [
                'names' => [
                    'index' => 'slider.index',
                    'store' => 'slider.store',
                    'show' => 'slider.show',
                    'update' => 'slider.update',
                ]
            ])->parameters(['' => 'slider']);

            Route::delete('/', ['uses' => 'SliderPackageController@destroy', 'as' => 'slider.destroy']);
            Route::PUT('/', ['uses' => 'SliderPackageController@update', 'as' => 'slider.update']);

        });
    });
});
