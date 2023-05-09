<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class ReasonForNotVisiting extends Model
{
	protected $fillable = [
		'visitor_id',
		'reson_id',
		'customer_id',
		'description'
	];

	public function visitor()
	{
		return $this->belongsTo(User::class,'visitor_id');
	}

    public function customer()
    {
        return $this->belongsTo(User::class,'customer_id');
    }
    public function reson()
    {
        return $this->belongsTo(NotVisited::class,'reson_id');
    }
}
