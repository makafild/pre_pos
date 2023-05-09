<?php
return [
    'routes' => [
        'deny' => [
            'ignition.healthCheck',
            'ignition.executeSolution',
            'ignition.shareReport',
            'ignition.scripts',
            'ignition.styles',

            'users.logout',
            'users.login'
        ],
        'access' => [
            'users.list' => ['code'=>'01001','name'=>'لیست'],
            'users.store' => ['code'=>'01002','name'=>'ثبت'],
            'users.update' =>['code'=>'01003','name'=>'ویرایش'],
            'users.states' =>['code'=>'01004','name'=>'وضعیت'],
            'users.profile' => ['code'=>'01005','name'=>'پروفایل'],
            'users.users.loginAs' => ['code'=>'01006','name'=>' ورود به عنوان مشتری'],

            'roles.list' =>  ['code'=>'02001','name'=>'لیست'],
            'roles.store' => ['code'=>'02002','name'=>'ثبت'],
            'roles.update' => ['code'=>'02003','name'=>'ویرایش'],

            'message.index'=>['code'=>'03001','name'=>'لیست'],
            'message.store'=>['code'=>'03002','name'=>'ثبت'],
            'message.show'=>['code'=>'03003','name'=>'نمایش'],

            'news.index'=>['code'=>'04001','name'=>'لیست'],
            'news.show'=>['code'=>'04002','name'=>'نمایش'],
            'news.top'=>['code'=>'04003','name'=>''],//--

            'company.index'=>['code'=>'05001','name'=>'لیست'],
            'company.show'=>['code'=>'05002','name'=>'نمایش'],
            'company.superior'=>['code'=>'05003','name'=>''],//--
            'company.products'=>['code'=>'05004','name'=>'محصولات'],
            'company.tree'=>['code'=>'05005','name'=>''],//--
            'company.score.store'=>['code'=>'05006','name'=>''],//--
            'company.score.show'=>['code'=>'05007','name'=>''],//--
        ],
        'parents'=>[
            'users'=>'کاربران',
            'roles'=>'نقش ها',
            'message'=>'پیام ها',
            'news'=>'اخبار',
            'company'=>'شرکت ها',
        ]
    ]
];
