<?php

use Core\Packages\coupon\src\controllers\CouponPackageController;

$prefix = config('core.prefix') . '/coupon';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {


        Route::group(['middleware' => ['acl']], function () {



            Route::resource(
                '/',
                \CouponPackageController::class,
                [
                    'names' => [
                        'index' => 'coupon.index',
                        'store' => 'coupon.store',
                        'show' => 'coupon.show',
                        'update' => 'coupon.update',
                    ]
                ]
            )->parameters(['' => 'coupon']);

            Route::delete('/', ['uses' => 'CouponPackageController@destroy', 'as' => 'coupon.destroy']);
        });
    });
});
