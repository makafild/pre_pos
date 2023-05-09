<?php

namespace Core\Packages\order;

use App\Models\Order\PaymentMethod;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodCustomer extends Model
{
    public function PaymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
