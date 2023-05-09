<?php

namespace App\Rules;

use Hekmatinasser\Verta\Verta;
use Illuminate\Contracts\Validation\Rule;

class Jalali implements Rule
{
	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		$date = explode('/', $value);

		try {
			if (Verta::isValidDate($date[0], $date[1], $date[2]))
				return true;
		} catch (\Exception $ex) {

		}


		return false;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return trans('validation.custom.jalali');
	}
}
