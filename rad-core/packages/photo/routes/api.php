<?php

use Core\Packages\photo\src\controllers\PhotoPackageController;

$prefix = config('core.prefix') . '/photo';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt', 'acl']], function () {

        Route::get('/status', ['uses' => 'PhotoPackageController@status', 'as' => 'photo.status']);
        Route::resource('/', \PhotoPackageController::class, [
            'names' => [
                'index' => 'photo.index',
                'store' => 'photo.store',
                'show' => 'photo.show',
            ]
        ]);
        Route::post('/file', ['uses' => 'PhotoPackageController@file', 'as' => 'photo.file']);

    });
});
