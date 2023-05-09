<?php

namespace App\Models\Setting;

use App\Models\User\User;
use Hamcrest\Core\Set;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 *
 * @package App\Models\Setting
 *
 * @property string $key
 * @property string $value
 *
 * @property int    $company_id
 * @property User   $company
 *
 * @method static Setting CompanyId(integer $company_id)
 */
class Setting extends Model
{
	const SMS_CODE = 'sms_code';
	const SMS_SENDER = 'sms_sender';
	const SMS_API_KEY = 'sms_api_key';

	public static function getSettingBy($name)
	{
		$setting = Setting::where('key', $name)->first();

		return $setting->value ?? '';
	}

	// ********************************* Scope *********************************

	public function scopeCompanyId($query, $companyId)
	{
		if ($companyId) {
			return $query->where('company_id', $companyId);
		} else {
			return $query->whereNull('company_id');
		}

	}
}
