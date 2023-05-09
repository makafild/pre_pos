<?php

use Core\Packages\visitor_position\src\controllers\VisitorPositionController;

$prefix = config('core.prefix') . '/visitor_position';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::put('/', [VisitorPositionController::class, 'update'])->name('visitor_position.update');
        Route::get('/', [VisitorPositionController::class, 'list'])->name('visitor_position.list');
        Route::get('/Positions', [VisitorPositionController::class, 'VisitorPositions'])->name('visitor_position.VisitorPositions');

    });
});
