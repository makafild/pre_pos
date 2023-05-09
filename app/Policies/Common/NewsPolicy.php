<?php

namespace App\Policies\Common;

use App\Models\User\User;
use App\Models\Common\News;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsPolicy
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
		if ($user->can('Common.News.superIndex')) {
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
		if ($user->can('Common.News.index')) {
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
		if ($user->can('Common.News.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Common\News $news
	 * @return mixed
	 */
	public function superShow(User $user, News $news)
	{
		if ($user->can('Common.News.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Common\News $news
	 * @return mixed
	 */
	public function show(User $user, News $news)
	{
		if ($user->can('Common.News.show')) {
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
	 * @param  \App\Models\Common\News $news
	 * @return mixed
	 */
	public function update(User $user, News $news)
	{
		if ($user->can('Common.News.update')) {
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
	 * @param  \App\Models\Common\News $news
	 * @return mixed
	 */
	public function superDestroy(User $user, News $news)
	{
		if ($user->can('Common.News.superDestroy')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Common\News $news
	 * @return mixed
	 */
	public function destroy(User $user, News $news)
	{
		if ($user->can('Common.News.destroy')) {
			if ($news->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
