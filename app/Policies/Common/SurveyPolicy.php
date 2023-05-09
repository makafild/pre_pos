<?php

namespace App\Policies\Common;

use App\Models\User\User;
use App\Models\Common\Survey;
use Illuminate\Auth\Access\HandlesAuthorization;

class SurveyPolicy
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
		if ($user->can('Common.Survey.superIndex')) {
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
		if ($user->can('Common.Survey.index')) {
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
		if ($user->can('Common.Survey.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Common\Survey $survey
	 * @return mixed
	 */
	public function superShow(User $user, Survey $survey)
	{
		if ($user->can('Common.Survey.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Common\Survey $survey
	 * @return mixed
	 */
	public function show(User $user, Survey $survey)
	{
		if ($user->can('Common.Survey.show')) {
			if ($survey->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Common\Survey $survey
	 * @return mixed
	 */
	public function update(User $user, Survey $survey)
	{
		if ($user->can('Common.Survey.update')) {
			if ($survey->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Common\Survey $survey
	 * @return mixed
	 */
	public function destroy(User $user, Survey $survey)
	{
		if ($user->can('Common.Survey.destroy')) {
			if ($survey->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
