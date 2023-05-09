<?php

namespace App\Policies\Order;

use App\Models\User\User;
use App\Models\Order\VisitTour;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisitTourPolicy
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
		if ($user->can('Order.VisitTour.superIndex')) {
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
		if ($user->can('Order.VisitTour.index')) {
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
		if ($user->can('Order.VisitTour.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\VisitTour $visitTour
	 * @return mixed
	 */
	public function superShow(User $user, VisitTour $visitTour)
	{
		if ($user->can('Order.VisitTour.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\VisitTour $visitTour
	 * @return mixed
	 */
	public function show(User $user, VisitTour $visitTour)
	{
		if ($user->can('Order.VisitTour.show')) {
			if ($visitTour->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\VisitTour $visitTour
	 * @return mixed
	 */
	public function update(User $user, VisitTour $visitTour)
	{
		if ($user->can('Order.VisitTour.update')) {
			if ($visitTour->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\VisitTour $visitTour
	 * @return mixed
	 */
	public function superDestroy(User $user, VisitTour $visitTour)
	{
		if ($user->can('Order.VisitTour.superDestroy')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\VisitTour $visitTour
	 * @return mixed
	 */
	public function destroy(User $user, VisitTour $visitTour)
	{
		if ($user->can('Order.VisitTour.destroy')) {
			if ($visitTour->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
