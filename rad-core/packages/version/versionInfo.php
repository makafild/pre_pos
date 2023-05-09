<?php

namespace Core\Packages\version;

use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class versionInfo extends Model
{
    use  SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $table = "versions_info";
}
