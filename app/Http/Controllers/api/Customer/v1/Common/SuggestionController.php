<?php

namespace App\Http\Controllers\api\Customer\v1\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\Customer\v1\Common\StoreSuggestionRequest;
use App\Models\Common\Suggestion;
use App\Models\User\Role;
use App\Models\User\User;
use Validator;
use App\Traits\CheckAccess;

class SuggestionController extends Controller
{
	const SUGGESTION_PER_PAGE = 15;
    use CheckAccess;

	public function index()
	{
		return Suggestion::UserId(auth()->id())
			->where('company_id', request('company_id'))
			->paginate(self::SUGGESTION_PER_PAGE);
	}

	public function store(StoreSuggestionRequest $request)
	{
		/** @var User $company */
		$company = User::MyCompany()->findOrFail($request->company_id);

        if (!$this->chAc($company->id)) {
            return [
                'status'  => true,
                'message' => 'شما به این صفحه دسترسی ندارید.',
            ];
        }


		Suggestion::create([
			'suggestion' => $request->suggestion,
			'company_id' => $company->id,
			'user_id'    => auth()->id(),
		]);

		return [
			'status'  => true,
			'message' => trans('messages.api.customer.common.suggestion_store'),
		];
	}
}
