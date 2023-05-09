<?php

namespace App\Policies\Setting;

use App\Models\User\User;
use App\Models\Setting\Constant;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConstantPolicy
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
		if ($user->can('Setting.Constant.index')) {
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
		if ($user->can('Setting.Constant.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User        $user
	 * @param  \App\Models\Setting\Constant $constant
	 * @return mixed
	 */
	public function show(User $user, Constant $constant)
	{
		if ($user->can('Setting.Constant.show')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User        $user
	 * @param  \App\Models\Setting\Constant $constant
	 * @return mixed
	 */
	public function update(User $user, Constant $constant)
	{
		if ($user->can('Setting.Constant.update')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User        $user
	 * @param  \App\Models\Setting\Constant $constant
	 * @return mixed
	 */
	public function destroy(User $user, Constant $constant)
	{
		if ($user->can('Setting.Constant.destroy')) {
			return true;
		}

		return false;
	}
}
