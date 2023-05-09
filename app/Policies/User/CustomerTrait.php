<?php

namespace App\Policies\User;

use App\Models\User\User;

/**
 * Created by PhpStorm.
 * User: imohammad
 * Date: 3/26/18
 * Time: 5:46 PM
 */
trait CustomerTrait
{
	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @return mixed
	 */
	public function customerSuperIndex(User $user)
	{
		if ($user->can('Customer.Customer.superIndex')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @return mixed
	 */
	public function customerIndex(User $user)
	{
		if ($user->can('Customer.Customer.index')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $customer
	 * @return mixed
	 */
	public function customerSuperShow(User $user, User $customer)
	{
		if ($user->can('Customer.Customer.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $customer
	 * @return mixed
	 */
	public function customerShow(User $user, User $customer)
	{
		if ($user->can('Customer.Customer.show')) {

			$userCities = $user->CompanyUser->cities->pluck('id')->all();
			$customerCity = $customer->cities->pluck('id')->first();

			if (in_array($customerCity, $userCities)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $customer
	 * @return mixed
	 */
	public function customerSuperUpdate(User $user, User $customer)
	{
		if ($user->can('Customer.Customer.superUpdate')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $customer
	 * @return mixed
	 */
	public function customerUpdate(User $user, User $customer)
	{
		if ($user->can('Customer.Customer.update')) {
			$userCities = $user->CompanyUser->cities->pluck('id')->all();
			$customerCity = $customer->cities->pluck('id')->first();

			if (in_array($customerCity, $userCities)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $customer
	 * @return mixed
	 */
	public function customerDestroy(User $user, User $customer)
	{
		if ($user->can('Customer.Customer.destroy')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $customer
	 * @return mixed
	 */
	public function customerChangeStatus(User $user, User $customer)
	{
		if ($user->can('Customer.Customer.changeStatus')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $customer
	 * @return mixed
	 */
	public function customerScore(User $user, User $customer)
	{
		if ($user->can('Customer.Customer.score')) {
			return true;
		}

		return false;
	}
}