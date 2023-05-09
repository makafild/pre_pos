<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;

class CustomerFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];
    protected $camel_cased_methods = false;
    //    public function area($filter)
    //    {
    //        return $this->where( 'area', 'like', '%' . $filter . '%');
    //    }
    //    public function name_en($filter)
    //    {
    //        return $this->where('name_en', $filter);
    //    }
    public function status($filter)
    {
        return $this->where('status', $filter);
    }
    public function referral_id_api($filter)
    {
        return $this->where('referral_id', 'like', '%' . $filter . '%');
    }


    public function id($filter)
    {

        return $this->where('id', $filter);
    }
    public function approve($filter)
    {
        return $this->where('approve', ($filter == 'active') ? 1 : 0);
    }

    public function full_name($filter)
    {
        return $this->where('first_name', 'like', '%' . $filter . '%')->orWhere('last_name', 'like', '%' . $filter . '%');
    }
    public function introduction_source($filter)
    {
        return $this->whereHas('IntroductionSource', function ($q) use ($filter) {
            return $q->where('constant_fa', 'like', '%' . $filter . '%');
        });
    }
    public function guild($filter)
    {
        return $this->whereHas('categories', function ($q) use ($filter) {
            return $q->where('constant_fa', 'like', '%' . $filter . '%');
        });
    }
    public function customerGrade($filter)
    {
        return $this->whereHas('customerGrade', function ($q) use ($filter) {
            return $q->where('constant_fa', 'like', '%' . $filter . '%');
        });
    }
    public function customerGroup($filter)
    {
        return $this->whereHas('customerGroup', function ($q) use ($filter) {
            return $q->where('constant_fa', 'like', '%' . $filter . '%');
        });
    }
    public function description($filter)
    {
        return $this->where('description', 'like', '%' . $filter . '%');
    }
    public function province($filter)
    {
        return $this->whereHas('provinces', function ($q) use ($filter) {
            return $q->where('name', 'like', '%' . $filter . '%');
        });
    }
    public function area($filter)
    {
        return $this->whereHas('Areas', function ($q) use ($filter) {
            return $q->where('area', 'like', '%' . $filter . '%');
        });
    }
    public function routes($filter)
    {
        if (is_array($filter)) {
            return $this->whereHas('routes', function ($query) use ($filter) {
                $query->whereIn('id', $filter);
            });
        } else {
            return $this->whereHas('Routes', function ($q) use ($filter) {
                return $q->where('route', 'like', '%' . $filter . '%');
            });
        }
    }

    public function city($filter)
    {
        return $this->whereHas('cities', function ($q) use ($filter) {
            return $q->where('name', 'like', '%' . $filter . '%');
        });
    }
    public function introducer_code($filter)
    {
        return $this->whereHas('IntroducerCode', function ($q) use ($filter) {
            return $q->where('code', 'like', '%' . $filter . '%');
        });
    }
    public function mobile_number($filter)
    {
        return $this->where('mobile_number', 'like', '%' . $filter . '%');
    }
    public function score($filter)
    {
        return $this->where('score',  $filter);
    }
    /* public function introducer_code($filter)
    {
        return $this->where( 'introducer_code_id', 'like', '%' . $filter . '%' );
    }*/
    public function created_at($filter)
    {
        $ids = explode('|', $filter);
        return $this
            ->where(DB::raw("DATE (created_at)"), '>=', date($ids[0]))
            ->where(DB::raw("DATE (created_at)"), '<=', date($ids[1]));
    }
    //    public function end_at($filter)
    //    {
    //        $ids = array_flip(array_flip(explode('|', $filter)));
    //        return $this->whereBetween('end_at', $ids);
    //    }

    // function multi sort

    public function sortid($filter)
    {
        
        return $this->orderBy('id', $filter);
    }
    public function sortconstant_fa($filter)
    {
        dd($filter);
        return $this->orderBy('constant_fa', $filter);
    }
    public function constant_en($filter)
    {
        return $this->orderBy('constant_en', $filter);
    }

    public function sortprovince($filter)
    {
          return $this->whereHas('provinces', function ($query) use ($filter) {
            $query->orderBy('name', $filter);
        });

    }
    public function sorttype2($filter)
    {
          return $this->whereHas('Type2', function ($query) use ($filter) {
            $query->orderBy('name', $filter);
        });

    }
    public function sortcity($filter)
    {
        return $this->orderBy('city', $filter);
    }
    public function sortarea($filter)
    {
        return $this->orderBy('area', $filter);
    }
    public function sortroutes($filter)
    {
        return $this->orderBy('routes', $filter);
    }
    public function sortintroducer_code($filter)
    {
        return $this->orderBy('introducer_code', $filter);
    }
    public function sortintroduction_source($filter)
    {
        return $this->orderBy('introduction_source', $filter);
    }
    public function sortdescription($filter)
    {
        return $this->orderBy('description', $filter);
    }
    public function sortguild($filter)
    {
        return $this->orderBy('guild', $filter);
    }
    public function sortcustomer_class($filter)
    {
        return $this->orderBy('customer_class', $filter);
    }
    public function sortcustomerGrade($filter)
    {
        return $this->orderBy('customerGrade', $filter);
    }
    public function sortcustomerGroup($filter)
    {
        return $this->orderBy('customerGroup', $filter);
    }
    public function sortapprove($filter)
    {
        return $this->orderBy('approve', $filter);
    }
    public function sortscore($filter)
    {
        return $this->orderBy('score', $filter);
    }
    public function sortcreated_at($filter)
    {
        return $this->orderBy('created_at', $filter);
    }

}
