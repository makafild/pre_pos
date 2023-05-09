<?php

namespace App\Models\Order;

use App\Models\User\User;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;

/**
 * Class VisitTour
 *
 * @package App\Models\Order
 * @property string $direction
 * @property string $visitor
 * @property string $visit_date
 * @property string $visit_time
 */
class VisitTour extends Model
{
	public function getVisitDateAttribute()
	{
		$v = new Verta($this->attributes['visit_date']);

		return str_replace('-', '/', $v->formatDate());
	}

	public function company()
	{
	    return $this->belongsTo(User::class);
	}
}

