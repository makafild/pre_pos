<?php

namespace App\Models\User;

use App\Models\Common\File;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Category
 *
 * @package App\Models\User
 * @property string $title_en
 * @property string $title_fa
 *
 */
class Category extends Model
{
	protected $table = 'user_categories';

}
