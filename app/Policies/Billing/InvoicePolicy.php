<?php

namespace App\Policies\Billing;

use App\Models\Billing\Invoice;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Billing\Invoice $invoice
	 * @return mixed
	 */
	public function superIndex(User $user)
	{
		if (!$user->can('Billing.Invoice.superIndex')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Billing\Invoice $invoice
	 * @return mixed
	 */
	public function index(User $user)
	{
		if ($user->can('Billing.Invoice.index')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Billing\Invoice $invoice
	 * @return mixed
	 */
	public function superShow(User $user, Invoice $invoice)
	{
		if (!$user->can('Billing.Invoice.superShow')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User $user
	 * @return mixed
	 */
	public function create(User $user)
	{
		if ($user->can('Billing.Invoice.create')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Billing\Invoice $invoice
	 * @return mixed
	 */
	public function show(User $user, Invoice $invoice)
	{
		if ($user->can('Billing.Invoice.show')) {
			if ($invoice->user_id == $user->company_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Billing\Invoice $invoice
	 * @return mixed
	 */
	public function confirm(User $user, Invoice $invoice)
	{
		if ($user->can('Billing.Invoice.confirm')) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether the user can view the order.
	 *
	 * @param  \App\Models\User\User       $user
	 * @param  \App\Models\Billing\Invoice $invoice
	 * @return mixed
	 */
	public function update(User $user, Invoice $invoice)
	{
		if ($user->can('Billing.Invoice.confirm')) {
			return true;
		}

		return false;
	}
}
