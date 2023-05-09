<?php
use Core\Packages\introducer_code\src\controllers\IntroducerCodeController;

$prefix = config('core.prefix') . '/introducer_codes';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::post('/', [IntroducerCodeController::class, 'store'])->name('introducer_code.store');
        Route::get('/', [IntroducerCodeController::class, 'list'])->name('introducer_code.list');
        Route::get('/{id}', [IntroducerCodeController::class, 'show'])->name('introducer_code.show');
        Route::put('/{id}', [IntroducerCodeController::class, 'update'])->name('introducer_code.update');
        Route::delete('/', [IntroducerCodeController::class, 'destroy'])->name('introducer_code.destroy');
    });
});
