<?php

use Core\Packages\report\src\controllers\ReportController;

$prefix = config('core.prefix') . '/report';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/1', [ReportController::class, 'report_1'])->name('report.1');
        Route::get('/2', [ReportController::class, 'report_2'])->name('report.2');
        Route::get('/3', [ReportController::class, 'report_3'])->name('report.3');
        Route::get('/4', [ReportController::class, 'report_4'])->name('report.4');
        Route::get('/5', [ReportController::class, 'report_5'])->name('report.5');
        Route::get('/getCountCustomerIsbuy', [ReportController::class, 'getCountCustomerIsbuy'])->name('getCountCustomerIsbuy');
        Route::get('/getCountCustomerIsbuy_list', [ReportController::class, 'getCountCustomerIsbuy_list'])->name('getCountCustomerIsbuy_list');
        Route::get('/getCountCustomerIsbuyInRoute', [ReportController::class, 'getCountCustomerIsbuyInRoute'])->name('getCountCustomerIsbuyInRoute');
        Route::get('/getCountCustomerIsbuyInRoute_list', [ReportController::class, 'getCountCustomerIsbuyInRoute_list'])->name('getCountCustomerIsbuyInRoute_list');
        Route::get('/getListOperationProductSale', [ReportController::class, 'getListOperationProductSale'])->name('getListOperationProductSale');
        Route::get('/getListOperationProductSale_list', [ReportController::class, 'getListOperationProductSale_list'])->name('getListOperationProductSale_list');
        Route::get('/getListOperationProvince', [ReportController::class, 'getListOperationProvince'])->name('getListOperationProvince');
        Route::get('/getListOperationProvince_list', [ReportController::class, 'getListOperationProvince_list'])->name('getListOperationProvince_list');
        Route::get('/getListOperationVisitor', [ReportController::class, 'getListOperationVisitor'])->name('getListOperationVisitor');
        Route::get('/getListOperationVisitor_list', [ReportController::class, 'getListOperationVisitor_list'])->name('getListOperationVisitor_list');
        Route::get('/getListOperationCustomer', [ReportController::class, 'getListOperationCustomer'])->name('getListOperationCustomer');
        Route::get('/getListOperationCustomer_list', [ReportController::class, 'getListOperationCustomer_list'])->name('getListOperationCustomer_list');
       // Route::get('/getListOperationProductSaletest', [ReportController::class, 'getListOperationProductSaletest'])->name('getListOperationProductSaletest');

    });
});
