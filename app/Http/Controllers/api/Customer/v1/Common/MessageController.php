<?php

namespace App\Http\Controllers\api\Customer\v1\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\Customer\v1\Common\StoreMessageRequest;
use App\Models\Common\Message;
use App\Models\Common\MessageList;
use App\Models\User\Role;
use App\Models\User\User;
use App\Traits\CheckAccess;

class MessageController extends Controller
{
	const MESSAGE_PER_PAGE = 15;
    use CheckAccess;

	public function index()
	{
		$messages = MessageList::with(['To.photo'])->Mine();

		return $messages->get();
	}

	public function show($id)
	{
		$messages = Message::Mine()
			->Audience($id)
			->orderBy('created_at', 'desc');

		if ($messages->count()) {
			$messageList          = MessageList::firstOrNew([
				'user_id' => $id,
				'to_id'   => auth()->id(),
			]);

			$messageList->seen_at = now();
			$messageList->save();
		}

		return $messages->paginate(self::MESSAGE_PER_PAGE);
	}

	public function store(StoreMessageRequest $request)
	{
		/** @var User $company */
		$company = User::MyCompany()->findOrFail($request->to_id);

        if (!$this->chAc($company->id)) {
            return [
                'status'  => false,
                'message' => 'شما به این صفحه دسترسی ندارید.',
            ];
        }

		$message          = new Message();
		$message->from_id = auth()->id();
		$message->to_id   = $request->to_id;
		$message->message = $request->message;
		$message->save();

		$messageList          = MessageList::firstOrNew([
			'user_id' => auth()->id(),
			'to_id'   => $request->to_id,
		]);
		$messageList->seen_at = NULL;
		$messageList->message = $request->message;
		$messageList->save();

		$messageList          = MessageList::firstOrNew([
			'to_id'   => auth()->id(),
			'user_id' => $request->to_id,
		]);
		$messageList->seen_at = NULL;
		$messageList->message = $request->message;
		$messageList->save();

		return [
			'status'  => true,
			'message' => trans('messages.api.customer.common.message_store'),
		];
	}
}
