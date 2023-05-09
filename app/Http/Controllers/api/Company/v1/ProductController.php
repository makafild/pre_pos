<?php

namespace App\Http\Controllers\api\Company\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\Company\v1\Product\ChangeStatusRequest;
use App\Http\Requests\api\Company\v1\Product\StoreProductPriceClassRequest;
use App\Http\Requests\api\Company\v1\Product\StoreProductRequest;
use App\Http\Requests\api\Company\v1\Product\UpdateProductRequest;
use App\Models\Product\Product;
use App\Models\User\PriceClass;
use App\Models\User\User;

class ProductController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{

		$companyId = auth('mobile')->id();

		/** @var Product[] $products */
		$products = Product::CompanyId($companyId)
			->paginate();

		return $products;
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  StoreProductRequest $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreProductRequest $request)
	{

		$companyId = auth()->id();

		$productIds = array_column($request->products, 'referral_id');

		/** @var Product[] $productsEntity */
		$productsEntity = Product::CompanyId($companyId)
			->ReferralId($productIds)
			->get()
			->keyBy('referral_id');

		foreach ($request->products as $product) {

			$referralId = $product['referral_id'];
			if (isset($productsEntity[$referralId]) && $productsEntity[$referralId])
				$productEntity = $productsEntity[$referralId];
			else {
				$productEntity = new Product();

				$productEntity->brand_id    = $product['brand_id'] ?? NULL;
				$productEntity->category_id = $product['category_id'] ?? NULL;
				$productEntity->photo_id    = $product['photo_id'];
				$productEntity->Photos()->sync($product['photo_id']);
			}

			$productEntity->referral_id  = $product['referral_id'];
			$productEntity->order_column = $product['order_column'] ?? $product['referral_id'];

			$productEntity->name_fa     = $product['name_fa'];
			$productEntity->name_en     = $product['name_en'] ?? NULL;
			$productEntity->description = $product['description'] ?? NULL;

			$productEntity->per_master = $product['per_master'] ?? 0;
			$productEntity->per_slave  = $product['per_slave'] ?? 0;

			$productEntity->master_unit_id = $product['master_unit_id'] ?? NULL;
			$productEntity->slave_unit_id  = $product['slave_unit_id'] ?? NULL;
			$productEntity->slave2_unit_id = $product['slave2_unit_id'];

			$productEntity->master_status = $product['master_status'] ?? 1;
			$productEntity->slave_status  = $product['slave_status'] ?? 1;
			$productEntity->slave2_status = $product['slave2_status'] ?? 1;

//			$productEntity->master_status = $product['master_unit_id'] ? 1 : 0;
//			$productEntity->slave_status  = $product['slave_unit_id'] ? 1 : 0;
//			$productEntity->slave2_status = $product['slave2_unit_id'] ? 1 : 0;

			$productEntity->purchase_price = $product['purchase_price'] ?? 0;
			$productEntity->sales_price    = $product['sales_price'] ?? 0;
			$productEntity->consumer_price = $product['consumer_price'] ?? 0;

			$productEntity->company_id = auth('mobile')->user()->company_id;

			$productEntity->save();
		}

		return [
			'status'  => true,
			'message' => trans('messages.api.company.product.store'),
		];
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $referral_id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show($referral_id)
	{
		$companyId = auth()->id();

		/** @var Product $product */
		$product = Product::CompanyId($companyId)
			->ReferralId($referral_id)
			->with([
				'brand',
				'category',
				'photo',
				'company',

				'MasterUnit',
				'SlaveUnit',
				'Slave2Unit',
			])
			->first();

		return $product;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  UpdateProductRequest $request
	 * @param  int                  $referral_id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(UpdateProductRequest $request)
	{
		$companyId = auth()->id();

		$productIds = array_column($request->products, 'referral_id');

		/** @var Product[] $productsEntity */
		$productsEntity = Product::CompanyId($companyId)
			->ReferralId($productIds)
			->get()
			->keyBy('referral_id');

		foreach ($request->products as $product) {

			$referralId    = $product['referral_id'];
			$productEntity = $productsEntity[$referralId];

			$productEntity->referral_id = $product['referral_id'];


			if (isset($product['name_fa']) && $product['name_fa'])
				$productEntity->name_fa = $product['name_fa'];
			if (isset($product['name_en']) && $product['name_en'])
				$productEntity->name_en = $product['name_en'] ?? NULL;
			if (isset($product['description']) && $product['description'])
				$productEntity->description = $product['description'] ?? NULL;

			$productEntity->per_master = $product['per_master'] ?? 0;
			$productEntity->per_slave  = $product['per_slave'] ?? 0;

			$productEntity->master_unit_id = $product['master_unit_id'] ?? NULL;
			$productEntity->slave_unit_id  = $product['slave_unit_id'] ?? NULL;
			$productEntity->slave2_unit_id = $product['slave2_unit_id'];

			$productEntity->purchase_price = $product['purchase_price'] ?? 0;
			$productEntity->sales_price    = $product['sales_price'] ?? 0;
			$productEntity->consumer_price = $product['consumer_price'] ?? 0;

			$productEntity->company_id = auth('mobile')->user()->company_id;

			$productEntity->save();

			if (isset($product['price_classes']) && $product['price_classes']) {
				$productEntity->PriceClasses()->detach($productEntity->PriceClasses);
				foreach ($product['price_classes'] as $price_class) {
					$priceClass = PriceClass::ReferralId($price_class['referral_id'])
						->first();

					$productEntity->PriceClasses()->attach($priceClass['id'], [
						'price' => $price_class['price'],
					]);
				}
			}

		}

		return [
			'status'  => true,
			'message' => trans('messages.api.company.product.update'),
		];
	}

	public function changeStatus(ChangeStatusRequest $request)
	{
		$companyId = auth()->id();

		$productIds = array_column($request->products, 'referral_id');

		/** @var Product[] $productsEntity */
		$productsEntity = Product::CompanyId($companyId)
			->ReferralId($productIds)
			->get();

		$updatedProduct = [];
		foreach ($request->products as $product) {
			foreach ($productsEntity as $productEntity) {
				if ($productEntity->referral_id == $product['referral_id']) {

					if (isset($product['status'])) {
						if ($product['status'] == Product::STATUS_AVAILABLE)
							$productEntity->status = Product::STATUS_AVAILABLE;
						if ($product['status'] == Product::STATUS_UNAVAILABLE)
							$productEntity->status = Product::STATUS_UNAVAILABLE;
					}

					if (isset($product['show_status'])) {
						if ($product['show_status'] == User::STATUS_ACTIVE)
							$productEntity->show_status = User::STATUS_ACTIVE;
						if ($product['show_status'] == User::STATUS_INACTIVE)
							$productEntity->show_status = User::STATUS_INACTIVE;
					}
					$productEntity->save();

					$updatedProduct[] = $product['referral_id'];
				}
			}
		}

		$productCount = count($productIds);
		$updatedCount = count($updatedProduct);

		$productIds = array_diff($productIds, $updatedProduct);
		$productIds = implode(', ', $productIds);

		\Log::info("company_id = {$companyId}: Count of request = {$productCount} and count updated {$updatedCount} & ids = {$productIds}");
		\Log::info($request->all());

		return [
			'status'  => true,
			'message' => trans('messages.api.company.product.changeStatus'),
		];
	}

	public function priceClass(StoreProductPriceClassRequest $request)
	{
		$companyId = auth()->id();

		$productIds = array_column($request->products, 'referral_id');

		/** @var Product[] $productsEntity */
		$productsEntity = Product::CompanyId($companyId)
			->ReferralId($productIds)
			->get()
			->keyBy('referral_id');

		$referralIds = [];
		foreach ($request->products as $product) {

			$referralId = $product['referral_id'];

			if (isset($productsEntity[$referralId]) && $productsEntity[$referralId]) {
				$productEntity = $productsEntity[$referralId];
			} else {
				$referralIds[] = $referralId;
				continue;
			}

			foreach ($product['price_classes'] as $price_class) {
				$priceClass = PriceClass::CompanyId(auth('mobile')->user()->company_id)
					->ReferralId($price_class['referral_id'])
					->first();

				$productEntity->PriceClasses()->detach($priceClass['id']);
				$productEntity->PriceClasses()->attach($priceClass['id'], [
					'price' => $price_class['price'],
				]);
			}
		}

		\Log::error(array_unique($referralIds));
		return [
			'status'                => true,
			'error_on_referral_ids' => array_unique($referralIds),
		];
	}
}
