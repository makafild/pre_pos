<?php

namespace App\Http\Controllers\api\Company\v1;

use App\Models\Setting\Constant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConstantController extends Controller
{
	public function index(Request $request)
	{
		$constants = Constant::where('kind', $request->kind)->get();

		return $constants;
	}
}
