<?php

namespace App\Http\Controllers\api\Company\v1;

use App\Events\Message\MessageStoredEvent;
use App\Http\Requests\api\Company\v1\Product\ChangeStatusRequest;
use App\Http\Requests\api\Company\v1\Product\StoreProductPriceClassRequest;
use App\Http\Requests\api\Company\v1\Product\StoreProductRequest;
use App\Http\Requests\api\Company\v1\Product\UpdateProductRequest;
use App\Models\Common\Message;
use App\Models\Common\MessageList;
use App\Models\Product\Product;
use App\Models\User\CompanyCustomer;
use App\Models\User\PriceClass;
use App\Models\User\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{


	public function store(Request $request)
	{

		$customer = CompanyCustomer::where('referral_id', $request->referral_id)
			->where('company_id', auth('mobile')->user()->company_id)
			->first();

		$message          = new Message();
		$message->from_id = auth('mobile')->user()->company_id;
		$message->fill($request->all());
		$message->save();

		$messageList = MessageList::firstOrNew([
			'user_id' => auth('mobile')->user()->company_id,
			'to_id'   => $customer->customer_id,
		]);

		$messageList->seen_at = NULL;
		$messageList->message = $request->message;
		$messageList->save();


//		event(new MessageStored($message));
		event(new MessageStoredEvent($message));

		return [
			'status'  => true,
			'message' => trans('messages.api.customer.common.message_store'),
		];
	}
}
