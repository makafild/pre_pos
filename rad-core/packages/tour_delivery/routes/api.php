<?php

use Core\Packages\tour_delivery\src\controllers\TourDeliveryController;







$prefix = config('core.prefix') . '/tour_delivery';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

         Route::get('/list',[TourDeliveryController::class, 'list'] )->name('tour_delivery.list');
        Route::get('/', [TourDeliveryController::class, 'index'])->name('tour_delivery.index');
        Route::post('/', [TourDeliveryController::class, 'store'])->name('tour_delivery.store');
        Route::get('/{id}', [TourDeliveryController::class, 'show'])->name('tour_delivery.show');
        Route::put('/{id}', [TourDeliveryController::class, 'update'])->name('tour_delivery.update');
        Route::delete('/', [TourDeliveryController::class, 'delete'])->name('tour_delivery.delete');
    });
});

