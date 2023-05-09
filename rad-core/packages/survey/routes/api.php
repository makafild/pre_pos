<?php
use Core\Packages\survey\src\controllers\SurveyController;

$prefix = config('core.prefix') . '/survey';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt']], function () {

        Route::group(['middleware' => ['acl']], function () {

            Route::get('/', ['uses' => 'SurveyController@index', 'as' => 'survey.list']);
            Route::get('/{id}', ['uses' => 'SurveyController@show', 'as' => 'survey.show']);
            Route::post('/', ['uses' => 'SurveyController@store', 'as' => 'survey.store']);
            Route::post('/{id}', ['uses' => 'SurveyController@update', 'as' => 'survey.update']);
            Route::delete('/', ['uses' => 'SurveyController@destroy', 'as' => 'survey.destroy']);

        });
    });
});
