<?php

namespace Core\Packages\notification\src\request;
use App\Rules\Jalali;
use Core\Packages\common\Constant;
use Core\Packages\notification\Notification;
use Core\System\Http\Requests\FormRequestCustomize ;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'       => 'required',
            'message' => 'required',
            'kind' => [
                'required',
                'in:' . implode(',', Notification::KINDS),
            ],
            'company'  => [
                'nullable',
                'required_if:kind,' . Notification::KIND_COMPANY,
                'required_if:kind,' . Notification::KIND_PRODUCT,
                Rule::exists('users', 'id')
                    ->where('kind', 'company'),
            ],
            'product'  => [
                'nullable',
                'required_if:kind,' . Notification::KIND_PRODUCT
            ],
            'link' => [
                'required_if:kind,' . Notification::KIND_LINK

            ],

            'provinces' => 'required',
            'provinces' => 'required|array|exists:provinces,id',
            'customer_category' => 'array',
            'cities' => 'array',
            'areas' => 'array',
            'route' => 'array',
           'provinces.*' => 'integer|exists:provinces,id',
           'cities.*' => 'integer|exists:cities,id',
            'areas.*' => 'integer|exists:areas,id',
            'route.*' => 'integer|exists:routes,id',
        ];
    }
}
