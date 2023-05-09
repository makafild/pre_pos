<?php
return [
    'controllers' => [
        'namespace' => 'Core\\System\\Http\\Controllers'
    ],
    'packages_controllers' => [
        'namespace' => 'Core\\Packages'
    ],
    'local' => 'fa',
    'packages' => [
        \Core\Packages\user\UserServiceProvider::class,
        \Core\Packages\role\RoleServiceProvider::class,
        \Core\Packages\company\CompanyServiceProvider::class,
        \Core\Packages\gis\GisServiceProvider::class,
        \Core\Packages\visitor\VisitorServiceProvider::class,
        \Core\Packages\photo\PhotoServiceProvider::class,
        \Core\Packages\brand\BrandServiceProvider::class,
        \Core\Packages\category\CategoryServiceProvider::class,
        \Core\Packages\product\ProductServiceProvider::class,
        \Core\Packages\constant\ConstantServiceProvider::class,
        \Core\Packages\customer\CustomerServiceProvider::class,
        \Core\Packages\tour_delivery\TourDeliveryServiceProvider::class,
        \Core\Packages\order\OrderServiceProvider::class,
        \Core\Packages\promotion\PromotionServiceProvider::class,
        \Core\Packages\news\NewsServiceProvider::class,
        \Core\Packages\price_class\PriceClassServiceProvider::class,
        \Core\Packages\slider\SliderServiceProvider::class,
        \Core\Packages\introducer_code\IntroducerCodeServiceProvider::class,
        \Core\Packages\survey\SurveyServiceProvider::class,
        \Core\Packages\tour_visit\TourVisitServiceProvider::class,
        \Core\Packages\setting\SettingServiceProvider::class,
        \Core\Packages\comment\CommentServiceProvider::class,
        \Core\Packages\visitor_position\VisitorPositionServiceProvider::class,
        \Core\Packages\version\VersionServiceProvider::class,
        \Core\Packages\coupon\CouponServiceProvider::class,
	    \Core\Packages\report\ReportServiceProvider::class,
        \Core\Packages\group\GroupServiceProvider::class,
        \Core\Packages\robots\RobotsServiceProvider::class,
        \Core\Packages\not_visited\NotVisitedServiceProvider::class,
        \Core\Packages\notification\NotificationServiceProvider::class,
        \Core\Packages\shop\ShopServiceProvider::class,
//    \Core\Packages\shop\shop\ShopServiceProvider::class,
//         \Core\Packages\shop\companyShop\CompanyShopServiceProvider::class,
//         \Core\Packages\shop\orderShop\OrderShopServiceProvider::class,
//         \Core\Packages\shop\userShop\UsershopServiceProvider::class,
       
    ],
    'prefix' => 'core'
];
