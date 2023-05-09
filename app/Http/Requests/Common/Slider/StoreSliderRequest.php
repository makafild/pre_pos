<?php

namespace App\Http\Requests\Common\Slider;

use App\Models\Common\Slider;
use App\Rules\Jalali;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreSliderRequest
 *
 * @package App\Http\Requests\Common\Slider
 * @property int    $file_id
 * @property string kind
 * @property string $link
 * @property object $company
 * @property object $product
 *
 * @property string $start_at
 * @property string $end_at
 */
class StoreSliderRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'kind'       => [
				'required',
				'in:' . implode(',', Slider::KINDS),
			],
			'file_id'    => 'required|exists:files,id',
			'link'       => [
				'required_if:kind,' . Slider::KIND_LINK,
			],
			'company.id' => [
				'required_if:kind,' . Slider::KIND_COMPANY,
				'required_if:kind,' . Slider::KIND_PRODUCT,
				Rule::exists('users', 'id')
					->where('kind', 'company'),
			],
			'product.id' => [
				'required_if:kind,' . Slider::KIND_PRODUCT,
				Rule::exists('products', 'id')
					->where('company_id', $this->company['id']),
			],

			'start_at' => [
				new Jalali(),
			],
			'end_at'   => [
				new Jalali(),
			],
		];
	}
}
