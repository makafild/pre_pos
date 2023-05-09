<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class SliderFilter extends ModelFilter
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
//    public function status($filter)
//    {
//        return $this->where('status', $filter);
//    }
    public function id($filter)
    {
        return $this->where('id', $filter);
    }
    public function created_at($filter)
    {
        $ids = array_flip(array_flip(explode('|', $filter)));
//        dd($ids);
        return $this->whereBetween('created_at', $ids);
    }
//    public function end_at($filter)
//    {
//        $ids = array_flip(array_flip(explode('|', $filter)));
//        return $this->whereBetween('end_at', $ids);
//    }

}
