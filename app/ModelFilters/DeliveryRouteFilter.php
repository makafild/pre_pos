<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;

class DeliveryRouteFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];
    protected $camel_cased_methods = false;
    public function route($filter)
    {
        return $this->where( 'route', 'like', '%' . $filter . '%');
    }
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
        return $this->where('id', 'like' ,"%$filter%");
    }
    public function area($filter)
    {
        $this->related('areas' , function($query) use ($filter) {
            return $query->where('area', 'like' ,"%$filter%");
        })->get();
    }
    public function province($filter)
    {
        $this->related('areas.province' , function($query) use ($filter) {
            return $query->where('name', 'like' ,"%$filter%");
        })->get();
    }

    public function created_at($filter)
    {
        $ids = explode('|', $filter);
        return $this
        ->where(DB::raw ("DATE (created_at)") ,'>=',date($ids[0]))
        ->where(DB::raw ("DATE (created_at)") ,'<=',date($ids[1]));
    }
//    public function end_at($filter)
//    {
//        $ids = array_flip(array_flip(explode('|', $filter)));
//        return $this->whereBetween('end_at', $ids);
//    }

public function sortroute($filter)
{
    return $this->orderBy( 'route' , $filter);
}

public function sortid($filter)
{
  
    return $this->orderBy( 'id' , $filter);
}

public function sortarea($filter)
{
    $this->related('areas' , function($query) use ($filter) {
        return $query->orderBy( 'area' , $filter);
    })->get();
}
public function sortprovince($filter)
{
    $this->related('areas.province' , function($query) use ($filter) {
        return $query->orderBy( 'name' , $filter);
    })->get();
}
public function sortcreated_at($filter)
    {
        $ids = explode('|', $filter);
        return $this
        ->where(DB::raw ("DATE (created_at)") ,'>=',date($ids[0]))
        ->where(DB::raw ("DATE (created_at)") ,'<=',date($ids[1]));
    }

}




