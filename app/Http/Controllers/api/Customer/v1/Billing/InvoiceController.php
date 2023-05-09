<?php

namespace App\Http\Controllers\api\Customer\v1\Billing;

use App\Http\Controllers\Billing\ChargeController;
use App\Http\Requests\api\Customer\v1\Billing\Invoice\StoreInvoiceRequest;
use App\Models\Billing\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$userId = auth()->id();

		$invoices = Invoice::UserId($userId)
			->paginate();

		return $invoices;
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  StoreInvoiceRequest $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreInvoiceRequest $request)
	{
		$invoice = new Invoice();

		$invoice->kind = Invoice::KIND_CHARGE;
		$invoice->amount = $request->amount;
		$invoice->title = 'افزایش اعتبار';
		$invoice->description = 'افزایش اعتبار به مبلغ ' . $request->amount;
		$invoice->status = Invoice::STATUS_UNDONE;
		$invoice->user_id = auth()->id();

		$invoice->save();

		$redirectUrl = ChargeController::send($invoice->amount, $invoice->id, '', 'ir_pay');

		return [
			'status'       => true,
			'redirect_url' => $redirectUrl,
		];
	}
}
