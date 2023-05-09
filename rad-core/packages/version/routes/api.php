<?php

use Core\Packages\version\src\controllers\VersionPackageController;

$prefix = config('core.prefix') . '/version';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {


        Route::group(['middleware' => ['acl']], function () {



            Route::resource('/', \VersionPackageController::class,
                [
                    'names' => [
                        'index' => 'version.index',
                        'store' => 'version.store',
                        'show' => 'version.show',
                        'update' => 'version.update',
                    ]
                ])->parameters(['' => 'version']);

                Route::delete('/', ['uses' => 'VersionPackageController@destroy', 'as' => 'version.destroy']);



        });
    });
});
