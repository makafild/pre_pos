<?php

namespace core\Packages\category\src\controllers;


use Illuminate\Http\Request;
use Core\Packages\category\Category;
use Core\Packages\category\src\request\StoreCategoryRequest;
use Core\Packages\category\src\request\UpdateCategoryRequest;
use Core\System\Http\Controllers\CoreController;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */


class CategoryPackageController extends CoreController
{
    public function index(Request  $request)
    {


        //        if (!auth()->user()->can('index', Category::class)) {
        //            abort(500);
        //        }
        $filter = $request->title;
        if ($filter) {

            $nodes_id = Category::where('title', "like", '%' . $filter . '%')->first();
            if ($nodes_id) {
                $nodes =  Category::descendantsAndSelf($nodes_id)->toTree()->first();
                return $nodes;
            }
        }
        $nodes = Category::get()->toTree();
        if ($nodes) {
            return $nodes[0];
        }
    }
    public function store(StoreCategoryRequest $request)
    {
        //        if (!auth()->user()->can('store', \App\Models\Product\Category::class)) {
        //            abort(500);
        //        }

        $parent = Category::findOrFail($request->parent_id);
        //        if ($parent->products->count() && $request->parent_id != 1) {
        //            return [
        //                'status'  => false,
        //                'message' => trans('messages.product.category.parent_has_products'),
        //            ];
        //        }

        $category        = new Category();
        $category->title = $request->title;
        $category->appendToNode($parent)->save();


        return [
            'status'  => true,
            'message' => trans('messages.product.category.store'),
            'id'      => $category->id,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::with('photo')->findOrFail($id);

        //        if (!auth()->user()->can('show', $category)) {
        //            abort(500);
        //        }

        return $category;
    }
    public function image($id, Request $request)
    {
        //        if (!auth()->user()->can('store', Category::class)) {
        //            abort(500);
        //        }

        /** @var Category $category */
        $category = Category::findOrFail($id);
        $category->Photo()->associate($request->photo['id']);
        $category->save();

        return [
            'status'  => true,
            'message' => trans('messages.product.category.store'),
            'id'      => $category->id,
        ];
    }
    public function update(UpdateCategoryRequest $request, $id)
    {
        /** @var Category $parent */
        $parent = Category::findOrFail($request->parent_id);
        //        if ($parent->products->count() && $request->parent_id != 1) {
        //            return [
        //                'status'  => false,
        //                'message' => trans('messages.product.category.parent_has_products'),
        //            ];
        //        }

        //        if (!auth()->user()->can('update', $parent)) {
        //            abort(500);
        //        }

        /** @var Category $category */
        $category        = Category::findOrFail($id);
        $category->title = $request->title;
        $category->appendToNode($parent)->save();

        return [
            'status'  => true,
            'message' => trans('messages.product.category.update'),
            'id'      => $category->id,
        ];
    }
    public function destroy($id)
    {
        /** @var Category $node */
        $node = Category::with('children', 'products')->findOrFail($id);

        //        if (!auth()->user()->can('destroy', $node)) {
        //            abort(500);
        //        }

        if ($node->children->count()) {
            return [
                'status'  => false,
                'message' => trans('messages.product.category.destroy_has_children'),
            ];
        }

        if ($node->products->count()) {
            return [
                'status'  => false,
                'message' => trans('messages.product.category.destroy_has_products'),
            ];
        }

        $node->delete();

        return [
            'status'  => true,
            'message' => trans('messages.product.category.destroy'),
        ];
    }
    public function list()
    {
        $result = Category::select('*');

        return $result->get();
    }
}
