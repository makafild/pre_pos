<?php

namespace App\Policies\Product;

use App\Models\User\User;
use App\Models\Product\Category;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
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
		if ($user->can('Product.Category.index')) {
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
		if ($user->can('Product.Category.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User        $user
	 * @param  \App\Models\Product\Category $category
	 * @return mixed
	 */
	public function show(User $user, Category $category)
	{
		if ($user->can('Product.Category.show')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User        $user
	 * @param  \App\Models\Product\Category $category
	 * @return mixed
	 */
	public function update(User $user, Category $category)
	{
		if ($user->can('Product.Category.update')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User        $user
	 * @param  \App\Models\Product\Category $category
	 * @return mixed
	 */
	public function destroy(User $user, Category $category)
	{
		if ($user->can('Product.Category.destroy')) {
			return true;
		}

		return false;
	}
}
