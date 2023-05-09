<?php

namespace App\Http\Controllers\api\Customer\v1\Setting;

use App\Http\Requests\Setting\Province\StoreProvinceRequest;
use App\Models\Setting\City;
use App\Models\Setting\Country;
use App\Models\Setting\Province;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
	public function countries()
	{
		/** @var Country[] $countries */
		$countries = Country::get();

		$data = [];
		foreach ($countries as $country) {
			$data[] = [
				'id'   => $country->id,
				'name' => $country->name,
			];
		}

		return $data;
	}

	public function provinces(Request $request)
	{
		if (is_array($request->countries)) {
			$countriesId = $request->countries;
		} else {
			$countriesId = [$request->countries];
		}

		/** @var Province[] $provinces */
		$provinces = Province::whereIn('country_id', $countriesId)->get();

		$data = [];
		foreach ($provinces as $province) {
			$data[] = [
				'id'   => $province->id,
				'name' => $province->name,
			];
		}

		return $data;
	}

	public function cities(Request $request)
	{
		if (is_array($request->provinces)) {
			$provincesId = $request->provinces;
		} else {
			$provincesId = [$request->provinces];
		}

		/** @var City[] $cities */
		$cities = City::whereIn('province_id', $provincesId)->get();

		$data = [];
		foreach ($cities as $city) {
			$data[] = [
				'id'   => $city->id,
				'name' => $city->name,
			];
		}

		return $data;
	}
}
