<?php
use Core\Packages\tour_visit\src\controllers\TourVisitController;

$prefix = config('core.prefix') . '/tour_visits';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/', [TourVisitController::class, 'list'])->name('tour_visit.list');
        Route::post('/', [TourVisitController::class, 'store'])->name('tour_visit.store');
        Route::get('/{id}', [TourVisitController::class, 'show'])->name('tour_visit.show');
        Route::put('/{id}', [TourVisitController::class, 'update'])->name('tour_visit.update');
        Route::delete('/', [TourVisitController::class, 'delete'])->name('tour_visit.delete');
    });
});

