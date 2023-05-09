<?php

namespace App\Policies\Product;

use App\Models\User\User;
use App\Models\Product\Brand;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrandPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @return mixed
	 */
	public function index(User $user)
	{
		if ($user->can('Product.Brand.index')) {
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
		if ($user->can('Product.Brand.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User     $user
	 * @param  \App\Models\Product\Brand $brand
	 * @return mixed
	 */
	public function show(User $user)
	{
		if ($user->can('Product.Brand.show')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User     $user
	 * @param  \App\Models\Product\Brand $brand
	 * @return mixed
	 */
	public function update(User $user, Brand $brand)
	{
		if ($user->can('Product.Brand.update')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User     $user
	 * @param  \App\Models\Product\Brand $brand
	 * @return mixed
	 */
	public function destroy(User $user, Brand $brand)
	{
		if ($user->can('Product.Brand.destroy')) {
			return true;
		}

		return false;
	}
}
