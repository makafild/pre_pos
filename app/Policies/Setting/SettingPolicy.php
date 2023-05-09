<?php

namespace App\Policies\Setting;

use App\Models\Setting\Setting;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SettingPolicy
{
	use HandlesAuthorization;


	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Setting\Setting $setting
	 * @return mixed
	 */
	public function superIndex(User $user)
	{
		if (!$user->can('Setting.Setting.superIndex')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Setting\Setting $setting
	 * @return mixed
	 */
	public function index(User $user)
	{
		if ($user->can('Setting.Setting.index')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Setting\Setting $setting
	 * @return mixed
	 */
	public function superShow(User $user, Setting $setting)
	{
		if (!$user->can('Setting.Setting.superShow')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Setting\Setting $setting
	 * @return mixed
	 */
	public function show(User $user, Setting $setting)
	{
		if ($user->can('Setting.Setting.show')) {
			if ($setting->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can update the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Setting\Setting $setting
	 * @return mixed
	 */
	public function superUpdate(User $user, Setting $setting)
	{
		if (!$user->can('Setting.Setting.superUpdate')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can update the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Setting\Setting $setting
	 * @return mixed
	 */
	public function update(User $user, Setting $setting)
	{
		if ($user->can('Setting.Setting.update')) {
			if ($setting->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
