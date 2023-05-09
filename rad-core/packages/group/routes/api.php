<?php

use Core\Packages\group\src\controllers\groupPackageController;

$prefix = config('core.prefix') . '/group';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {


        Route::group(['middleware' => ['acl']], function () {

            Route::get('allRoutes', ['uses' => 'GroupPackageController@allRoutes', 'as' => 'group.allRoutes']);
            Route::post('farsi', ['uses' => 'GroupPackageController@farsi', 'as' => 'group.farsi']);
            Route::get('list', ['uses' => 'GroupPackageController@list', 'as' => 'group.list']);


            Route::resource(
                '/',
                \GroupPackageController::class,
                [
                    'names' => [
                        'index' => 'group.index',
                        'store' => 'group.store',
                        'show' => 'group.show',
                        'update' => 'group.update',
                    ]
                ]
            )->parameters(['' => 'group']);

            Route::delete('/', ['uses' => 'GroupPackageController@destroy', 'as' => 'group.destroy']);
            //  Route::PUT('/update{id}', ['uses' => 'groupPackageController@update', 'as' => 'group.update']);



        });
    });
});
