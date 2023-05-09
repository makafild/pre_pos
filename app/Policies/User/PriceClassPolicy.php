<?php

namespace App\Policies\User;

use App\Models\User\PriceClass;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PriceClassPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\User\PriceClass $priceClass
	 * @return mixed
	 */
	public function superIndex(User $user)
	{
		if (!$user->can('User.PriceClass.superIndex')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\User\PriceClass $priceClass
	 * @return mixed
	 */
	public function index(User $user)
	{
		if ($user->can('User.PriceClass.index')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\User\PriceClass $priceClass
	 * @return mixed
	 */
	public function superShow(User $user, PriceClass $priceClass)
	{
		if (!$user->can('User.PriceClass.superShow')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\User\PriceClass $priceClass
	 * @return mixed
	 */
	public function store(User $user)
	{
		if ($user->can('User.PriceClass.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\User\PriceClass $priceClass
	 * @return mixed
	 */
	public function show(User $user, PriceClass $priceClass)
	{
		if ($user->can('User.PriceClass.show')) {
			if ($priceClass->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can update the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\User\PriceClass $priceClass
	 * @return mixed
	 */
	public function superUpdate(User $user, PriceClass $priceClass)
	{
		if (!$user->can('User.PriceClass.superUpdate')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can update the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\User\PriceClass $priceClass
	 * @return mixed
	 */
	public function update(User $user, PriceClass $priceClass)
	{
		if ($user->can('User.PriceClass.update')) {
			if ($priceClass->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
