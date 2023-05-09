<?php

namespace App\Http\Requests\Visitor;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
            'positions' => 'required|array',
            'positions.*.accessibility' =>'required',
            'positions.*.device_id' => 'required',
            'positions.*.accuracy' => 'nullable',
            'positions.*.altitude' => 'nullable',
            'positions.*.heading' => 'nullable',
            'positions.*.latitude' =>'nullable',
            'positions.*.longitude' => 'nullable',
            'positions.*.speed' => 'nullable',
            'positions.*.timestamp' => 'nullable',
            'positions.*.timeout' => 'nullable',
            'positions.*.position_unavailable' => 'nullable',
            'positions.*.permission_denied' => 'nullable',
            'positions.*.message' => 'nullable',
            'positions.*.code' => 'nullable',
            'positions.*.location_status' => 'required',
            'positions.*.network' => 'required'
		];
	}
}
