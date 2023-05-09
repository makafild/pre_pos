<?php

use Core\Packages\brand\src\controllers\BrandPackageController;

$prefix = config('core.prefix') . '/brand';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/', ['uses' => 'BrandPackageController@index', 'as' => 'brand.index']);
        Route::group(['middleware' => ['acl']], function () {
            Route::resource('/', \BrandPackageController::class, [
                'names' => [
                    'store' => 'brand.store',
                    'show' => 'brand.show',
                    'update' => 'brand.update',
                ],
                'except' => ['index'],
            ])
                ->parameters(['' => 'brand']);
            Route::delete('destroy', ['uses' => 'BrandPackageController@destroy', 'as' => 'brand.destroy']);
        });
    });
});
