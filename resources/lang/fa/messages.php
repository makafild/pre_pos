<?php

/**
 * Created by PhpStorm.
 * User: mo121ntazeri@gmail.com
 * Date: 2/20/18
 * Time: 11:20 PM
 */

return [
    'common'   => [
        'photo'      => [
            'store' => 'تصویر با موفقیت ثبت شد.',
        ],
        'news'       => [
            'store'   => 'خبر با موفقیت ثبت شد.',
            'update'  => 'خبر با موفقیت به روزرسانی شد.',
            'destroy' => 'خبر با موفقیت حذف شد.',
        ],
        'survey'     => [
            'store'   => 'نظرسنجی با موفقیت ثبت شد.',
            'update'  => 'نظرسنجی با موفقیت به روزرسانی شد.',
            'destroy' => 'نظرسنجی با موفقیت حذف شد.',
        ],
        'slider'     => [
            'store'        => 'اسلایدر با موفقیت ثبت شد.',
            'update'       => 'اسلایدر با موفقیت به روزرسانی شد.',
            'destroy'      => 'اسلایدر با موفقیت حذف شد.',
            'changeStatus' => 'وضعیت اسلایدر با موفقیت به‌روزرسانی شد.',
        ],
        'suggestion' => [
            'destroy' => 'پشنهاد با موفقیت حذف شد.',
        ],
    ],
    'setting'  => [
        'constant' => [
            'store'   => 'با موفقیت ثبت شد.',
            'update'  => 'با موفقیت به روزرسانی شد.',
            'destroy' => 'با موفقیت حذف شد.',
        ],
        'setting'  => [
            'update' => 'تنظیمات با موفقیت به روزرسانی شد.',
        ],
    ],
    'gis' => [
        'route' => [
            'store'                => 'مسیر با موفقیت ثبت شد.',
            'update'               => 'مسیر با موفقیت به روزرسانی شد.',
            'destroy'              => 'مسیر با موفقیت حذف شد.',
        ],
        'area' => [
            'store'                => 'منطقه با موفقیت ثبت شد.',
            'update'               => 'منطقه با موفقیت به روزرسانی شد.',
            'destroy'              => 'منطقه با موفقیت حذف شد.',
        ],
        'point' => [
            'store'                => 'نقاط با موفقیت ثبت شد.',
            'update'               => 'نقاط با موفقیت به روزرسانی شد.',
            'destroy'              => 'نقاط با موفقیت حذف شد.',
        ]
    ],
    'product'  => [
        'category'   => [
            'store'                => 'دسته‌بندی با موفقیت ثبت شد.',
            'update'               => 'دسته‌بندی با موفقیت به روزرسانی شد.',
            'parent_has_products'  => 'دسته‌بندی دارای محصول می‌باشد.' .
                'محصولات آن را حذف کنید تا بتوانید به آن زیر شاخه اضافه کنید.',
            'destroy'              => 'دسته‌بندی با موفقیت حذف شد.',
            'destroy_has_children' => 'دسته‌بندی حذف نشد.'
                . 'دسته‌بندی دارای زیردسته می‌باشد.',
            'destroy_has_products' => 'دسته‌بندی حذف نشد.'
                . 'دسته‌بندی دارای محصول می‌باشد.',
        ],
        'brand'      => [
            'store'   => 'برند با موفقیت ثبت شد.',
            'update'  => 'برند با موفقیت به روزرسانی شد.',
            'destroy' => 'برند با موفقیت حذف شد.',
        ],
        'product'    => [
            'store'        => 'محصول با موفقیت ثبت شد.',
            'update'       => 'محصول با موفقیت به روزرسانی شد.',
            'destroy'      => 'محصول با موفقیت حذف شد.',
            'changeStatus' => 'وضعیت محصول با موفقیت به‌روزرسانی شد',
        ],
        'promotions' => [
            'store'        => 'بسته تبلیغاتی با موفقیت ثبت شد.',
            'update'       => 'بسته تبلیغاتی با موفقیت به روزرسانی شد.',
            'destroy'      => 'بسته تبلیغاتی با موفقیت حذف شد.',
            'changeStatus' => 'وضعیت بسته تبلیغاتی با موفقیت به‌روزرسانی شد.',
        ],
    ],
    'company'  => [
        'company' => [
            'store'        => 'شرکت با موفقیت ثبت شد.',
            'update'       => 'شرکت با موفقیت به روزرسانی شد.',
            'destroy'      => 'شرکت با موفقیت حذف شد.',
            'changeStatus' => 'وضعیت شرکت با موفقیت به‌روزرسانی شد.',
        ],
    ],
    'customer' => [
        'customer' => [
            'store'        => 'مشتری با موفقیت ثبت شد.',
            'update'       => 'مشتری با موفقیت به روزرسانی شد.',
            'destroy'      => 'مشتری با موفقیت حذف شد.',
            'changeStatus' => 'وضعیت مشتری با موفقیت به‌روزرسانی شد.',
            'score'        => 'امتیاز مشتری با موفقیت به‌روزرسانی شد.',
        ],
    ],
    'visitor' => [
        'visitor' => [
            'store' => [
                'visitor' => 'ویزیتور با موفقیت ثبت شد.',
                'super_visitor' => 'سرپرست با موفقیت ثبت شد.',
            ],
            'update' => [
                'visitor' => 'ویزیتور با موفقیت به روزرسانی شد.',
                'super_visitor' => 'سرپرست با موفقیت به روزرسانی شد.',
            ],
            'destroy'      => 'ویزیتور با موفقیت حذف شد.',
            'changeStatus' => 'ویزیتور با موفقیت به‌روزرسانی شد.',
        ],
        'position' => [
            'store'        => 'موقعیت ویزیتور با موفقیت ثبت شد.'
        ],
    ],
    'order'    => [
        'order'  => [
            'update'       => 'سفارش با موفقیت به روزرسانی شد.',
            'changeStatus' => 'وضعیت سفارش با موفقیت به‌روزرسانی شد و پس از تایید در سیستم مرجع اعمال می‌گردد.',
            'sms'          => 'با احترام \nشرکت :company \n\n تعداد :count سفارش در مجموع به مبلغ :amount ریال در تاریخ :date ثبت شده است.\n\n شرکت نرم‌افزاری پروشا',
        ],
        'coupon' => [
            'store'  => 'کد تخفیف با موفقیت ثبت شد.',
            'update' => 'کد تخفیف با موفقیت به روزرسانی شد.',
        ],
    ],
    'Billing'  => [
        'Invoice' => [
            'store'        => 'صورتحساب با موفقیت ثبت شد.',
            'update'       => 'صورتحساب با موفقیت به روزرسانی شد.',
            'destroy'      => 'صورتحساب با موفقیت حذف شد.',
            'changeStatus' => 'وضعیت صورتحساب با موفقیت به‌روزرسانی شد.',
        ],
    ],
    'user'     => [
        'user'        => [
            'store'          => ' کاربر با موفقیت ثبت شد.',
            'update'         => ' کاربر با موفقیت به روزرسانی شد.',
            'changePassword' => ' کلمه عبور با موفقیت به روزرسانی شد.',
            'updateProfile'  => ' پروفایل با موفقیت به روزرسانی شد.',
        ],
        'role'        => [
            'store'    => 'نقش کاربری با موفقیت ثبت شد.',
            'update'   => 'نقش کاربری با موفقیت به روزرسانی شد.',
            'destroy'  => 'نقش کاربری با موفقیت حذف شد.',
            'notEmpty' => 'نفش کاربری خالی نمی باشد.',
            'assign'   => 'نقش کاربری با موفقیت انتساب شد.',
            'revoke'   => 'نقش کاربری با موفقیت لغو شد.',
        ],
        'price_class' => [
            'store'  => 'نوع مشتری با موفقیت ثبت شد.',
            'delete'  => 'نوع مشتری با موفقیت حذف شد.',
            'update' => 'نوع مشتری با موفقیت به روزرسانی شد.',
        ],
        'category'    => [
            'store'   => 'دسته‌بندی کاربری با موفقیت ثبت شد.',
            'update'  => 'دسته‌بندی کاربری با موفقیت به روزرسانی شد.',
            'destroy' => 'دسته‌بندی کاربری با موفقیت حذف شد.',
        ],
    ],
    'api'      => [
        'version' => [
            'store' => 'ورژن با موفقیت ذخیره شد',
            'destore' => 'ورژن با موفقیت حذف شد',
            'update' => 'ورژن با موفقیت ویرایش شد',
        ],
        'customer' => [
            'user'    => [
                'sms_code'                => 'کد فعال سازی شما :code می‌باشد.',
                'register'                => 'حساب کاربری با موفقیت ساخته شد.',
                'login_fail'              => 'اطلاعات وارد شده صحیح نمی باشد.',
                'login_success'           => 'شما با موفقیت وارد سیستم شدید.',
                'inactive_user'           => 'حساب کاربری شما غیرفعال می باشد',
                'update_success'          => 'حساب کاربری با موفقیت بروزرسانی شد.',
                'add_favorite'            => 'محصول به علاقه مندی ها اضافه شد.',
                'delete_favorite'         => 'محصول از علاقه مندی ها حذف شد.',
                'add_company_favorite'    => 'شرکت به علاقه مندی ها اضافه شد.',
                'delete_company_favorite' => 'شرکت از علاقه مندی ها حذف شد.',
            ],
            'product' => [
                'score' => 'امتیاز :score برای محصول ":name" با موفقیت ثبت شد.',
            ],

            'company' => [
                'score' => 'امتیاز :score برای شرکت ":name" با موفقیت ثبت شد.',
            ],
            'common'  => [
                'suggestion_store' => 'پیشنهاد شما با موفقیت ذخیره شد.',
                'message_store'    => 'پیام شما با موفقیت ذخیره شد.',
                'answer_store'     => 'پاسخ شما با موفقیت ذخیره شد.',
            ],
            'order'   => [
                'order'  => [
                    'store' => 'تعداد :count سفارش به ازای درخواست شما در سیستم ثبت شد.',
                    'update' => 'ُسفارش شما با موفقیت بروزرسانی شد.',
                ],
                'coupon' => [
                    'check'     => 'این کد دارای :percentage درصد تخفیف است.',
                    'not_check' => 'این کد تخفیف وجود ندارد.',
                ],
            ],
        ],

        'company' => [
            'category' => [
                'store' => 'گروه کالا با موفقیت ثبت شد.',
            ],
            'product'  => [
                'store'        => 'محصولات با موفقیت ثبت شد.',
                'update'       => 'محصولات با موفقیت به روزرسانی شد.',
                'changeStatus' => 'محصولات با موفقیت به روزرسانی شد.',
            ],
            'customer' => [
                'store'  => 'مشتریان با موفقیت ثبت شد.',
                'update' => 'مشتریان با موفقیت به روزرسانی شد.',
            ],
        ],
    ],
    'exports'  => [
        'order'    => [
            'id'                        => 'شناسه',
            'price_without_promotions'  => 'قیمت بدون پرومشن‌ها',
            'promotion_price'           => 'جمع پرومشن‌ها',
            'price_with_promotions'     => 'قیمت با پرومشن‌ها',
            'amount_promotion'          => 'تخفیف زیر فاکتور',
            'discount'                  => 'تخفیف',
            'final_price'               => 'قیمت نهایی',
            'customer_id'               => 'شناسه مشتری',
            'company_id'                => 'شناسه شرکت',
            'coupon_id'                 => 'شناسه کوپن',
            'tracker_url'               => 'آدرس Tracker روی نقشه',
            'status'                    => 'وضعیت',
            'status_translate'          => 'وضعیت',
            'date_of_sending'           => 'تاریخ ارسال',
            'date_of_sending_translate' => 'تاریخ ارسال شمسی',
            'created_at'                => 'تاریخ ایجاد',
            'updated_at'                => 'تاریخ بروزرسانی',
        ],
        'product'  => [
            'id'             => 'شناسه',
            'referral_id'    => 'کدمرجع',
            'name_fa'        => 'نام فارسی',
            'name_en'        => 'نام انگلیسی',
            'description'    => 'توضیحات',
            'master_status'  => 'نمایش واحد مبنا',
            'slave_status'   => 'نمایش واحد جز',
            'slave2_status'  => 'نمایش واحد جز ۲',
            'quotas_master'  => 'سهمیه واحد مبنا',
            'quotas_slave'   => 'سهمیه واحد جز',
            'quotas_slave2'  => 'سهمیه واحد جز ۲',
            'per_master'     => 'در هر مبنا',
            'per_slave'      => 'در هر جز',
            'purchase_price' => 'قیمت خرید',
            'sales_price'    => 'قیمت فروش',
            'consumer_price' => 'قیمت مشتری',
            'discount'       => 'تخفیف',
            'brand_id'       => 'برند',
            'category_id'    => 'دسته‌بندی',
            'photo_id'       => 'تصویر',
            'company_id'     => 'شرکت',
            'score'          => 'امتیاز',
            'status'         => 'وضعیت',
            'created_at'     => 'ایجاد',
            'updated_at'     => 'بروزرسانی',
            'deleted_at'     => 'حذف',
            'price'          => 'قیمت',
            'markup_price'   => 'سود شرکت',
            'show_status'    => 'وضعیت نمایش',
        ],
        'user'     => [
            'id'                         => 'شناسه',
            'email'                      => 'ایمیل',
            'mobile_number'              => 'موبایل',
            'mobile_number_confirmation' => 'تایید موبایل',
            'name_fa'                    => 'نام فارسی',
            'name_en'                    => 'نام انگلیسی',
            'economic_code'              => 'کد اقتصادی',
            'api_url'                    => 'آدرس',
            'score'                      => 'امتیاز',
            'first_name'                 => 'نام',
            'last_name'                  => 'نام خانوادگی',
            'national_id'                => 'کدملی',
            'kind'                       => 'نوع',
            'status'                     => 'وضعیت',
            'creator_id'                 => 'سازنده',
            'company_id'                 => 'شرکت',
            'category_id'                => 'موضوع',
            'photo_id'                   => 'تصویر',
            'end_at'                     => 'پایان',
            'created_at'                 => 'ایجاد',
            'updated_at'                 => 'بروزرسانی',
            'deleted_at'                 => 'حذف',
            'title'                      => 'عنوان',
            'provinces'                  => 'استان',
            'cities'                     => 'شهر',
            'addresses'                  => 'آدرس',
            'categories'                 => 'گروه',
        ],
        'customer' => [
            'id'            => 'شناسه',
            'referral_id'   => '',
            'first_name'    => 'نام',
            'last_name'     => 'نام خانوادگی',
            'email'         => 'ایمیل',
            'mobile_number' => 'موبایل',
            'company_id'    => 'شرکت',
            'customer_id'   => 'مشتری',
            'created_at'    => 'ساخت',
            'updated_at'    => 'بروزرسانی',
            'title'         => 'عنوان',
        ],
    ],


];