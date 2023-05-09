<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PromotionFillMasterSlave implements Rule
{
	private $master;
	private $slave;

	/**
	 * Create a new rule instance.
	 *
	 * @param $master
	 * @param $slave
	 */
	public function __construct()
	{
//		$this->master = $master;
//		$this->slave  = $slave;
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string $attribute
	 * @param  mixed  $value
	 *
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
//		\Log::info($this->master);
//		\Log::info($this->slave);
		\Log::info($value);

		return false;

		if (!$this->master && !$this->slave && !$value) {
        	return true;
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
		return 'یک واحد باید پر شود.';
	}
}
