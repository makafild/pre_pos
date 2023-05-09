<?php


namespace core\Packages\company\src\request;

use Core\System\Http\Traits\HelperRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ScoreCustomerRequest
 *
 * @package App\Http\Requests\Customer\Customer
 * @property int $score
 */
class ScoreCompanyRequest extends FormRequest
{

    public function rules()
    {
        return [
            'score' => 'required|integer|min:1|max:5'
        ];
    }
}
