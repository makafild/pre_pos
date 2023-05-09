<?php

namespace App\Http\Controllers\api\Customer\v1\User;

use App\Http\Requests\api\Customer\v1\Common\AddFavoriteRequest;
use App\Http\Requests\api\Customer\v1\Common\DeleteFavoriteRequest;
use App\Models\Common\Favorite;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FavoriteController extends Controller
{
	const FAVORITE_PER_PAGE = 15;

	public function index()
	{
		$favorites = Favorite::Mine();

		if (request('s')) {
			$favorites->whereHas('Product', function ($query) {
				$query->SearchName(request('s'));
			});
		}

		return $favorites->paginate(self::FAVORITE_PER_PAGE);
	}

	public function add(AddFavoriteRequest $request)
	{
		$favorite = Favorite::firstOrNew([
			'customer_id' => auth()->id(),
			'product_id'  => $request->product_id,
		]);

		$favorite->save();

		return [
			'status'  => true,
			'message' => trans('messages.api.customer.user.add_favorite'),
		];
	}

	public function delete(DeleteFavoriteRequest $request)
	{
		$favorite = Favorite::where('customer_id', auth()->id())
			->where('product_id', $request->product_id)
			->firstOrFail();

		$favorite->delete();

		return [
			'status'  => true,
			'message' => trans('messages.api.customer.user.delete_favorite'),
		];
	}
}
