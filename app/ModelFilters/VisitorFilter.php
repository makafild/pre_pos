<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;

class VisitorFilter extends ModelFilter
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
    public function company($filter)
    {
        $this->related('user.CompanyRel' , function($query) use ($filter) {
            return $query->where('name_fa', 'like' ,"%$filter%");
        })->get();
    }
    public function super_visitor($filter)
    {
        $this->related('visitors' , function($query) use ($filter) {
            return $query->where('is_super_visitor', 'like' ,"%$filter%");
        })->get();
    }


    public function created_at($filter)
    {
        $ids = explode('|', $filter);
        return $this
        ->where(DB::raw ("DATE (created_at)") ,'>=',date($ids[0]))
        ->where(DB::raw ("DATE (created_at)") ,'<=',date($ids[1]));
    }
    public function type($filter)
    {
        if ($filter == "super_visitor"){
            return $this->where("is_super_visitor",1);
        }elseif($filter == "visitor"){
            return $this->where("is_super_visitor",0);
        }

    }
    public function mobile_number($filter)
    {
        $this->related('user' , function($query) use ($filter) {
            return $query->where('mobile_number', 'like' ,"%$filter%");
        })->get();
    }
    public function name($filter){
        return $this->whereHas('user',function ($q) use ($filter) {
            return $q->where( 'first_name', 'like', '%' . $filter . '%')->orWhere('last_name', 'like', '%' . $filter . '%');
        });
    }
    public function parent($filter){
      if ($filter == "true"){;
          return $this->whereHas('superVisitor');
      }elseif($filter == "false"){
          return $this->whereDoesntHave('superVisitor');

      }
    }


}
