<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;

class listCategoryCustomerFilters extends ModelFilter
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



    public function id($filter)
    {
        return $this->where('id', 'like' ,"$filter%");
    }

    public function constant_fa($filter)
    {
        return $this->where('constant_fa', 'like',"%$filter%");
    }

    public function constant_en($filter)
    {
        return $this->where('constant_en', 'like',"%$filter%");
    }

    public function created_at($filter)
    {
        $ids = explode('|', $filter);
              return $this
              ->where(DB::raw ("DATE (created_at)") ,'>=',date($ids[0]))
              ->where(DB::raw ("DATE (created_at)") ,'<=',date($ids[1]));
    }

    public function sortid($filter)
    {
        return $this->orderBy('id', $filter);
    }

    public function sortconstant_fa($filter)
    {
        return $this->orderBy('constant_fa', $filter);
    }

    public function sortconstant_en($filter)
    {
        return $this->orderBy('constant_en', $filter);
    }

    public function sortcreated_at($filter)
    {
        return $this->orderBy('created_at', $filter);
    }




}
