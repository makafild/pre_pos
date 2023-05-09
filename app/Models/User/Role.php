<?php

namespace App\Models\User;
use App\Traits\VersionObserve;

/**
 * Class Role
 *
 * @package App\Models\User
 *
 * @property integer      $id
 * @property string       $name
 * @property string       $company_id
 * @property string       $guard_name
 * @property Permission[] $permissions
 * @property User[]       $users
 *
 */
class Role extends \Spatie\Permission\Models\Role
{
	use VersionObserve;

	const CUSTOMER_API = 'customer_api';
	const CUSTOMER_DEFAULT = 'default';

	public function scopeCompanyId($query, $companyId)
	{
		if ($companyId)
			return $query->where('company_id', $companyId);
		else
			return $query->whereNull('company_id');
	}
}