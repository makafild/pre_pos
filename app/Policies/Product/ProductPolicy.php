<?php

namespace App\Policies\Product;

use App\Models\User\User;
use App\Models\Product\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @return mixed
	 */
	public function superIndex(User $user)
	{
		if ($user->can('Product.Product.superIndex')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @return mixed
	 */
	public function index(User $user)
	{
		if ($user->can('Product.Product.index')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can create news.
	 *
	 * @param  \App\Models\User\User $user
	 * @return mixed
	 */
	public function store(User $user)
	{
		if ($user->can('Product.Product.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Product\Product $product
	 * @return mixed
	 */
	public function superShow(User $user, Product $product)
	{
		if ($user->can('Product.Product.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Product\Product $product
	 * @return mixed
	 */
	public function show(User $user, Product $product)
	{
		if ($user->can('Product.Product.show')) {
			if ($product->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Product\Product $product
	 * @return mixed
	 */
	public function superUpdate(User $user, Product $product)
	{
		if ($user->can('Product.Product.superUpdate')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Product\Product $product
	 * @return mixed
	 */
	public function update(User $user, Product $product)
	{
		if ($user->can('Product.Product.update')) {
			if ($product->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Product\Product $product
	 * @return mixed
	 */
	public function superDestroy(User $user, Product $product)
	{
		if ($user->can('Product.Product.superDestroy')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Product\Product $product
	 * @return mixed
	 */
	public function superChangeStatus(User $user, Product $product)
	{
		if ($user->can('Product.Product.superChangeStatus')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Product\Product $product
	 * @return mixed
	 */
	public function changeStatus(User $user, Product $product)
	{
		if ($user->can('Product.Product.changeStatus')) {
			if ($product->company_id == $user->company_id)
				return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Product\Product $product
	 * @return mixed
	 */
	public function changeStatussuperDestroy(User $user, Product $product)
	{
		if ($user->can('Product.Product.changeStatus')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Product\Product $product
	 * @return mixed
	 */
	public function destroy(User $user, Product $product)
	{
		if ($user->can('Product.Product.destroy')) {
			if ($product->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
