<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'api'], function () {

    Route::group(['prefix' => 'customer', 'namespace' => 'Customer', 'middleware' => 'change_language'], function () {

        Route::group(['prefix' => 'v1', 'namespace' => 'v1'], function () {

            Route::group(['prefix' => 'setting', 'namespace' => 'Setting'], function () {
                Route::get('/location/countries', 'LocationController@countries', '');
                Route::get('/location/provinces', 'LocationController@provinces');
                Route::get('/location/cities', 'LocationController@cities');
                Route::get('/setting/list', 'SettingController@list');
                Route::get('/constant/list', 'ConstantController@list');
                Route::any('/onesignal_proxy/{uri}', 'SettingController@oneSignalProxy')->where('uri', '[.\-_0-9A-Za-z//]+');
            });

            Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {
                Route::post('forgetRequest', 'LoginController@forgetRequest');
                Route::post('checkForgetSmsCode', 'LoginController@checkForgetSmsCode');
                Route::post('login', 'LoginController@login')->name('login');
                Route::post('register', 'RegisterController@register');
                Route::post('register/custom', 'RegisterController@custom');
                Route::post('registerByVisitor', 'RegisterController@registerByVisitor');
                Route::get('getArea', 'RegisterController@getArea');
                Route::post('guest', 'RegisterController@guest');
                Route::post('sendSmsCodeNew', 'UserController@requestSmsCode');
                Route::post('checkSmsCodeNew', 'UserController@checkSmsCodeNew');
            });
 Route::group(['prefix' => 'product', 'namespace' => 'Product'], function () {

                Route::group(['prefix' => 'product'], function () {

                    Route::get('/jwtproduct', 'ProductController@index');
                    Route::get('jwtproduct/{id}', 'ProductController@jwtShow');
                    Route::get('jwtproduct/{id}/similar', 'ProductController@jwtsimilar');
                    Route::get('jwtproduct/{id}/score', 'ProductController@jwtgetScore');
                });
                Route::group(['prefix' => 'brand'], function () {

                    Route::get('/jwtbrand', 'BrandController@jwtindex');
                });
                Route::group(['prefix' => 'category'], function () {

                    Route::get('/jwtCategory', ['uses' => 'CategoryController@jwttree']);
                });
                Route::group(['prefix' => 'v1', 'namespace' => 'v1' ,'middleware' => ['api', 'change_language_en'] ], function () {
                    Route::get('/price_class_all', 'PriceClassController@list_all');
                });
            });
            Route::group(['prefix' => 'company', 'namespace' => 'Company'], function () {

                Route::group(['prefix' => 'company'], function () {
                    Route::get('/jwtsuperior', ['uses' => 'CompanyController@superior']);
                    Route::get('jwtsuperior/{id}', ['uses' => 'CompanyController@show']);
                    Route::get('{id}/products', ['uses' => 'CompanyController@products']);
                    Route::post('{id}/score', ['uses' => 'CompanyController@score', 'as' => 'a.company.score.store']);
                });
            });

            Route::group(['prefix' => 'common'], function () {
                Route::group(['prefix' => 'slide' , 'namespace' => 'Common'], function () {

              Route::get('index', 'SlideController@index');
            });
            });
            Route::group(['prefix' => 'order', 'namespace' => 'Order'], function () {
                Route::post('/coupon/check', 'CouponController@check');

                Route::get('/payment_method', 'PaymentMethodController@index');
                Route::get('/visit_tour', 'VisitTourController@index');

                Route::post('/order/{id}/payment', 'PaymentController@pay');
                Route::post('/order/check', 'OrderController@check');
                Route::post('/order/prise', 'OrderController@calculatePrise');
                Route::resource('/order', 'OrderController');
                Route::resource('/periodic', 'PeriodicOrderController');
            });
        });
    });
});

Route::group(['middleware' => ['api', 'jwt.verify'], 'namespace' => 'api'], function () {

    Route::group(['prefix' => 'customer', 'namespace' => 'Customer', 'middleware' => 'change_language'], function () {

        Route::group(['prefix' => 'v1', 'namespace' => 'v1'], function () {

            Route::group(['prefix' => 'search', 'namespace' => 'Search'], function () {
                Route::get('search', 'SearchController@index');
            });

            Route::group(['prefix' => 'user', 'namespace' => 'User'], function () {

                Route::post('logout', 'LoginController@logout');

                Route::resource('company_report', 'CompanyReportController');

                Route::post('attachSignalPlayerId', 'UserController@attachSignalPlayerId');

                Route::get('show', 'UserController@show');
                Route::post('update', 'UserController@update');

                Route::post('sendSmsCode', 'UserController@requestSmsCode');
                Route::post('checkSmsCode', 'UserController@checkSmsCode');

                Route::group(['prefix' => 'favorite'], function () {

                    Route::get('/', 'FavoriteController@index');
                    Route::post('add', 'FavoriteController@add');
                    Route::post('delete', 'FavoriteController@delete');


                    Route::group(['prefix' => 'company'], function () {
                        Route::get('/', 'CompanyFavoriteController@index');
                        Route::post('add', 'CompanyFavoriteController@add');
                        Route::post('delete', 'CompanyFavoriteController@delete');
                    });
                });
            });

            Route::group(['prefix' => 'comment', 'namespace' => 'Comment'], function () {

                Route::post('/', 'CommentController@store');
                Route::get('/{type}/{id}', 'CommentController@list');
                Route::post('/{comment_id}/rate', 'CommentController@rate_store');
                Route::get('/{comment_id}/rate', 'CommentController@rate_list');
            });

            Route::group(['prefix' => 'common', 'namespace' => 'Common'], function () {

               // Route::group(['prefix' => 'slide'], function () {
//
                //    Route::get('index', 'SlideController@index');
              //  });

                Route::group(['prefix' => 'news'], function () {

                    Route::get('index', ['uses' => 'NewsController@index', 'as' => 'a.news.index']);
                    Route::get('show/{id}', ['uses' => 'NewsController@show']);
                    Route::get('top', ['uses' => 'NewsController@top']);
                });

                Route::group(['prefix' => 'file'], function () {

                    Route::post('store', 'FileController@store');
                });

                Route::group(['prefix' => 'message'], function () {

                    Route::get('index', ['uses' => 'MessageController@index']);
                    Route::get('{id}/show', ['uses' => 'MessageController@show']);
                    Route::post('store', ['uses' => 'MessageController@store', 'as' => 'a.message.store']);
                });

                Route::group(['prefix' => 'survey'], function () {
                    Route::get('/', ['uses' => 'SurveyController@index', 'as' => 'a.survey.list']);
                    Route::get('{id}', 'SurveyController@show');
                    Route::post('{id}/answer', 'SurveyController@store');
                });

                Route::group(['prefix' => 'suggestion'], function () {
                    Route::get('index', 'SuggestionController@index');
                    Route::post('store', ['uses' => 'SuggestionController@store', 'as' => 'a.suggestion.store']);
                });
            });

            Route::group(['prefix' => 'company', 'namespace' => 'Company'], function () {

                Route::group(['prefix' => 'company'], function () {
                    Route::get('superior', ['uses' => 'CompanyController@superior']);
                    Route::get('/', ['uses' => 'CompanyController@index']);
                    Route::get('city', ['uses' => 'CompanyController@city']);
                    Route::get('{id}', ['uses' => 'CompanyController@show']);
                    Route::get('{id}/products', ['uses' => 'CompanyController@products']);
                    Route::get('{id}/tree', ['uses' => 'CompanyController@tree']);
                    Route::get('{id}/score', ['uses' => 'CompanyController@getScore']); //--
                    Route::post('{id}/score', ['uses' => 'CompanyController@score', 'as' => 'a.company.score.store']);
                });
            });



            Route::group(['prefix' => 'product', 'namespace' => 'Product'], function () {

                Route::group(['prefix' => 'product'], function () {

                    Route::get('/', 'ProductController@index');
                    Route::get('{id}', 'ProductController@show');
                    Route::post('visit', 'ProductController@visit_store');
                    Route::get('{id}/similar', 'ProductController@similar');
                    Route::get('{id}/score', 'ProductController@getScore');
                    Route::post('{id}/score', 'ProductController@score');
                });
                Route::group(['prefix' => 'brand'], function () {

                    Route::get('/', 'BrandController@index');
                });
                Route::group(['prefix' => 'category'], function () {

                    Route::get('/', ['uses' => 'CategoryController@tree', 'as' => 'a.product.category.tree']);
                    Route::get('{id}/products', 'CategoryController@products');
                });
            });
        });
    });

    Route::group(['prefix' => 'visitor', 'namespace' => 'Visitor'], function () {
        Route::group(['prefix' => 'v1', 'namespace' => 'v1'], function () {

            Route::group(['prefix' => 'route', 'namespace' => 'route'], function () {
                Route::get('RouteList', 'RouteController@index');
                Route::get('customerList', 'RouteController@customer_list');
                Route::get('CustomerRegisterByVisitor', 'RouteController@CustomerRegisterByVisitor');
            });

            Route::group(['prefix' => 'position', 'namespace' => 'position'], function () {
                Route::get('/', 'PositionController@index');
                Route::post('/', 'PositionController@store');
                Route::get('/{id}', 'PositionController@show');
            });

            Route::group(['prefix' => 'reason_for_not_visiting', 'namespace' => 'customer'], function () {
                Route::post('/', 'ReasonForNotVisitingController@store');
            });

            Route::group(['prefix' => 'time_visit', 'namespace' => 'customer'], function () {
                Route::post('/', 'VisitorController@setTimeVisit');
                Route::get('/', 'VisitorController@getTime');
            });
            Route::group(['prefix' => 'ListNotVisited', 'namespace' => 'customer'], function () {
                Route::get('/', 'VisitorController@ListNotVisited');
            });
            Route::group(['prefix' => 'getOrderRegisterByVisitor', 'namespace' => 'customer'], function () {
                Route::get('/', 'VisitorController@getOrderRegisterByVisitor');
            });
            Route::group(['prefix' => 'VisitorIsHaveOrderForUser', 'namespace' => 'customer'], function () {
                Route::get('/', 'VisitorController@VisitorIsHaveOrderForUser');
            });
        });
    });

    Route::group(['prefix' => 'company', 'namespace' => 'Company', 'middleware' => 'change_language_en'], function () {

        Route::group(['prefix' => 'v1', 'namespace' => 'v1'], function () {

            Route::resource('/photo', 'PhotoController');

            Route::post('/message/store', 'MessageController@store');

            Route::resource('/category', 'CategoryController');

            Route::post('/product/priceClass', 'ProductController@priceClass');
            Route::post('/product/changeStatus', 'ProductController@changeStatus');
            Route::put('/product', 'ProductController@update');
            Route::resource('/product', 'ProductController');

            Route::put('/customer', 'CustomerController@update');
            Route::resource('/customer', 'CustomerController');

            Route::resource('/constant', 'ConstantController');

            Route::put('/order', 'OrderController@update');
            Route::resource('/order', 'OrderController');

            Route::get('/price_class', 'PriceClassController@list');
            Route::post('/price_class', 'PriceClassController@store');
            Route::put('/price_class', 'PriceClassController@update');
        });
    });
});

Route::middleware(['basicAuth'])->group(function () {
    Route::get('/hi', function () {
        return 'Hello World';
    });

    Route::get('/users', 'ws\UsersController@index');
});
