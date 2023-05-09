<?php

namespace App\Policies\Common;

use App\Models\User\User;
use App\Models\Common\Slider;
use Illuminate\Auth\Access\HandlesAuthorization;

class SliderPolicy
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
		if ($user->can('Common.Slider.index')) {
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
		if ($user->can('Common.Slider.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User     $user
	 * @param  \App\Models\Common\Slider $slider
	 * @return mixed
	 */
	public function show(User $user, Slider $slider)
	{
		if ($user->can('Common.Slider.show')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User     $user
	 * @param  \App\Models\Common\Slider $slider
	 * @return mixed
	 */
	public function update(User $user, Slider $slider)
	{
		if ($user->can('Common.Slider.update')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User     $user
	 * @param  \App\Models\Common\Slider $slider
	 * @return mixed
	 */
	public function destroy(User $user, Slider $slider)
	{
		if ($user->can('Common.Slider.destroy')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User     $user
	 * @param  \App\Models\Common\Slider $slider
	 * @return mixed
	 */
	public function changeStatus(User $user, Slider $slider)
	{
		if ($user->can('Common.Slider.changeStatus')) {
			return true;
		}

		return false;
	}
}
