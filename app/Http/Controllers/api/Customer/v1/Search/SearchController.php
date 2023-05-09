<?php

namespace App\Http\Controllers\api\Customer\v1\Search;

use App\Http\Requests\api\Customer\v1\Common\ShowSearchRequest;
use App\Models\Product\Product;
use App\Models\User\User;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
	const COMPANY_PER_PAGE = 20;
	const PRODUCT_PER_PAGE = 20;

	public function index(ShowSearchRequest $request)
	{
		$company = User::Company()
			->with('photo')
			->SearchName($request->name)
			->paginate(self::COMPANY_PER_PAGE)
			->setPageName('company');

		$product = Product::with('photo')
			->SearchName($request->name)
			->paginate(self::PRODUCT_PER_PAGE)
			->setPageName('product');

		return [
			'company' => $company,
			'product' => $product,
		];

	}
}

