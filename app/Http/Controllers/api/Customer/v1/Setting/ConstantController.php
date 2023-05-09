<?php

namespace App\Http\Controllers\api\Customer\v1\Setting;

use App\Http\Requests\Setting\Constant\ListConstantRequest;
use App\Http\Requests\Setting\Constant\StoreConstantRequest;
use App\Http\Requests\Setting\Constant\UpdateConstantRequest;
use App\Models\Setting\Constant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConstantController extends Controller
{
	public function list(ListConstantRequest $request)
	{
       

		$constants = Constant::where('kind', $request->kind)->get();

		return $constants;
	}
}
