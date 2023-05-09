<?php
/**
 * Created by PhpStorm.
 * User: imohammad
 * Date: 3/30/18
 * Time: 5:23 AM
 */

namespace App\Exports;

use App\Models\User\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerExport implements FromCollection, WithHeadings
{
	const COLUMNS      = [
		"id",
		"email",
		"mobile_number",
		"first_name",
		"last_name",
	];
	const COLUMNS_NAME = [
		"id",
		"email",
		"mobile_number",
		"first_name",
		"last_name",
		"provinces",
		"cities",
		"addresses",
		"categories",
		"title",
	];

	public function headings() :array
	{
		\App::setLocale('fa');

		return collect(self::COLUMNS_NAME)->map(function ($item, $key) {
			return trans('messages.exports.user.' . $item);
		})->toArray();
	}

	public function collection()
	{
		$cities = NULL;

		if (auth()->user()->can('customerSuperIndex', User::class)) {
		} else if (auth()->user()->can('customerIndex', User::class)) {
			if (auth()->user()->kind == User::KIND_COMPANY_ADMIN) {
				$cities = auth()->user()->CompanyUser->Cities->pluck('id')->all();
			} else if (auth()->user()->kind == User::KIND_COMPANY) {
				$cities = auth()->user()->Cities->pluck('id')->all();
			}
		} else {
			abort(500);
		}

		$customer = User::where('users.kind', User::KIND_CUSTOMER)
			->select(self::COLUMNS)
			->with([
				'provinces',
				'cities',
				'addresses',
				'IntroducerCode',
				'categories' => function ($query) {
					$query->select('id', 'constant_fa');
				},
			]);

		if ($cities)
			$customer = $customer->whereCities($cities);

		$customer = $customer->get()
			->each(function ($item, $key) {
				if (isset($item->provinces[0]))
					$item->province = $item->provinces[0]->name;
				if (isset($item->cities[0]))
					$item->cities = $item->cities[0]->name;
				if (isset($item->addresses[0]))
					$item->addresses = $item->addresses[0]->name;
				if (isset($item->categories))
					$item->category = $item->categories->implode('constant_fa', ', ');
			});

		return $customer;
	}
}