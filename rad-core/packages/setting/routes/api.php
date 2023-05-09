<?php

use Core\Packages\setting\src\controllers\SettingController;

$prefix = config('core.prefix') . '/settings';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::put('/', [SettingController::class, 'update'])->name('setting.update');
        Route::get('/', [SettingController::class, 'list'])->name('setting.list');
        Route::get('/crmStatus', [SettingController::class, 'crmStatus'])->name('crmStatus');
        Route::get('/crmRun', [SettingController::class, 'crmRun'])->name('crmRun');

    });
});
