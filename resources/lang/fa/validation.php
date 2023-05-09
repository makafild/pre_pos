<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    "accepted"             => ":attribute باید پذیرفته شده باشد.",
    "active_url"           => "آدرس :attribute معتبر نیست",
    "after"                => ":attribute باید تاریخی بعد از :date باشد.",
    "alpha"                => ":attribute باید شامل حروف الفبا باشد.",
    "alpha_dash"           => ":attribute باید شامل حروف الفبا و عدد و خظ تیره(-) باشد.",
    "alpha_num"            => ":attribute باید شامل حروف الفبا و عدد باشد.",
    "array"                => ":attribute باید شامل آرایه باشد.",
    "before"               => ":attribute باید تاریخی قبل از :date باشد.",
    "between"              => [
        "numeric" => ":attribute باید بین :min و :max باشد.",
        "file"    => ":attribute باید بین :min و :max کیلوبایت باشد.",
        "string"  => ":attribute باید بین :min و :max کاراکتر باشد.",
        "array"   => ":attribute باید بین :min و :max آیتم باشد.",
    ],
    "boolean"              => "The :attribute field must be true or false",
    "confirmed"            => ":attribute با تاییدیه مطابقت ندارد.",
    "date"                 => ":attribute یک تاریخ معتبر نیست.",
    "date_format"          => ":attribute با الگوی :format مطاقبت ندارد.",
    "different"            => ":attribute و :other باید متفاوت باشند.",
    "digits"               => ":attribute باید :digits رقم باشد.",
    "digits_between"       => ":attribute باید بین :min و :max رقم باشد.",
    "email"                => "فرمت :attribute معتبر نیست.",
    "exists"               => ":attribute انتخاب شده، معتبر نیست.",
    "image"                => ":attribute باید تصویر باشد.",
    "in"                   => ":attribute انتخاب شده، معتبر نیست.",
    "integer"              => ":attribute باید مقدار عددی باشد.",
    "ip"                   => ":attribute باید IP آدرس معتبر باشد.",
    "max"                  => [
        "numeric" => ":attribute نباید بزرگتر از :max باشد.",
        "file"    => ":attribute نباید بزرگتر از :max کیلوبایت باشد.",
        "string"  => ":attribute نباید بیشتر از :max کاراکتر باشد.",
        "array"   => ":attribute نباید بیشتر از :max آیتم باشد.",
    ],
    "mimes"                => ":attribute باید یکی از فرمت های :values باشد.",
    "min"                  => [
        "numeric" => ":attribute نباید کوچکتر از :min باشد.",
        "file"    => ":attribute نباید کوچکتر از :min کیلوبایت باشد.",
        "string"  => ":attribute نباید کمتر از :min کاراکتر باشد.",
        "array"   => ":attribute نباید کمتر از :min آیتم باشد.",
    ],
    "not_in"               => ":attribute انتخاب شده، معتبر نیست.",
    "numeric"              => ":attribute باید شامل عدد باشد.",
    "regex"                => ":attribute یک فرمت معتبر نیست",
    "required"             => "فیلد :attribute الزامی است",
    "required_if"          => "فیلد :attribute هنگامی که :other برابر با :value است، الزامیست.",
    "required_with"        => ":attribute الزامی است زمانی که :values موجود است.",
    "required_with_all"    => ":attribute الزامی است زمانی که :values موجود است.",
    "required_without"     => ":attribute الزامی است زمانی که :values موجود نیست.",
    "required_without_all" => ":attribute الزامی است زمانی که :values موجود نیست.",
    "same"                 => ":attribute و :other باید مانند هم باشند.",
    "size"                 => [
        "numeric" => ":attribute باید برابر با :size باشد.",
        "file"    => ":attribute باید برابر با :size کیلوبایت باشد.",
        "string"  => ":attribute باید برابر با :size کاراکتر باشد.",
        "array"   => ":attribute باسد شامل :size آیتم باشد.",
    ],
    "timezone"             => "The :attribute must be a valid zone.",
    "unique"               => ":attribute قبلا انتخاب شده است.",
    "url"                  => "فرمت آدرس :attribute اشتباه است.",

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom'     => [
        'orders.*.id' => [
            'exists' => 'سفارش انتخاب شده دارای وضعیت ثبت شده نیست.',
        ],

        'price_classes.*.price_class.id' => [
            'required' => 'کلاس قیمت باید انتخاب شود یا سطر آن حذف شود.',
        ],

        'row_version' => [
            'exists' => 'این فرم توسط کاربر دیگری به‌روزرسانی شده است، لطفا تغییرات خود را دوباره اعمال کنید.',
        ],

        'jalali'    => ' :attribute تاریخ صحیحی نمی‌باشد.',
        'is_mobile' => 'تلفن همراه وارد شده معتبر نمی باشد.',
        'lat_long'  => 'مختصات وارد شده معتبر نمی باشد.',


    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */
    'attributes' => [
        "name"                  => "نام",
        "username"              => "نام کاربری",
        "email"                 => "پست الکترونیکی",
        "first_name"            => "نام",
        "last_name"             => "نام خانوادگی",
        "password"              => "رمز عبور",
        "password_confirmation" => "تاییدیه ی رمز عبور",
        "province" => "استان",
        "city" => "شهر",
        "city_id" => "شهر ها",
        "country" => "کشور",
        "address" => "نشانی",
        "questions" => "سوالات",
        "questions.*.question" => "سوالات",
        "phone" => "تلفن",
        "mobile" => "تلفن همراه",
        "age" => "سن",
        "sex" => "جنسیت",
        "gender" => "جنسیت",
        "day" => "روز",
        "month" => "ماه",
        "year" => "سال",
        "hour" => "ساعت",
        "minute" => "دقیقه",
        "second" => "ثانیه",
        "title" => "عنوان",
        "text" => "متن",
        "content" => "محتوا",
        "description" => "توضیحات",
        "excerpt" => "گلچین کردن",
        "date" => "تاریخ",
        "time" => "زمان",
        "available" => "موجود",
        "size" => "اندازه",
        "visitors" => "ویزیتور",
        "version" => "ورژن",
        "time_finished" => "زمان اتمام",

        "name_fa"  => "نام فارسی",
        "name_en"  => "نام انگلیسی",
        "photo_id" => "تصویر",
        "end_at"   => "تاریخ پایان",

        "constant" => "ثابت",
        "kind"     => "نوع",

        "economic_code" => "شماره اقتصادی",
        "mobile_number" => "شماره موبایل",
        "countries"     => "کشور‌ها",
        "provinces"     => "استان‌ها",
        "province_id"     => "استان‌ها",
        "cities"        => "شهرها",

        "addresses" => "آدرس‌ها",
        "contacts"  => "راه‌های ارتباطی",

        "addresses.*.address"     => "آدرس",
        "addresses.*.postal_code" => "کدپستی",

        "coupon"     => "کد تخفیف",
        "coupon_percentage" => "مقدار تخیف",
        "coupon_start_at" => "تاریخ شروع",
        "coupon_end_at" => "تاریخ اتمام",
        "discount_max" => "بیشترین میزان تخفیف",
        "percentage" => "مقدار تخفیف",

        "score" => "امتیاز",

        "constant_en" => "نام انگلیسی",
        "constant_fa" => "نام فارسی",
        "width" => "ابعاد",
        "weight" => "وزن",
        "number_of_page" => "تعداد صفحه/برگ",



        "master_unit.id" => "واحد مبنا",
        "slave_unit.id"  => "واحد جز",
        "slave2_unit.id" => "واحد جز 2",
        "brand.id"       => "برند",
        "category.id"    => "دسته‌بندی",
        "brands"         => "برندها",

        "master_status" => "نمایش واحد مبنا",
        "slave_status"  => "نمایش واحد جز",
        "slave2_status" => "نمایش واحد جز 2",

        "introduction_id" => "کد معرف",
        "code"            => "کد",
        "photo"           => "تصویر",
        "categories"      => "صنف",

        "direction" => "مسیر",
        "visitor" => "توضیحات",
        "visit_date" => "تاریخ توزیع",
        "visit_time" => "ساعت توزیع",
        "area_id" => "شناسه منطقه",
        "product_type_1" => "نوع 1",
        "product_type_2" => "نوع 2",
        "customer_group" => 'فعالیت تخصصی',
        "introduction_source" => 'منبع ورودی',
        "customer_category" => 'زمینه فعالیت',
        "manager_mobile_number" => 'شماره موبایل مدیریت',
        "status.name" =>'وضعیت',
        "company_id" =>'شرکت',
        "customer_class" =>'دسته بندی مشتری',
        "deliver_date.*" =>'تاریخ',

    ],
    'uniques' => [
        "users_email"=>'ایمیل',
        "users_mobile_number"=>'شماره همراه',
        "roles_name"=>'شماره همراه'
    ],

    "The given data was invalid." => 'خطایی رخ داده است.',

    "survey_questions_option_required" => 'پاسخ سوال :index خالی است.',

    "order_additions_id_exists"      => 'ID اضافات :index سفارش وجود ندارد.',
    "order_additions_key_required"   => 'عنوان اضافات :index سفارش الزامی است.',
    "order_additions_value_required" => 'مقدار اضافات :index سفارش الزامی است.',

    "order_products_quotas_max" => 'حداکثر مقدار برای کالای ":name" :max :unit می‌باشد.',
    "order_product_not_available" =>'محصول :product (ناموجود) می باشد.',


];
