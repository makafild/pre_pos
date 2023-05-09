<?php

namespace App\Models\User;
use Core\Packages\user\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotVisited extends Model
{
    use SoftDeletes;
    protected $table="resone_not_visited";



}
