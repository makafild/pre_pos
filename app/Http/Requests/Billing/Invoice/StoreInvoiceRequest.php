<?php

namespace App\Http\Requests\Billing\Invoice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreInvoiceRequest
 *
 * @package App\Http\Requests\Billing\Invoice
 *
 * @property int    $amount
 * @property string $title
 * @property string $description
 *
 * @property array    $user
 */
class StoreInvoiceRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'amount'      => 'required',
			'description' => 'required',
			'user.id'     => 'required|exists:users,id',
		];
	}
}
