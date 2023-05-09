<?php

namespace App\Policies\User;

use App\Models\User\IntroducerCode;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IntroducerCodePolicy
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
		if ($user->can('Company.IntroducerCode.superIndex')) {
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
		if ($user->can('Company.IntroducerCode.index')) {
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
		if ($user->can('Company.IntroducerCode.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User           $user
	 * @param  \App\Models\User\IntroducerCode $introducerCode
	 * @return mixed
	 */
	public function superShow(User $user, IntroducerCode $introducerCode)
	{
		if ($user->can('Company.IntroducerCode.show')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User           $user
	 * @param  \App\Models\User\IntroducerCode $introducerCode
	 * @return mixed
	 */
	public function show(User $user, IntroducerCode $introducerCode)
	{
		if ($user->can('Company.IntroducerCode.show')) {
			if ($introducerCode->company_id == $user->company_id)
				return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User           $user
	 * @param  \App\Models\User\IntroducerCode $introducerCode
	 * @return mixed
	 */
	public function superUpdate(User $user, IntroducerCode $introducerCode)
	{
		if ($user->can('Company.IntroducerCode.superUpdate')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User           $user
	 * @param  \App\Models\User\IntroducerCode $introducerCode
	 * @return mixed
	 */
	public function update(User $user, IntroducerCode $introducerCode)
	{
		if ($user->can('Company.IntroducerCode.update')) {
			if ($introducerCode->company_id == $user->company_id)
				return true;
		}

		return false;
	}
}
