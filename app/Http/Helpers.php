<?php
namespace App\Http;
class Helpers{

    static  function  numberToEnglish($word)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];
        $num = range(0, 9);

        $word = str_replace($persian, $num, $word);
        $word = str_replace($arabic, $num, $word);

        return $word;
    }

}
