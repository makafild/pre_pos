<?php

use Core\Packages\news\src\controllers\NewsPackageController;

$prefix = config('core.prefix') . '/news';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'NewsPackageController@list', 'as' => 'news.list']);

        Route::group(['middleware' => ['acl']], function () {

            Route::resource('/', \NewsPackageController::class, [
                'names' => [
                    'index' => 'news.index',
                    'store' => 'news.store',
                    'show' => 'news.show',
                    'update' => 'news.update',
                ]
            ])->parameters(['' => 'news']);
            Route::delete('/', ['uses' => 'NewsPackageController@destroy', 'as' => 'news.destroy']);
        });
    });
});
