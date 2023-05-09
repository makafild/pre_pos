<?php

namespace App\Policies\User;

use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
	use HandlesAuthorization;

	use UserTrait;
	use CustomerTrait;
	use CompanyTrait;

}
