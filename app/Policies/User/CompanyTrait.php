<?php

namespace App\Policies\User;

use App\Models\User\User;

/**
 * Created by PhpStorm.
 * User: imohammad
 * Date: 3/26/18
 * Time: 5:46 PM
 */
trait CompanyTrait
{

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @return mixed
	 */
	public function companyIndex(User $user)
	{
		if ($user->can('Company.Company.index')) {
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
	public function companyStore(User $user)
	{
		if ($user->can('Company.Company.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $company
	 * @return mixed
	 */
	public function companyShow(User $user, User $company)
	{
		if ($user->can('Company.Company.show')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $company
	 * @return mixed
	 */
	public function companyUpdate(User $user, User $company)
	{
		if ($user->can('Company.Company.update')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $company
	 * @return mixed
	 */
	public function companyChangeStatus(User $user, User $company)
	{
		if ($user->can('Company.Company.changeStatus')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $company
	 * @return mixed
	 */
	public function companyDestroy(User $user, User $company)
	{
		if ($user->can('Company.Company.destroy')) {
			return true;
		}

		return false;
	}
}