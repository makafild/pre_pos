<?php

use Core\Packages\promotion\src\controllers\PromotionPackageController;

$prefix = config('core.prefix') . '/promotion';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'PromotionPackageController@states', 'as' => 'promotion.states']);

        Route::group(['middleware' => ['acl']], function () {


            Route::resource('/', \PromotionPackageController::class,
                [
                    'names' => [
                        'index' => 'promotion.index',
                        'store' => 'promotion.store',
                        'show' => 'promotion.show',
                        'update' => 'promotion.update',
                    ]
                ])->parameters(['' => 'promotion']);

            Route::post('/list', ['uses' => 'PromotionPackageController@list', 'as' => 'promotion.list']);

            Route::delete('/', ['uses' => 'PromotionPackageController@destroy', 'as' => 'promotion.destroy']);

        });
    });
});
