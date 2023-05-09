<?php

use Core\Packages\visitor\src\controllers\VisitorPackageController;

$prefix = config('core.prefix') . '/visitors';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'VisitorPackageController@states', 'as' => 'visitors.states']);

        Route::group(['middleware' => ['acl']], function () {

            Route::get('/list', ['uses' => 'VisitorPackageController@list', 'as' => 'visitors.list']);
            Route::get('/listVisited', ['uses' => 'VisitorPackageController@listVisited', 'as' => 'visitors.listVisited']);
            Route::get('/getToTalCustomerinDetailsTime', ['uses' => 'VisitorPackageController@getToTalCustomerinDetailsTime', 'as' => 'visitors.getToTalCustomerinDetailsTime']);
            Route::get('/listVisitedExcel', ['uses' => 'VisitorPackageController@listVisitedExcel', 'as' => 'visitors.listVisitedExcel']);
            Route::get('/listVisitedExcel2', ['uses' => 'VisitorPackageController@listVisitedExcel2', 'as' => 'visitors.listVisitedExcel2']);

            Route::get('/customers', ['uses' => 'VisitorPackageController@customers', 'as' => 'visitors.customers']);
            Route::get('/super', ['uses' => 'VisitorPackageController@super', 'as' => 'visitors.super']);
            Route::get('/super/{id}', ['uses' => 'VisitorPackageController@super_show', 'as' => 'visitors.super_show']);

            Route::get('/reason_for_not_visiting', ['uses' => 'VisitorPackageController@reason_for_not_visiting_list', 'as' => 'visitors.reason_for_not_visiting_list']);
            Route::get('/get_list_route_visitor', ['uses' => 'VisitorPackageController@get_list_route_visitor', 'as' => 'visitors.get_list_route_visitor']);
            Route::get('/reason_for_not_visiting_list_export', ['uses' => 'VisitorPackageController@reason_for_not_visiting_list_export', 'as' => 'visitors.reason_for_not_visiting_list_export']);
            Route::post('/super', ['uses' => 'VisitorPackageController@storeSuper', 'as' => 'visitors.storeSuper']);
            Route::put('/super', ['uses' => 'VisitorPackageController@updateSuper', 'as' => 'visitors.updateSuper']);
            Route::delete('/super', ['uses' => 'VisitorPackageController@destroy_super', 'as' => 'visitors.destroy_super']);
         //   Route::delete('/reason_for_not_visiting', ['uses' => 'VisitorPackageController@reason_for_not_visiting_delete', 'as' => 'visitors.reason_for_not_visiting_delete']);

            Route::resource('/', \VisitorPackageController::class, [
                'names' => [
                    'index' => 'visitors.index',
                    'store' => 'visitors.store',
                    'show' => 'visitors.show',
                    'update' => 'visitors.update',
                ]
            ])->parameters(['' => 'visitors']);

            Route::post('/unvisited_report', ['uses' => 'VisitorPackageController@unvisitedReport', 'as' => 'visitors.unvisited_report']);
            Route::delete('/', ['uses' => 'VisitorPackageController@destroy', 'as' => 'visitors.destroy']);
            Route::get('/routes/{id}', ['uses' => 'VisitorPackageController@routes', 'as' => 'visitors.routes']);

        });
    });
});
