<?php

use Core\Packages\not_visited\src\controllers\NotVisitedController;

$prefix = config('core.prefix') . '/not_visiteds';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/', [NotVisitedController::class, 'index'])->name('not_visited.index');
        Route::get('/list_message', [NotVisitedController::class, 'list_message'])->name('not_visited.list_message');
        Route::post('/', [NotVisitedController::class, 'store'])->name('not_visited.store');
        Route::get('/{id}', [NotVisitedController::class, 'show'])->name('not_visited.show');
        Route::put('/{id}', [NotVisitedController::class, 'update'])->name('not_visited.update');
        Route::delete('/', [NotVisitedController::class, 'destroy'])->name('not_visited.destroy');
    });
});
