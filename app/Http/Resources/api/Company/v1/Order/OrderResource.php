<?php

namespace App\Http\Resources\api\Company\v1\Order;

use App\Models\Order\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrderResource
 *
 * @package App\Http\Resources\api\Company\v1\Order
 * @mixin Order
 */
class OrderResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request)
	{
		$data = [
			'id'          => $this->id,
			'customer_id' => $this->customer->getReferralIdBy($this->company_id),


			'price_without_promotions' => $this->price_without_promotions,
			'discount'                 => $this->discount,
			'amount_promotion'         => $this->amount_promotion,
			'price_with_promotions'    => $this->price_with_promotions,
			'final_price'              => $this->final_price,

			'payment_method_id' => $this->payment_method_id,
			'coupon_id'         => $this->coupon_id,

			'description'       => $this->description,
			'payment_confirm'   => $this->payment_confirm,
			'transfer_number'   => $this->transfer_number,
			'carriage_fares'    => $this->carriage_fares,

			'date_of_sending' => $this->date_of_sending,

			'details' => DetailResource::collection($this->details),
		];

		if ($this->customer->IntroducerCode) {
			$data['introducer_code']  = $this->customer->IntroducerCode->code;
			$data['introducer_title'] = $this->customer->IntroducerCode->title;
		}

		return $data;
	}
}
