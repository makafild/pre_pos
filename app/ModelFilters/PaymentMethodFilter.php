<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;

class PaymentMethodFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];
    protected $camel_cased_methods = false;
    public function constant_fa($filter)
    {
        return $this->where( 'constant_fa', 'like', "%$filter%");
    }
    public function constant_en($filter)
    {
        return $this->where('constant_en', 'like', "%$filter%");
    }
    public function default($filter)
    {
       ($filter == "inactive")?$filter = 0 : $filter = 1 ;
        return $this->where('default', 'like', "%$filter%");
    }
    public function id($filter)
    {
        return $this->where('id', 'like', "%$filter%");
    }
    public function discount($filter)
    {
        return $this->where('discount', 'like', "%$filter%");
    }
    public function discount_max($filter)
    {
        return $this->where('discount_max', 'like', "%$filter%");
    }
    // public function approve($filter)
    // {
    //     ($filter == 'active')? $filter = 1 :$filter = 0 ;
    //     return $this->where('approve', 'like', "%$filter%");
    // }
    public function created_at($filter)
    {
        $ids = explode('|', $filter);
        return $this
        ->where(DB::raw ("DATE (created_at)") ,'>=',date($ids[0]))
        ->where(DB::raw ("DATE (created_at)") ,'<=',date($ids[1]));
    }
    // public function end_at($filter)
    // {
    //     $ids = explode('|', $filter);
    //     return $this
    //     ->where(DB::raw ("DATE (created_at)") ,'>=',date($ids[0]))
    //     ->where(DB::raw ("DATE (created_at)") ,'<=',date($ids[1]));
    // }



    //sort


    public function sortconstant_fa($filter)
    {
        return $this->orderBy('constant_fa', $filter);
    }
    public function sortconstant_en($filter)
    {

        return $this->orderBy('constant_en', $filter);
    }
    public function sortdefault($filter)
    {

        return $this->orderBy('default', $filter);
    }
    public function sortcompany($filter)
    {


        return $this->related('Company' , function($query) use ($filter) {
            return $query->orderBy('name_fa' ,$filter );
        });
    }
    public function sortid($filter)
    {
        return $this->orderBy('id', $filter);
    }
    public function sortdiscount($filter){

        return $this->orderBy('discount', $filter);
    }
    public function sortdiscount_max($filter)
    {
        return $this->orderBy('discount_max', $filter);
    }
    public function sortcreated_at($filter)
    {
        return $this->orderBy('created_at', $filter);
    }


}
