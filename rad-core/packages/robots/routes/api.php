<?php

use core\Packages\robots\src\controllers\RobotsController;
 $prefix = config('core.prefix') . '/robots';

Route::group(['prefix' => $prefix], function () {

        Route::post('/getProduct', ['uses' => 'RobotsController@getProduct', 'as' => 'RobotsController.getProduct']);
         Route::post('/check', ['uses' => 'RobotsController@check', 'as' => 'RobotsController.check']);




        Route::group(['middleware' => ['jwt']], function () {
             //   Route::group(['middleware' => ['acl']], function () {
                        Route::get('/searchUser', ['uses' => 'RobotsController@searchUser', 'as' => 'RobotsController.searchUser']);

                        Route::get('/getToken/{id}', ['uses' => 'RobotsController@getToken', 'as' => 'RobotsController.getToken']);


              //  });

        });


});






