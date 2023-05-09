<?php

namespace core\Packages\setting\src\controllers;

use App\Models\Monitor\Monitor;
use App\Models\Setting\Setting;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\setting\src\request\SettingRequest;

class SettingController extends CoreController
{


    public function list()
    {
//        $companyId = NULL;
//        if (request('company_id'))
//            $companyId = request('company_id');
        $companyId = auth('api')->user()->company_id;
        $setting = Setting::CompanyId($companyId)
            ->latest()->jsonPaginate(1000);
        return $setting;
    }

    public function show($id)
    {
        $setting = Setting::findOrFail($id);
        return $setting;

    }

    public function update(SettingRequest $request)
    {
        foreach ($request->setting as $setting) {
            Setting::where('id', $setting['id'])->update([
                'value' => $setting['value'],
                'company_id' => auth('api')->user()->company_id
            ]);
        }

        return [
            'status' => true,
            'message' => trans('messages.setting.setting.update')
        ];
    }
    public function crmRun()
    {
        $command1 = "openfortivpn -c /etc/openfortivpn/config >/dev/null 2>&1";
        $returnVar = NULL;
        $output = NULL;
        $statusVPN=Setting::where("key","VPNSTATUS")->first();
         $statusVPN->value=1;
         $statusVPN->save();
        exec($command1, $output, $returnVar);
        return [
            'status' => true,
            'message' => "vpn  اجرا شد"
        ];
    }
    public function crmStatus()
    {
        $statusVPN=Setting::where("key","VPNSTATUS")->first();
        return $statusVPN->value;
    }


   




}
