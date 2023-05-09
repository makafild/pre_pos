<?php
/**
 * Created by PhpStorm.
 * User: imohammad
 * Date: 3/30/18
 * Time: 5:23 AM
 */

namespace App\Exports;

use App\Models\Product\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection, WithHeadings
{
	const COLUMNS = ["id", "referral_id", "name_fa", "name_en", "description", "quotas_master", "quotas_slave", "quotas_slave2", "master_status", "slave_status", "slave2_status", "per_master", "per_slave", "purchase_price", "sales_price", "consumer_price", "discount", "brand_id", "category_id", "photo_id", "company_id", "score", "status", "created_at", "updated_at", "deleted_at", "show_status"];

	public function headings(): array
	{
		\App::setLocale('fa');

		return collect(Product::select(self::COLUMNS)->first())->keys()->map(function ($item, $key) {
			return trans('messages.exports.product.' . $item);
		})->toArray();
	}

	public function collection()
	{
		if (auth()->user()->can('superIndex', Product::class)) {
			$products = Product::with('brand', 'category')->select(self::COLUMNS)->get();
		} elseif (auth()->user()->can('index', Product::class)) {
			$companyId = auth()->user()->company_id;

			$products = Product::with('brand', 'category')->select(self::COLUMNS)->CompanyId($companyId)->get();
		} else {
			return Product::whereNull('updated_at')->whereNotNull('updated_at');
		}

		$products->each(function ($item, $key) {
			if ($item->brand)
				$item->brand_id = $item->brand->name_fa;
			if ($item->category)
				$item->category_id = $item->category->title;
		});

		return $products;
	}
}