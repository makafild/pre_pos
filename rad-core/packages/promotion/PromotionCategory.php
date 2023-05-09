<?php

namespace Core\Packages\promotion;

use App\Models\Product\Promotions;
use App\Traits\VersionObserve;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionCategory extends Model
{
    use VersionObserve, SoftDeletes;
    protected $table = 'promotions_category';

    public function Promotions()
    {
        return $this->belongsTo(Promotions::class);
    }

}
