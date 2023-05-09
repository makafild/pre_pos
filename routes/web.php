<?php


Route::group([
    'prefix'    => 'common',
//    'namespace' => 'Common',
], function () {

//    Route::post('/notification/destroy', 'NotificationController@destroy');
    Route::resource('/notification', 'api\Customer\v1\Common\NotificationController');
});
