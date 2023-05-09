<?php

namespace App\Http\Resources\api\Company\v1\Order;

use App\Models\Order\Detail;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DetailResource
 *
 * @package App\Http\Resources\api\Company\v1\Order
 * @mixin Detail
 */
class DetailResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray($request)
	{
		return [
			'referral_id' => $this->product->referral_id,
			'master'      => $this->master,
			'slave'       => $this->slave,
			'slave2'      => $this->slave2,
			'total'       => $this->total,
			'price'       => $this->price,
			'discount'    => $this->discount,
			'final_price' => $this->final_price,
			'prise'       => $this->prise,
		];
	}
}
