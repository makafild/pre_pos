<?php

use Core\Packages\constant\src\controllers\ConstantPackageController;

$prefix = config('core.prefix') . '/constant';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'ConstantPackageController@states', 'as' => 'constant.states']);

        Route::group(['middleware' => ['acl']], function () {
            Route::get('/list', ['uses' => 'ConstantPackageController@list', 'as' => 'constant.list']);
            Route::get('/listCategoryCustomer', ['uses' => 'ConstantPackageController@listCategoryCustomer', 'as' => 'constant.listCategoryCustomer']);
            Route::delete('/destroyCategory', ['uses' => 'ConstantPackageController@destroyCategory', 'as' => 'constant.destroyCategory']);
            Route::put('/updateCategory/{id}', ['uses' => 'ConstantPackageController@updateCategory', 'as' => 'constant.updateCategory']);
            Route::post('/CategoryCustomer', ['uses' => 'ConstantPackageController@CategoryCustomer', 'as' => 'constant.CategoryCustomer']);
            Route::get('/company', ['uses' => 'ConstantPackageController@listConstantCompany', 'as' => 'constant.listConstantCompany']);
            Route::resource('/', \ConstantPackageController::class, [
                'names' => [
                    'index' => 'constant.index',
                    'store' => 'constant.store',
                    'show' => 'constant.show',
                    'update' => 'constant.update',
                ]
            ])->parameters(['' => 'constant']);
            Route::delete('/', ['uses' => 'ConstantPackageController@destroy', 'as' => 'constant.destroy']);

        });
    });
});
