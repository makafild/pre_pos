<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class OrderCompanyPriorities extends Model
{
    public function Order()
    {
        return $this->belongsTo(Order::class);
    }

    public function Company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

}
