<?php

namespace App\Policies\User;

use App\Models\User\User;
use App\Models\User\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
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
		if ($user->can('User.Role.superIndex')) {
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
		if ($user->can('User.Role.index')) {
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
		if ($user->can('User.Role.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\Role $role
	 * @return mixed
	 */
	public function superShow(User $user, Role $role)
	{
		if ($user->can('User.Role.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\Role $role
	 * @return mixed
	 */
	public function show(User $user, Role $role)
	{
		if ($user->can('User.Role.show')) {
			if ($role->company_id == $user->company_id)
				return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\Role $role
	 * @return mixed
	 */
	public function superUpdate(User $user, Role $role)
	{
		if ($user->can('User.Role.superUpdate')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\Role $role
	 * @return mixed
	 */
	public function update(User $user, Role $role)
	{
		if ($user->can('User.Role.update')) {
			if ($role->company_id == $user->company_id)
				return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\Role $role
	 * @return mixed
	 */
	public function superDestroy(User $user, Role $role)
	{
		if ($user->can('User.Role.superDestroy')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\Role $role
	 * @return mixed
	 */
	public function destroy(User $user, Role $role)
	{
		if ($user->can('User.Role.destroy')) {
			if ($role->company_id == $user->company_id)
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
	public function customerIndex(User $user)
	{
		if ($user->can('Customer.Role.customerIndex')) {
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
	public function customerStore(User $user)
	{
		if ($user->can('Customer.Role.customerStore')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\Role $role
	 * @return mixed
	 */
	public function customerSuperUpdate(User $user, Role $role)
	{
		if ($user->can('Customer.Role.customerSuperUpdate')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\Role $role
	 * @return mixed
	 */
	public function customerUpdate(User $user, Role $role)
	{
		if ($user->can('Customer.Role.customerUpdate')) {
			if ($role->company_id == $user->company_id)
				return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User $user
	 * @param  \App\Models\User\Role $role
	 * @return mixed
	 */
	public function customerDestroy(User $user, Role $role)
	{
		if ($user->can('Customer.Role.customerDestroy')) {
			if ($role->company_id == $user->company_id)
				return true;
		}

		return false;
	}
}
