<?php

namespace App\Policies\Order;

use App\Models\Order\Coupon;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CouponPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User    $user
	 * @param  \App\Models\Order\Coupon $coupon
	 * @return mixed
	 */
	public function superIndex(User $user)
	{
		if (!$user->can('Order.Coupon.superIndex')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User    $user
	 * @param  \App\Models\Order\Coupon $coupon
	 * @return mixed
	 */
	public function index(User $user)
	{
		if ($user->can('Order.Coupon.index')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User    $user
	 * @param  \App\Models\Order\Coupon $coupon
	 * @return mixed
	 */
	public function superShow(User $user, Coupon $coupon)
	{
		if (!$user->can('Order.Coupon.superShow')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User    $user
	 * @param  \App\Models\Order\Coupon $coupon
	 * @return mixed
	 */
	public function show(User $user, Coupon $coupon)
	{
		if ($user->can('Order.Coupon.show')) {
			if ($coupon->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can update the order.
	 *
	 * @param  \App\Models\User\User    $user
	 * @param  \App\Models\Order\Coupon $coupon
	 * @return mixed
	 */
	public function superUpdate(User $user, Coupon $coupon)
	{
		if (!$user->can('Order.Coupon.superUpdate')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can update the order.
	 *
	 * @param  \App\Models\User\User    $user
	 * @param  \App\Models\Order\Coupon $coupon
	 * @return mixed
	 */
	public function update(User $user, Coupon $coupon)
	{
		if ($user->can('Order.Coupon.update')) {
			if ($coupon->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can update the order.
	 *
	 * @param  \App\Models\User\User    $user
	 * @param  \App\Models\Order\Coupon $coupon
	 * @return mixed
	 */
	public function superChangeStatus(User $user, Coupon $coupon)
	{
		if ($user->can('Order.Coupon.superChangeStatus')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can update the order.
	 *
	 * @param  \App\Models\User\User    $user
	 * @param  \App\Models\Order\Coupon $coupon
	 * @return mixed
	 */
	public function changeStatus(User $user, Coupon $coupon)
	{
		if ($user->can('Order.Coupon.changeStatus')) {
			if ($coupon->company_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}
}
