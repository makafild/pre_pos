<?php

namespace App\Http\Requests\Visitor;

use Illuminate\Foundation\Http\FormRequest;

class ReasonForNotVisitingRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'reson_id'         => 'required',
			'visitor_id' => 'required|exists:users,id',
		];
	}
}
