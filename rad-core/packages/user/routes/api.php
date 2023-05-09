<?php

use Core\Packages\user\src\controllers\UserPackageController;


$prefix = config('core.prefix') . '/users';
Route::group(['prefix' => $prefix], function () {

    Route::post('/', [UserPackageController::class, 'store'])->name('user.store');
    Route::post('/login', [UserPackageController::class, 'login'])->name('user.login');
    Route::group(['middleware' => ['jwt']], function () {
        Route::post('/logout', [UserPackageController::class, 'logout'])->name('user.logout');
         Route::put('/states', [UserPackageController::class, 'states'])->name('user.states');
        Route::put('{id}', [UserPackageController::class, 'update'])->name('user.update');
        Route::delete('/', [UserPackageController::class, 'destory'])->name('user.destory');
        Route::get('/profile', [UserPackageController::class, 'profile'])->name('user.profile');
        Route::get('/refresh', [UserPackageController::class, 'refreshToken'])->name('user.token.refresh');
        Route::post('/login_as/{id}', [UserPackageController::class, 'loginAs'])->name('user.loginAs');
        Route::get('{id}', [UserPackageController::class, 'show'])->name('user.show');
        Route::group(['middleware' => ['acl']], function () {
            Route::get('/', [UserPackageController::class, 'index'])->name('user.list');
        });
    });
});
