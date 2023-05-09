<?php

namespace App\Policies\User;

use App\Models\User\User;

/**
 * Created by PhpStorm.
 * User: imohammad
 * Date: 3/26/18
 * Time: 5:46 PM
 */
trait UserTrait
{

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 *
	 * @return mixed
	 */
	public function userSuperIndex(User $user)
	{
		if ($user->can('User.User.superIndex')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 *
	 * @return mixed
	 */
	public function userIndex(User $user)
	{
		if ($user->can('User.User.index')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function userSuperShow(User $user, User $model)
	{
		if ($user->can('User.User.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function userShow(User $user, User $model)
	{
		if ($user->can('User.User.show')) {
			if ($model->company_id == $user->company_id)
				return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function userSuperUpdate(User $user, User $model)
	{
		if ($user->can('User.User.superUpdate')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function userUpdate(User $user, User $model)
	{
		if ($user->can('User.User.update')) {
			if ($model->company_id == $user->company_id)
				return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function userSuperDestroy(User $user, User $model)
	{
		if ($user->can('User.User.superDestroy')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function userDestroy(User $user, User $model)
	{
		if ($user->can('User.User.destroy')) {
			if ($model->company_id == $user->company_id)
				return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function userSuperChangeStatus(User $user, User $model)
	{
		if ($user->can('User.User.superChangeStatus')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function userChangeStatus(User $user, User $model)
	{
		if ($user->can('User.User.changeStatus')) {
			if ($model->company_id == $user->company_id)
				return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function userSuperProfile(User $user, User $model)
	{
		if ($user->can('User.User.superProfile')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function userProfile(User $user, User $model)
	{
		if ($user->can('User.User.profile')) {
//			if ($user->cities->contains($model->cities[0]))
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\User $model
	 *
	 * @return mixed
	 */
	public function loginAs(User $user, User $model)
	{
		if ($model->kind == 'company') {
			if ($user->can('Company.Company.loginAs')) {
				return true;
			}
		} else if ($model->kind == 'admin') {
			if ($user->can('User.Role.loginAs')) {
				return true;
			}
		}

		return false;
	}
}