<?php

use Core\Packages\category\src\controllers\CategoryPackageController;

$prefix = config('core.prefix') . '/category';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/', ['uses' => 'CategoryPackageController@index', 'as' => 'category.index']);
        Route::get('/list', ['uses' => 'CategoryPackageController@list', 'as' => 'category.list']);
        Route::group(['middleware' => ['acl']], function () {

            Route::post('/', ['uses' => 'CategoryPackageController@store', 'as' => 'category.store']);
            Route::post('/{id}/image', ['uses' => 'CategoryPackageController@image', 'as' => 'category.image']);
            Route::get('/{id}', ['uses' => 'CategoryPackageController@search', 'as' => 'category.show']);
            Route::put('/{id}', ['uses' => 'CategoryPackageController@update', 'as' => 'category.update']);
            Route::delete('/{id}', ['uses' => 'CategoryPackageController@destroy', 'as' => 'category.destroy']);

        });
    });
});
