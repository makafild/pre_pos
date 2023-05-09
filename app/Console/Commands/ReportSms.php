<?php

namespace App\Console\Commands;

use Core\Packages\user\Users;
use Illuminate\Support\Carbon;
use Core\Packages\user\Address;
use Core\System\Helper\CrmSabz;
use Illuminate\Console\Command;
use App\Events\User\SendSMSEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Hekmatinasser\Verta\Facades\Verta;
use Core\Packages\customer\CompanyCustomer;
use Symfony\Component\VarDumper\VarDumper;

class ReportSms extends Command
{

    protected $signature = 'reportsms:start';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $v = verta();
        $companys = Users::Company()->with('Orders')
            ->whereHas('Orders', function ($q) {
                $q->whereDay('created_at', '=', date('d'))->where('status','registered');
            })
            ->get();


        foreach ($companys as $company) {

            $message = "
 با سلام
 شرکت  " . $company->name_fa . "
شما " . $company->Orders->count() . " عدد سفارش ثبت شده در سامانه سراسری هانا دارید.
لطفا در اولین فرصت جهت تعیین وضعیت آن اقدام فرمایید.
جمع مبلغ سفارشات امروز مورخ " . $v->formatJalaliDate() . " مبلغ " . number_format($company->Orders->sum('final_price'), 0, '', ',') . " ریال میباشد.
با تشکر            ";

           // event(new SendSMSEvent($message, $company->mobile_number));
            event(new SendSMSEvent($message, "09331014716"));
        }
    }
}
