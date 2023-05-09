<?php

namespace App\Http\Controllers\api\Customer\v1\Product;

use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\CheckAccess;

class CategoryController extends Controller
{
    use CheckAccess;

    public function tree()
	{
		$companyId = request('company_id');

		if (!$companyId) {
			// $cities     = auth('mobile')->user()->Cities->pluck('id')->all();
			$companyIds = User::Select('id')
				->Company()
				// ->whereCities($cities)
				->Active()
				->get()
				->pluck('id')
				->toArray();

//			$tree = Category::withCount([
//				'products' => function ($query) use ($companyIds) {
//					$query->whereIn('company_id', $companyIds);
//				},
//			])->get()->toTree();
//
//			return $tree;


			$product     = Product::whereIn('company_id', $companyIds)
				->select('category_id')
				->get();
			$categoryIds = $product->pluck('category_id')->unique();

			$nodes = Category::query()->with('Photo')
				->withCount([
					'products' => function ($query) use ($companyIds) {
						$query->whereIn('company_id', $companyIds)->active();
					},
				])
				->whereIn('id', $categoryIds)
				->get();


			$ancestors = Category::query()->with('Photo')
//			->whereNotIn('id', $categoryIds)
				->whereNested(function ($inner) use ($nodes) {
					foreach ($nodes as $node) {
						$inner->orWhere('_lft', '<', $node->getLft())
							->where('_rgt', '>', $node->getLft());
					}
				})
				->get();

			$tree = $ancestors->merge($nodes)->toTree();

			return $tree;
		}

        if (!$this->chAc($companyId)) {
            return [];
        }

		$product     = Product::CompanyId($companyId)
			->select('category_id')
			->get();
		$categoryIds = $product->pluck('category_id')->unique();

		$nodes = Category::query()->with('Photo')
			->withCount([
				'products' => function ($query) use ($companyId) {
					$query->where('company_id', $companyId)->active();
				},
			])
			->whereIn('id', $categoryIds)
			->get();


		$ancestors = Category::query()->with('Photo')
//			->whereNotIn('id', $categoryIds)
			->whereNested(function ($inner) use ($nodes) {
				foreach ($nodes as $node) {
					$inner->orWhere('_lft', '<', $node->getLft())
						->where('_rgt', '>', $node->getLft());
				}
			})
			->get();

		$tree = $ancestors->merge($nodes)->toTree();

		return $tree;
	}
    public function jwttree()
	{

		$companyId = request('company_id');

		if (!$companyId) {
			//$cities     = auth('mobile')->user()->Cities->pluck('id')->all();
			$companyIds = User::Select('id')
				->Company()
			//	->whereCities($cities)
				->Active()
				->get()
				->pluck('id')
				->toArray();

//			$tree = Category::withCount([
//				'products' => function ($query) use ($companyIds) {
//					$query->whereIn('company_id', $companyIds);
//				},
//			])->get()->toTree();
//
//			return $tree;


			$product     = Product::whereIn('company_id', $companyIds)
				->select('category_id')
				->get();
			$categoryIds = $product->pluck('category_id')->unique();

			$nodes = Category::query()->with('Photo')
				->withCount([
					'products' => function ($query) use ($companyIds) {
						$query->whereIn('company_id', $companyIds)->active();
					},
				])
				->whereIn('id', $categoryIds)
				->get();


			$ancestors = Category::query()->with('Photo')
//			->whereNotIn('id', $categoryIds)
				->whereNested(function ($inner) use ($nodes) {
					foreach ($nodes as $node) {
						$inner->orWhere('_lft', '<', $node->getLft())
							->where('_rgt', '>', $node->getLft());
					}
				})
				->get();

			$tree = $ancestors->merge($nodes)->toTree();

			return $tree;
		}

        if (!$this->chAc($companyId)) {
            return [];
        }

		$product     = Product::CompanyId($companyId)
			->select('category_id')
			->get();
		$categoryIds = $product->pluck('category_id')->unique();

		$nodes = Category::query()->with('Photo')
			->withCount([
				'products' => function ($query) use ($companyId) {
					$query->where('company_id', $companyId)->active();
				},
			])
			->whereIn('id', $categoryIds)
			->get();


		$ancestors = Category::query()->with('Photo')
//			->whereNotIn('id', $categoryIds)
			->whereNested(function ($inner) use ($nodes) {
				foreach ($nodes as $node) {
					$inner->orWhere('_lft', '<', $node->getLft())
						->where('_rgt', '>', $node->getLft());
				}
			})
			->get();

		$tree = $ancestors->merge($nodes)->toTree();

		return $tree;
	}

	public function products($id)
	{
		$companyId = request('company_id');

		$searchName = request('search_name');

		$products = Product::with([
			'brand',
			'category',
			'photo',
			'company',
			'labels',

			'promotions',

			'MasterUnit',
			'SlaveUnit',
			'Slave2Unit',

			'PriceClasses.Customers' => function ($query) {
				$query->where('id', auth()->id());
			},
		])->where('category_id', '=', $id)
			->CompanyId($companyId)
			->SearchName($searchName);

		if (request('order')) {
			$products->Order(request('order'));
		}

		return $products->paginate();
	}
}
