<?php

use Core\Packages\company\src\controllers\CompanyPackageController;

$prefix = config('core.prefix') . '/company';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'CompanyPackageController@states', 'as' => 'company.states']);
        Route::get('/city', ['uses' => 'CompanyPackageController@city', 'as' => 'company.city']);

        Route::group(['middleware' => ['acl']], function () {

            Route::put('/status', ['uses' => 'CompanyPackageController@changeStatus', 'as' => 'company.status.change']);
            Route::delete('/', ['uses' => 'CompanyPackageController@destroy', 'as' => 'company.destroy']);
            Route::get('/list', ['uses' => 'CompanyPackageController@list', 'as' => 'company.list']);
            Route::put('/approve', ['uses' => 'CompanyPackageController@approveStates', 'as' => 'company.state.approve']);
            Route::put('/states', ['uses' => 'CompanyPackageController@changeStates', 'as' => 'company.states.change']);

            Route::resource('/', \CompanyPackageController::class, [
                'names' => [
                    'index' => 'company.index',
                    'store' => 'company.store',
                    'show' => 'company.show',
                    'update' => 'company.update',
                ]
            ])->parameters(['' => 'company']);

        });
    });
});
