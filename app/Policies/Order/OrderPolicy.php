<?php

namespace App\Policies\Order;

use App\Models\User\User;
use App\Models\Order\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\Order $order
	 * @return mixed
	 */
	public function superIndex(User $user)
	{
		if (!$user->can('Order.Order.superIndex')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\Order $order
	 * @return mixed
	 */
	public function index(User $user)
	{
		if ($user->can('Order.Order.index')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\Order $order
	 * @return mixed
	 */
	public function superShow(User $user, Order $order)
	{
		if (!$user->can('Order.Order.superShow')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\Order $order
	 * @return mixed
	 */
	public function show(User $user, Order $order)
	{
		if ($user->can('Order.Order.show')) {
			if($order->company_id == $user->company_id){
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can update the order.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\Order $order
	 * @return mixed
	 */
	public function superUpdate(User $user, Order $order)
	{
		if (!$user->can('Order.Order.superUpdate')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can update the order.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\Order $order
	 * @return mixed
	 */
	public function update(User $user, Order $order)
	{
		if ($user->can('Order.Order.update')) {
			if($order->company_id == $user->company_id){
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the order.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\Order $order
	 * @return mixed
	 */
	public function superChangeStatus(User $user, Order $order)
	{
		if ($user->can('Order.Order.superChangeStatus')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can delete the order.
	 *
	 * @param  \App\Models\User\User   $user
	 * @param  \App\Models\Order\Order $order
	 * @return mixed
	 */
	public function changeStatus(User $user, Order $order)
	{
		if ($user->can('Order.Order.changeStatus')) {
			if($order->company_id == $user->company_id){
				return true;
			}
		}

		return false;
	}
}
