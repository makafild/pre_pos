<?php

namespace App\Policies\Common;

use App\Models\User\User;
use App\Models\Common\Message;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
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
		if ($user->can('Common.Message.index')) {
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
		if ($user->can('Common.Message.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Common\Message $news
	 * @return mixed
	 */
	public function superShow(User $user, Message $news)
	{
		if ($user->can('Common.Message.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Common\Message $news
	 * @return mixed
	 */
	public function show(User $user, Message $news)
	{
		if ($user->can('Common.Message.show')) {
			if ($news->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Common\Message $news
	 * @return mixed
	 */
	public function update(User $user, Message $news)
	{
		if ($user->can('Common.Message.update')) {
			if ($news->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
