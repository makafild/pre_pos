<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class PaymentMethodCustomer extends Model
{
    public function PaymentMethodCustomer()
    {
        return $this->belongsTo( PaymentMethod::class,'payment_method_id');
    }
}

