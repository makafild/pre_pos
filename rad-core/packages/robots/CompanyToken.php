<?php

namespace Core\Packages\robots;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**

 */
class CompanyToken extends Model
{

    protected $table="company_token";
    protected $fillable = [
        'company_id',
        'token'
    ];


}
