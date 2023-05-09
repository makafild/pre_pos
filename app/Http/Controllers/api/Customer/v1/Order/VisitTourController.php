<?php

namespace App\Http\Controllers\api\Customer\v1\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\VisitTour;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitTourController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$visitTours = VisitTour::whereDate('visit_date', '>', Carbon::now());

		if ($request->company_id)
			$visitTours = $visitTours->where('company_id', $request->company_id);

		$visitTours = $visitTours->paginate();

		return $visitTours;
	}
}
