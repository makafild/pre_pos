<?php

namespace App\Http\Controllers\api\Customer\v1\User;

use App\Common\CompanyFavorite;
use App\Http\Controllers\Controller;
use App\Http\Requests\api\Customer\v1\Common\AddCompanyFavoriteRequest;
use App\Http\Requests\api\Customer\v1\Common\DeleteCompanyFavoriteRequest;

class CompanyFavoriteController extends Controller
{
	const FAVORITE_PER_PAGE = 15;

	public function index()
	{
		$favorites = CompanyFavorite::Mine();

		if (request('s')) {
			$favorites->whereHas('Company', function ($query) {
				$query->SearchName(request('s'));
			});
		}

		return $favorites->paginate(self::FAVORITE_PER_PAGE);
	}

	public function add(AddCompanyFavoriteRequest $request)
	{
		$favorite = CompanyFavorite::firstOrNew([
			'customer_id' => auth()->id(),
			'company_id'  => $request->company_id,
		]);

		$favorite->save();

		return [
			'status'  => true,
			'message' => trans('messages.api.customer.user.add_company_favorite'),
		];
	}

	public function delete(DeleteCompanyFavoriteRequest $request)
	{
		$favorite = CompanyFavorite::where('customer_id', auth()->id())
			->where('company_id', $request->company_id)
			->firstOrFail();

		$favorite->delete();

		return [
			'status'  => true,
			'message' => trans('messages.api.customer.user.delete_company_favorite'),
		];
	}
}
