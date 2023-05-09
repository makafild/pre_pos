<?php

namespace App\Policies\Common;

use App\Models\User\User;
use App\Models\Common\Suggestion;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuggestionPolicy
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
		if ($user->can('Common.Suggestion.superIndex')) {
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
		if ($user->can('Common.Suggestion.index')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User         $user
	 * @param  \App\Models\Common\Suggestion $suggestion
	 * @return mixed
	 */
	public function superShow(User $user, Suggestion $suggestion)
	{
		if ($user->can('Common.Suggestion.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User         $user
	 * @param  \App\Models\Common\Suggestion $suggestion
	 * @return mixed
	 */
	public function show(User $user, Suggestion $suggestion)
	{
		if ($user->can('Common.Suggestion.show')) {
			if ($suggestion->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User         $user
	 * @param  \App\Models\Common\Suggestion $suggestion
	 * @return mixed
	 */
	public function superDestroy(User $user, Suggestion $suggestion)
	{
		if ($user->can('Common.Suggestion.superDestroy')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User         $user
	 * @param  \App\Models\Common\Suggestion $suggestion
	 * @return mixed
	 */
	public function destroy(User $user, Suggestion $suggestion)
	{
		if ($user->can('Common.Suggestion.destroy')) {
			if ($suggestion->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
