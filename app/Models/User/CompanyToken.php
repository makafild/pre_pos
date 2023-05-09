<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CompanyToken
 *
 * @package App\Models\User
 * @property string $token
 *
 * @property string $company_id
 * @property User   $company
 */
class CompanyToken extends Model
{
	public function Company()
	{
		return $this->belongsTo(User::class)->withTrashed();
	}
}
