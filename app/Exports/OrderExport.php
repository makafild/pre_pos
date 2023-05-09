<?php
/**
 * Created by PhpStorm.
 * User: imohammad
 * Date: 3/30/18
 * Time: 5:23 AM
 */

namespace App\Exports;

use App\Models\Order\Order;
use App\Models\User\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromCollection, WithHeadings
{
	const COLUMNS = [
		"id",
		"price_without_promotions",
		"promotion_price",
		"price_with_promotions",
		"amount_promotion",
		"discount",
		"final_price",
		"customer_id",
		"company_id",
		"coupon_id",
		"tracker_url",
		"status",
		"date_of_sending",
		"created_at",
		"updated_at",
	];

	public function headings(): array
	{
		ini_set('memory_limit','1G');

		\App::setLocale('fa');

//		$customer = collect(User::select(self::COLUMNS)->first())->keys()->map(function ($item, $key) {
//			return trans('messages.exports.user.' . $item) . " مشتری";
//		})->flip()->toArray();
//
//		$company  = collect(User::first())->keys()->map(function ($item, $key) {
//			return trans('messages.exports.user.' . $item) . " شرکت";
//		})->flip()->toArray();

		$data = collect(Order::select(self::COLUMNS)->first())->keys()->map(function ($item, $key) {

//			return $item;
			return trans('messages.exports.order.' . $item);

		})
//			->merge($customer)
//			->merge($company)
			->toArray();

		$data[] = 'تاریخ ارسال شمسی';

		return $data;
	}

	public function collection()
	{
		if (auth()->user()->can('superIndex', Order::class)) {
			return Order::with([
				'customer',
			])->select(self::COLUMNS)->get();

		} elseif (auth()->user()->can('index', Order::class)) {
			$companyId = auth()->user()->company_id;

			return Order::CompanyId($companyId)->select(self::COLUMNS)
				->with([
					'customer',
				])->get();
		}

		return Order::whereNull('updated_at')->whereNotNull('updated_at');
	}

//	/**
//	 * @param mixed $row
//	 *
//	 * @return array
//	 */
//	public function map($row): array
//	{
//		return collect($row)
//			->only($this->headings())
////			->merge($row->customer)
////			->merge(collect($row->company)->values())
//			->toArray();
//	}
}