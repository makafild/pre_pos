<?php



use Illuminate\Support\Facades\Route;
use Illuminate\Http\Resources\Json\Resource;
use core\Packages\shop\src\controllers\ShopRegisterController;
use Core\Packages\shop\src\controllers\WithOutLoginController;
use core\Packages\stockroom\src\controllers\receiptController;
use core\Packages\shop\src\controllers\ShopRegisterrController;
use Core\Packages\shop\src\controllers\UserShopPackageController;
use Core\Packages\shop\src\controllers\OrderShopPackageController;
use core\Packages\shop\src\controllers\ConsumerShopPackageController;




$prefix = config('core.prefix') . '/shop';

Route::group(['prefix' => $prefix], function () {



                Route::group(['prefix' => "jwtrequest"], function () {


                Route::group(['prefix' => "jwtproduct"] , function(){
                Route::get('/product', [WithOutLoginController::class, 'product_index']);
                Route::get('/product/getProductsByIDcategores', [WithOutLoginController::class, 'getProductsByIDcategores']);
                Route::get('/product/{id}', [WithOutLoginController::class, 'product_show']);
                Route::get('/product/{id}/similar', [WithOutLoginController::class, 'product_similar']);
                Route::get('/product/{id}/score', [WithOutLoginController::class, 'product_score']);

            });
            Route::group(['prefix' => "jwtbrand"] , function(){
                Route::get('/brand', [WithOutLoginController::class, 'brand_index']);

            });
            Route::group(['prefix' => "jwtcategory"] , function(){
                Route::get('/category', [WithOutLoginController::class, 'category_index']);

            });
            Route::group(['prefix' => 'jwtcompany'], function () {
                Route::get('/company',[WithOutLoginController::class, 'company_index'] );
                Route::get('company/{id}',[WithOutLoginController::class, 'company_show'] );
                Route::get('company/{id}/products',[WithOutLoginController::class, 'company_products'] );
                Route::post('company/{id}/score',[WithOutLoginController::class, 'company_score'] );
            });
            Route::group(['prefix' => 'jwtcommon'], function () {
                Route::group(['prefix' => 'slide'], function () {
                Route::get('slide', [WithOutLoginController::class , 'slide_index']);
            });
            });
            Route::group(['prefix' => 'jwtorder', 'namespace' => 'Order'], function () {
                Route::post('order_check', [WithOutLoginController::class ,'order_check']);
                // Route::get('/payment_method', 'PaymentMethodController@index');
                // Route::get('/visit_tour', 'VisitTourController@index');
                // Route::post('/order/{id}/payment', 'PaymentController@pay');
                // Route::post('/order/check', 'OrderController@check');
                // Route::post('/order/prise', 'OrderController@calculatePrise');
                // Route::resource('/order', 'OrderController');
                // Route::resource('/periodic', 'PeriodicOrderController');
            });
        });
    });


    $prefix = config('core.prefix') . '/userShop';
Route::group(['prefix' => $prefix], function () {

    Route::post('/', [UserShopPackageController::class, 'store'])->name('user.store');
    Route::post('/login', [UserShopPackageController::class, 'login']);
    Route::group(['middleware' => ['jwt']], function () {
        Route::post('/logout', [UserShopPackageController::class, 'logout'])->name('user.logout');
        Route::get('user_address', [UserShopPackageController::class, 'user_address']);
        Route::get('user_address/{id}', [UserShopPackageController::class, 'address_show']);
        Route::post('user_address', [UserShopPackageController::class, 'address_store']);
        Route::put('user_address/{id}', [UserShopPackageController::class, 'address_update']);
        Route::delete('user_address/{id}', [UserShopPackageController::class, 'address_delete']);
        Route::put('/states', [UserShopPackageController::class, 'states']);
        Route::get('/dates', [UserShopPackageController::class, 'dates']);
        Route::put('{id}', [UserShopPackageController::class, 'update']);
        Route::delete('/', [UserShopPackageController::class, 'destory']);
        Route::get('/profile', [UserShopPackageController::class, 'profile']);
        Route::get('/refresh', [UserShopPackageController::class, 'refreshToken']);
        Route::post('/login_as/{id}', [UserShopPackageController::class, 'loginAs']);
        Route::get('{id}', [UserShopPackageController::class, 'show']);
        Route::group(['middleware' => ['acl']], function () {
        Route::get('/', [UserShopPackageController::class, 'index'])->name('user.list');
        });
    });
});

$prefix = config('core.prefix') . '/orderShop';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt',]], function () {

        Route::get('/states', ['uses' => 'OrderShopPackageController@states', 'as' => 'order.states']);
        Route::get('/reportAll', ['uses' => 'OrderShopPackageController@reportAll', 'as' => 'order.reportAll']);

        Route::group(['middleware' => ['acl']], function () {

            Route::post('/deliver', ['uses' => 'OrderShopPackageController@deliver', 'as' => 'order.deliver']);
            Route::post('/check', ['uses' => 'OrderShopPackageController@check', 'as' => 'order.check']);
            Route::get('/payment_method', ['uses' => 'OrderShopPackageController@payment_method_list', 'as' => 'order.payment_method.list']);
            Route::post('/status', ['uses' => 'OrderShopPackageController@changeStatus', 'as' => 'order.changeStatus']);
            Route::get('/payment_method/{id}', ['uses' => 'OrderShopPackageController@payment_method_show', 'as' => 'order.payment_method.show']);
            Route::post('/payment_method', ['uses' => 'OrderShopPackageController@payment_method_store', 'as' => 'order.payment_method.store']);
            Route::post('/payment_method/{id}', ['uses' => 'OrderShopPackageController@payment_method_update', 'as' => 'order.payment_method.update']);
            Route::post('/payment_method/default/{id}', ['uses' => 'OrderShopPackageController@payment_method_default', 'as' => 'order.payment_method.default']);
            Route::delete('/payment_method', ['uses' => 'OrderShopPackageController@payment_method_delete', 'as' => 'order.payment_method.delete']);
            Route::post('/invoice/deliver', ['uses' => 'OrderShopPackageController@invoice_deliver', 'as' => 'order.invoice.deliver']);
            Route::post('/invoice/{id}', ['uses' => 'OrderShopPackageController@invoice_store', 'as' => 'order.invoice.store']);
            Route::put('/invoice/{id}', ['uses' => 'OrderShopPackageController@invoice_update', 'as' => 'order.invoice.update']);
            Route::get('/invoice/{id}', ['uses' => 'OrderShopPackageController@invoice_show', 'as' => 'order.invoice.show']);
            Route::get('/invoice/', ['uses' => 'OrderShopPackageController@invoice_index', 'as' => 'order.invoice.list']);
            Route::delete('/invoice/', ['uses' => 'OrderShopPackageController@invoice_delete', 'as' => 'order.invoice.delete']);
            Route::get('/', ['uses' => 'OrderShopPackageController@index', 'as' => 'order.index']);
           // Route::get('/behzad', ['uses' => 'OrderShopPackageController@behzad', 'as' => 'order.behzad']);
            Route::post('/', ['uses' => 'OrderShopPackageController@store', 'as' => 'order.store']);
            Route::post('/order', ['uses' => 'OrderShopPackageController@multiStore', 'as' => 'order.store']);
            Route::get('/export', ['uses' => 'OrderShopPackageController@export', 'as' => 'order.export']);
            Route::get('/getCountVisitVisitorInDays', ['uses' => 'OrderShopPackageController@getCountVisitVisitorInDays', 'as' => 'order.getCountVisitVisitorInDays']);
            Route::get('/getPercentBrands', ['uses' => 'OrderShopPackageController@getPercentBrands', 'as' => 'order.getPercentBrands']);
            Route::get('/getPercentCategory', ['uses' => 'OrderShopPackageController@getPercentCategory', 'as' => 'order.getPercentCategory']);
            Route::get('/sing', ['uses' => 'OrderShopPackageController@sing', 'as' => 'order.sing']);
            Route::get('/OrderAndInvoiceDifference', ['uses' => 'OrderShopPackageController@OrderAndInvoiceDifference', 'as' => 'order.OrderAndInvoiceDifference']);
            Route::get('/export2', ['uses' => 'OrderShopPackageController@export2', 'as' => 'order.export2']);
            Route::get('/{id}', ['uses' => 'OrderShopPackageController@show', 'as' => 'order.show']);
            Route::post('/{id}', ['uses' => 'OrderShopPackageController@update', 'as' => 'order.update']);
            Route::post('/list', ['uses' => 'OrderShopPackageController@check', 'as' => 'order.check']);
            Route::delete('/', ['uses' => 'OrderShopPackageController@destroy', 'as' => 'order.destroy']);

        });
    });
});

$prefix = config('core.prefix') . '/ConsumerShop';

Route::group(['prefix' => $prefix], function () {

    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'ConsumerShopPackageController@states', 'as' => 'customer.states']);

        Route::group(['middleware' => ['acl']], function () {
            Route::get('/CustomerInRoute', ['uses' => 'ConsumerShopPackageController@CustomerInRoute', 'as' => 'CustomerInRoute']);
            Route::delete('/CustomerInRoute/delete', ['uses' => 'ConsumerShopPackageController@CustomerInRouteDelete', 'as' => 'CustomerInRouteDelete']);
            Route::post('/CustomerInRoute/add', ['uses' => 'ConsumerShopPackageController@CustomerInRouteAdd', 'as' => 'CustomerInRouteAdd']);
            Route::get('/export', ['uses' => 'ConsumerShopPackageController@export', 'as' => 'customer.export']);
            Route::get('/sign', ['uses' => 'ConsumerShopPackageController@sign', 'as' => 'customer.sign']);
            Route::get('/geo', ['uses' => 'ConsumerShopPackageController@geo', 'as' => 'customer.geo']);


            Route::get('/list', ['uses' => 'ConsumerShopPackageController@list', 'as' => 'customer.list']);
            Route::get('/visitors', ['uses' => 'ConsumerShopPackageController@visitors', 'as' => 'customer.visitors']);
            Route::delete('/', ['uses' => 'ConsumerShopPackageController@destroy', 'as' => 'customer.destroy']);
            Route::put('/states', ['uses' => 'ConsumerShopPackageController@changeStates', 'as' => 'customer.states.change']);
            //Route::get('/kinds', ['uses' => 'CustomerPackageController@kinds', 'as' => 'customer.kinds']);
            Route::get('/ImportFromExcel', ['uses' => 'ConsumerShopPackageController@ImportFromExcel', 'as' => 'customer.ImportFromExcel']);

            Route::put('/approve', ['uses' => 'ConsumerShopPackageController@approveStates', 'as' => 'customer.state.approve']);
            Route::get('/routes', ['uses' => 'ConsumerShopPackageController@routes', 'as' => 'customer.routes']);
            Route::resource('/', \ConsumerShopPackageController::class,
                [
                    'names' => [
                        'index' => 'customer.index',
                        'store' => 'customer.store',
                        'show' => 'customer.show',
                        'update' => 'customer.update',
                    ]
                ])->parameters(['' => 'customer']);

            Route::get('/{id}/routes', ['uses' => 'ConsumerShopPackageController@routes', 'as' => 'customer.routes']);

        });
    });
});


$prefix = config('core.prefix') . '/companyShop';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/states', ['uses' => 'CompanyShopPackageController@states', 'as' => 'company.states']);
        Route::get('/city', ['uses' => 'CompanyShopPackageController@city', 'as' => 'company.city']);

        Route::group(['middleware' => ['acl']], function () {

            Route::put('/status', ['uses' => 'CompanyShopPackageController@changeStatus', 'as' => 'company.status.change']);
            Route::delete('/', ['uses' => 'CompanyShopPackageController@destroy', 'as' => 'company.destroy']);
            Route::get('/list', ['uses' => 'CompanyShopPackageController@list', 'as' => 'company.list']);
            Route::put('/approve', ['uses' => 'CompanyShopPackageController@approveStates', 'as' => 'company.state.approve']);
            Route::put('/states', ['uses' => 'CompanyShopPackageController@changeStates', 'as' => 'company.states.change']);

            Route::resource('/', \CompanyShopPackageController::class, [
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
$prefix = config('core.prefix') . '/shopRegister';

Route::group(['prefix' => $prefix] , function() {
  //Route::get('/list' ,[ ShopRegisterController::class , 'register']);

  Route::post('/', ['uses' => 'ShopRegisterController@register']);
  Route::post('/check', ['uses' => 'ShopRegisterController@checkSmsCode']);

});





