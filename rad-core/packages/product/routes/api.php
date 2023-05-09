<?php

use Core\Packages\product\src\controllers\ProductPackageController;

$prefix = config('core.prefix') . '/product';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'ProductPackageController@states', 'as' => 'product.states']);

        Route::group(['middleware' => ['acl']], function () {

            //NO PAGINATE
            Route::get('/all', ['uses' => 'ProductPackageController@all', 'as' => 'product.all']);

            Route::get('/list', ['uses' => 'ProductPackageController@list', 'as' => 'product.list']);
            Route::get('/getProductsByIDcategores', ['uses' => 'ProductPackageController@getProductsByIDcategores', 'as' => 'product.getProductsByIDcategores']);
            Route::get('/getProductsCompany', ['uses' => 'ProductPackageController@getProductsCompany', 'as' => 'product.getProductsCompany']);

            Route::put('/states', ['uses' => 'ProductPackageController@changeStates', 'as' => 'product.states.change']);

            Route::get('/export', ['uses' => 'ProductPackageController@export', 'as' => 'product.export']);
            Route::get('/test', ['uses' => 'ProductPackageController@test', 'as' => 'product.test']);
        


            Route::get('/category', ['uses' => 'ProductPackageController@categories', 'as' => 'product.category']);

            Route::get('/brand', ['uses' => 'ProductPackageController@brands', 'as' => 'product.brand']);

            Route::delete('/', ['uses' => 'ProductPackageController@destroy', 'as' => 'product.destroy']);

            Route::resource('/', \ProductPackageController::class,
                [
                    'names' => [
                        'index' => 'product.index',
                        'store' => 'product.store',
                        'show' => 'product.show',
                        'update' => 'product.update',
                    ]
                ])->parameters(['' => 'product']);

        });
    });
});
