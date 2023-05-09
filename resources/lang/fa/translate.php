<?php
/**
 * Created by PhpStorm.
 * User: imohammad
 * Date: 3/11/18
 * Time: 2:50 PM
 */
return [
	'setting' => [
		'constant' => [
			'unit'              => 'واحد',
			'customer_category' => 'دسته بندی مشتری',
			'additions'         => 'اضافات',
			'sublayer'         => 'تولید کننده',
			'deductions'         => 'کسورات',
			'product_label'     => 'لیبل محصول',
			'payment_method'    => 'روش پرداخت',
			'invoice_title'     => 'عنوان صورتحساب',
			'tax'     => 'عوارض و مالیات',
            "product_type_1" => "نوع 1",
            "product_type_2" => "نوع 2"
		],
	],
	'user'    => [
		'status'       => [
			'active'   => 'فعال',
			'inactive' => 'غیر فعال',
		],
		'permission' => [
			'superIndex'        => 'لیست (ادمین)',
			'index'             => 'لیست',
			'store'             => 'ثبت جدید',
			'superStore'        => 'ثبت جدید (ادمین)',
			'create'            => 'ثبت جدید',
			'superShow'         => 'نمایش (ادمین)',
			'show'              => 'نمایش',
			'superUpdate'       => 'به روزرسانی (ادمین)',
			'update'            => 'به روزرسانی',
			'destroy'           => 'حذف',
			'confirm'           => 'تایید',
			'superDestroy'      => 'حذف (ادمین)',
			'changeStatus'      => 'تغییر وضعیت',
			'superChangeStatus' => 'تغییر وضعیت (ادمین)',
			'superProfile'      => 'تغییر پروفایل (ادمین)',
			'profile'           => 'پروفایل (ادمین)',
			'loginAs'           => 'ورود به عنوان کاربر (ادمین)',
			'score'             => 'امتیاز دادن',


			'customerIndex'       => 'لیست',
			'customerStore'       => 'ثبت جدید',
			'customerShow'        => 'نمایش',
			'customerSuperUpdate' => 'به روزرسانی (ادمین)',
			'customerUpdate'      => 'به روزرسانی',
			'customerDestroy'     => 'حذف',

			'product'                 => 'محصولات',
			'category'                => 'دسته‌بندی',
			'news'                    => 'اخبار',
			'survey'                  => 'نظرسنجی',
			'suggestion'              => 'انتقادات و پیشنهادات',
			'message'                 => 'ارسال پیام',
			'report'                  => 'گزارش از شرکت',
			'report_turn_overs'       => 'گزارش گردش مالی',
			'report_account_balances' => 'گزارش تراز حساب',
			'report_factors'          => 'گزارش  فاکتورها',
			'report_return_cheques'   => 'گزارش  چک‌ها',
		],
		'role'       => [
			'controller' => [
				'Message'        => 'پیام‌ها',
				'News'           => 'اخبار',
				'Slider'         => 'اسلایدر‌ها',
				'Suggestion'     => 'انتقادات و پیشنهادات',
				'Survey'         => 'نظرسنجی‌ها',
				'Notification'   => 'اعلان‌ها',
				'Company'        => 'شرکت‌ها',
				'Customer'       => 'مشتری‌ها',
				'Order'          => 'سفارشات',
				'Coupon'         => 'کدهای‌ تخفیف',
				'Invoice'        => 'فاکتورها',
				'Brand'          => 'برندها',
				'Category'       => 'دسته‌بندی‌ها',
				'Product'        => 'محصولات',
				'Promotions'     => 'بسته‌های تبایغاتی',
				'Constant'       => 'ثابت‌ها',
				'Setting'        => 'تنظیمات',
				'User'           => 'کاربران',
				'PriceClass'     => 'کلاس‌های قیمت',
				'Role'           => 'نقش‌ها',
				'IntroducerCode' => 'کدمعرف',
				'Photo'          => 'فایل',
				'VisitTour'      => 'تور ویزیت',
				'PaymentMethod'  => 'نحوه پرداخت',
			],
			'namespace'  => [
				'Common'   => 'سایر',
				'Company'  => 'شرکت‌‌ها',
				'Customer' => 'مشتری‌ها',
				'Order'    => 'سفارشات',
				'Billing'  => 'صورتحساب‌ها',
				'Product'  => 'محصولات',
				'Setting'  => 'تنظیمات',
				'User'     => 'کاربران',

				'customer_api' => 'دسترسی‌های مشتری',
			],
		],
	],
	'order'   => [
		'order'   => [
			'registered'       => 'ثبت شده',
			'confirmed'        => 'تایید شده',
			'posted'           => 'ارسال شده',
			'send_in_progress' => 'در حال ارسال به سرور',
			'rejected'         => 'رد شده',
		],
		'payment' => [
			'default'      => '',
			'success'      => 'موفق',
			'depending'    => 'منتظر پرداخت',
			'unsuccessful' => 'ناموفق',
		],
	],
	'product' => [
		'status'    => [
			'available'   => 'موجود',
			'unavailable' => 'ناموجود',
		],
        'show_status'    => [
            'active'   => 'فعال',
            'inactive' => 'غیر فعال',
        ],
		'promotions' => [
			'amount'     => 'ریالی',
			'percentage' => 'درصدی',
			'basket'     => 'سبد',
			'stairs'     => 'پله ای',
			'volumetric'     => 'حجمی',
			'row'     => 'سطری',
			'percentage_strip'     => 'درصدی-پله ای',
			'kalai'     => 'سبدی-درصدی',
		],
	],
    'slider'       => [
        "status"=>[
            'active'   => 'فعال',
            'inactive' => 'غیر فعال',
        ],
        "kind"=>[
            'link'    => 'لینک',
            'company' => 'شرکت',
            'product' => 'محصول',
        ]
    ],
	'common'  => [
		'file'   => [
			'have'     => 'دارد',
			'not_have' => 'ندارد',
		],
	],
	'billing' => [
		'invoice' => [
			'store'   => 'ثبت شده',
			'undone'  => 'انجام نشده',
			'done'    => 'انجام شده',
			'confirm' => 'تایید شده',
		],
	],
];
