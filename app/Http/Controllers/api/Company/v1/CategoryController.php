<?php

namespace App\Http\Controllers\api\Company\v1;

use App\Http\Requests\api\Company\v1\Category\StoreCategoryRequest;
use App\Models\Product\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		/** @var Category[] $categories */
		$categories = Category::leaves();

		return $categories;
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  StoreCategoryRequest $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreCategoryRequest $request)
	{
		$parent = Category::findOrFail($request->parent_id);

		$category = new Category();
		$category->title = $request->title;
		$category->appendToNode($parent)->save();

		return [
			'status'  => true,
			'message' => trans('messages.api.company.category.store'),
			'id'      => $category->id,
		];
	}
}
