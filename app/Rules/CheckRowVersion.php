<?php

namespace App\Rules;

use App\Models\Order\Order;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckRowVersion implements Rule
{
	private $table;

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct($table)
	{
		//
		$this->table = $table;
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
		if (!is_array($value)) {
			return false;
		}

		$orderIds = array_column($value, 'id');
		$models = DB::table($this->table)->whereIn('id', $orderIds)->get()->keyBy('id');

		foreach ($value as $item) {
			$id = $item['id'];

			if ($models[$id]->row_version != $item['row_version']) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return 'سفارشات توسط کاربر دیگری تغییر کرده است.';
	}
}
