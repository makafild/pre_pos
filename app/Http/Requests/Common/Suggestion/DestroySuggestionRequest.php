<?php

namespace App\Http\Requests\Common\Suggestion;

use App\Rules\CheckRowVersion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DestroySuggestionRequest
 *
 * @package App\Http\Requests\Common\Suggestion
 * @property array $suggestions
 */
class DestroySuggestionRequest extends FormRequest
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
			'suggestions' => [
				'required',
				'array',
			],
		];
    }
}
