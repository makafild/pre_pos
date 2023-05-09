<?php

namespace App\Policies\Order;

use App\Models\User\User;
use App\Models\Order\PaymentMethod;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentMethodPolicy
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
		if ($user->can('Order.PaymentMethod.superIndex')) {
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
		if ($user->can('Order.PaymentMethod.index')) {
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
		if ($user->can('Order.PaymentMethod.store')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\PaymentMethod $visitTour
	 * @return mixed
	 */
	public function superShow(User $user, PaymentMethod $visitTour)
	{
		if ($user->can('Order.PaymentMethod.superShow')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\PaymentMethod $visitTour
	 * @return mixed
	 */
	public function show(User $user, PaymentMethod $visitTour)
	{
		if ($user->can('Order.PaymentMethod.show')) {
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
	 * @param  \App\Models\Order\PaymentMethod $visitTour
	 * @return mixed
	 */
	public function update(User $user, PaymentMethod $visitTour)
	{
		if ($user->can('Order.PaymentMethod.update')) {
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
	 * @param  \App\Models\Order\PaymentMethod $visitTour
	 * @return mixed
	 */
	public function superDestroy(User $user, PaymentMethod $visitTour)
	{
		if ($user->can('Order.PaymentMethod.superDestroy')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the news.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\PaymentMethod $visitTour
	 * @return mixed
	 */
	public function destroy(User $user, PaymentMethod $visitTour)
	{
		if ($user->can('Order.PaymentMethod.destroy')) {
			if ($visitTour->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
