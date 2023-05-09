<?php

namespace Core\Packages\survey\src\request;

use App\Models\Common\SurveyQuestion;
use Core\Packages\role\UserRoles;
use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;
use Core\Packages\user\Users;

class SurveyRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'                => 'required',
            'questions'            => 'required|array',
            'questions.*.kind'     => [
                'required',
                'in:' . implode(',', SurveyQuestion::KINDS),
            ],
            'questions.*.question' => 'required',
            'questions.*.options' => 'nullable|array',
            'from_date' => 'nullable|required_with:to_date',
            'to_date' => 'nullable|required_with:from_date',
            'provinces' => 'required|array|required_with:city_id',
            'provinces.*' => 'required|exists:provinces,id',
            'cities' => 'nullable|array|required_with:route_id',
            'cities.*' => 'required|required_with:city_id|exists:cities,id',
            'areas' => 'nullable|array|required_with:area_id',
            'areas.*' => 'required|required_with:route_id|exists:areas,id',
            'routes' => 'nullable|array',
            'routes.*' => 'required|exists:routes,id',
            'customers' => 'nullable|array',
            'customers.*' => 'nullable|' .
                Rule::exists('users', 'id')
                    ->where('kind', Users::KIND_CUSTOMER),
            'type' => 'required|in:survey,work'
        ];
    }
}
