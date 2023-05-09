<?php

namespace App\Policies\Product;

use App\Models\User\User;
use App\Models\Product\Promotions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromotionsPolicy
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
		if ($user->can('Product.Promotions.superIndex')) {
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
		if ($user->can('Product.Promotions.index')) {
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
		if ($user->can('Product.Promotions.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User          $user
	 * @param  \App\Models\Product\Promotions $promotions
	 * @return mixed
	 */
	public function superShow(User $user, Promotions $promotions)
	{
		if ($user->can('Product.Promotions.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User          $user
	 * @param  \App\Models\Product\Promotions $promotions
	 * @return mixed
	 */
	public function show(User $user, Promotions $promotions)
	{
		if ($user->can('Product.Promotions.show')) {
			if ($promotions->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User          $user
	 * @param  \App\Models\Product\Promotions $promotions
	 * @return mixed
	 */
	public function superUpdate(User $user, Promotions $promotions)
	{
		if ($user->can('Product.Promotions.superUpdate')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User          $user
	 * @param  \App\Models\Product\Promotions $promotions
	 * @return mixed
	 */
	public function update(User $user, Promotions $promotions)
	{
		if ($user->can('Product.Promotions.update')) {
			if ($promotions->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User          $user
	 * @param  \App\Models\Product\Promotions $promotions
	 * @return mixed
	 */
	public function superDestroy(User $user, Promotions $promotions)
	{
		if ($user->can('Product.Promotions.superDestroy')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User          $user
	 * @param  \App\Models\Product\Promotions $promotions
	 * @return mixed
	 */
	public function destroy(User $user, Promotions $promotions)
	{
		if ($user->can('Product.Promotions.destroy')) {
			if ($promotions->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
