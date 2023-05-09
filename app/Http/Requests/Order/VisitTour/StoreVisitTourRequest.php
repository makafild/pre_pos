<?php

namespace App\Http\Requests\Order\VisitTour;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitTourRequest extends FormRequest
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
			'direction'  => 'required',
			'visitor'    => 'required',
			'visit_date' => 'required',
			'visit_time' => 'required',
		];
	}
}
