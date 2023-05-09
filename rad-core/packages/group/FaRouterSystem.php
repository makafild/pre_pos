<?php

namespace Core\Packages\group;

use Illuminate\Database\Eloquent\Model;


class FaRouterSystem extends Model
{
    protected $fillable = [
        'en',
        'fa'
    ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $timestamps = false;

    protected $table = "fa_router_system";


   
}
