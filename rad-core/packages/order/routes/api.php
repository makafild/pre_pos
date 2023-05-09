<?php

use Core\Packages\order\src\controllers\OrderPackageController;

$prefix = config('core.prefix') . '/order';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt',]], function () {

        Route::get('/states', ['uses' => 'OrderPackageController@states', 'as' => 'order.states']);
        Route::get('/reportAll', ['uses' => 'OrderPackageController@reportAll', 'as' => 'order.reportAll']);

        Route::group(['middleware' => ['acl']], function () {

            Route::post('/deliver', ['uses' => 'OrderPackageController@deliver', 'as' => 'order.deliver']);
            Route::post('/check', ['uses' => 'OrderPackageController@check', 'as' => 'order.check']);
            Route::get('/payment_method', ['uses' => 'OrderPackageController@payment_method_list', 'as' => 'order.payment_method.list']);
            Route::post('/status', ['uses' => 'OrderPackageController@changeStatus', 'as' => 'order.changeStatus']);
            Route::get('/payment_method/{id}', ['uses' => 'OrderPackageController@payment_method_show', 'as' => 'order.payment_method.show']);
            Route::post('/payment_method', ['uses' => 'OrderPackageController@payment_method_store', 'as' => 'order.payment_method.store']);
            Route::post('/payment_method/{id}', ['uses' => 'OrderPackageController@payment_method_update', 'as' => 'order.payment_method.update']);
            Route::post('/payment_method/default/{id}', ['uses' => 'OrderPackageController@payment_method_default', 'as' => 'order.payment_method.default']);
            Route::delete('/payment_method', ['uses' => 'OrderPackageController@payment_method_delete', 'as' => 'order.payment_method.delete']);

            Route::post('/invoice/deliver', ['uses' => 'OrderPackageController@invoice_deliver', 'as' => 'order.invoice.deliver']);
            Route::post('/invoice/{id}', ['uses' => 'OrderPackageController@invoice_store', 'as' => 'order.invoice.store']);
            Route::put('/invoice/{id}', ['uses' => 'OrderPackageController@invoice_update', 'as' => 'order.invoice.update']);
            Route::get('/invoice/{id}', ['uses' => 'OrderPackageController@invoice_show', 'as' => 'order.invoice.show']);
            Route::get('/invoice/', ['uses' => 'OrderPackageController@invoice_index', 'as' => 'order.invoice.list']);
            Route::delete('/invoice/', ['uses' => 'OrderPackageController@invoice_delete', 'as' => 'order.invoice.delete']);

            Route::get('/', ['uses' => 'OrderPackageController@index', 'as' => 'order.index']);
           // Route::get('/behzad', ['uses' => 'OrderPackageController@behzad', 'as' => 'order.behzad']);
            Route::post('/', ['uses' => 'OrderPackageController@store', 'as' => 'order.store']);
            Route::get('/export', ['uses' => 'OrderPackageController@export', 'as' => 'order.export']);
            Route::get('/getCountVisitVisitorInDays', ['uses' => 'OrderPackageController@getCountVisitVisitorInDays', 'as' => 'order.getCountVisitVisitorInDays']);
            Route::get('/getPercentBrands', ['uses' => 'OrderPackageController@getPercentBrands', 'as' => 'order.getPercentBrands']);
            Route::get('/getBestProductsSeller', ['uses' => 'OrderPackageController@getBestProductsSeller', 'as' => 'order.getBestProductsSeller']);
            Route::get('/getPercentCategory', ['uses' => 'OrderPackageController@getPercentCategory', 'as' => 'order.getPercentCategory']);
            Route::get('/sing', ['uses' => 'OrderPackageController@sing', 'as' => 'order.sing']);
            Route::get('/OrderAndInvoiceDifference', ['uses' => 'OrderPackageController@OrderAndInvoiceDifference', 'as' => 'order.OrderAndInvoiceDifference']);
            Route::get('/export2', ['uses' => 'OrderPackageController@export2', 'as' => 'order.export2']);
            Route::get('/{id}', ['uses' => 'OrderPackageController@show', 'as' => 'order.show']);
            Route::post('/{id}', ['uses' => 'OrderPackageController@update', 'as' => 'order.update']);


            Route::post('/list', ['uses' => 'OrderPackageController@check', 'as' => 'order.check']);

            Route::delete('/', ['uses' => 'OrderPackageController@destroy', 'as' => 'order.destroy']);

        });
    });
});
