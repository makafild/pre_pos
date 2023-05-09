<?php

namespace App\Http\Controllers\api\Customer\v1\Setting;

use App\Http\Requests\Setting\Setting\StoreSettingRequest;
use App\Http\Requests\Setting\Setting\UpdateSettingRequest;
use App\Models\Setting\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
	public function list()
	{
		$companyId = request('$company_id');

		$settings = Setting::CompanyId($companyId)
			->get();

		return $settings;
	}

	public function oneSignalProxy($uri, Request $request)
	{
//		\Log::info("-------------------------- Request Start -------------------------");
//		\Log::info($uri);
//		\Log::info($request->all());
//		\Log::info($request->headers);
//		\Log::info($request->getQueryString());
//		\Log::info("-------------------------- Request End -------------------------");

		$curl = curl_init();

		$queryString = $request->getQueryString() ? '?' . $request->getQueryString() : '';
		curl_setopt_array($curl, [
			CURLOPT_URL            => "https://onesignal.com/api/v1/{$uri}{$queryString}",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => $request->method(),
			CURLOPT_POSTFIELDS     => json_encode($request->all()),
			CURLOPT_HTTPHEADER     => [
				"Accept: application/json",
				"Content-Type: application/json",
				"cache-control: no-cache",
			],
		]);

		$response = curl_exec($curl);
		$err      = curl_error($curl);

		curl_close($curl);

		if ($err) {
			\Log::info("cURL Error #:" . $err);;
		} else {
			\Log::info($response);
			return json_decode($response, true);
		}
	}
}
