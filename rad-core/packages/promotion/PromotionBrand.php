<?php

namespace Core\Packages\promotion;

use App\Models\Product\Promotions;
use App\Traits\VersionObserve;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionBrand extends Model
{
	use VersionObserve, SoftDeletes;
    protected $table = 'promotions_brand';
    public function Promotions()
    {
        return $this->belongsTo(Promotions::class);
    }
}
