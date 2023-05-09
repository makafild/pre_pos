<?php

use Core\Packages\notification\src\controllers\NotificationPackageController;

$prefix = config('core.prefix') . '/notification';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt']], function () {


        Route::group(['middleware' => ['acl']], function () {

            Route::resource('/', \NotificationPackageController::class, [
                'names' => [
                    'index' => 'notification.index',
                    'store' => 'notification.store',
                    'show' => 'notification.show',
                    'update' => 'notification.update',
                ]
            ])->parameters(['' => 'news']);
            Route::delete('/', ['uses' => 'NotificationPackageController@destroy', 'as' => 'notification.destroy']);

        });
    });
});
