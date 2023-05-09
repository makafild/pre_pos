<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;

class UserFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];
    protected $camel_cased_methods = false;
    public function name_fa($filter)
    {
        return $this->where( 'name_fa', 'like', "%$filter%");
    }
    public function name_en($filter)
    {
        return $this->where('name_en', 'like', "%$filter%");
    }
    public function status($filter)
    {
        return $this->where('status', 'like', "%$filter%");
    }
    public function id($filter)
    {
        return $this->where('id', 'like', "%$filter%");
    }
    public function approve($filter)
    {
        ($filter == 'active')? $filter = 1 :$filter = 0 ;
        return $this->where('approve', 'like', "%$filter%");
    }
    public function created_at($filter)
    {
        $ids = explode('|', $filter);
        return $this
        ->where(DB::raw ("DATE (created_at)") ,'>=',date($ids[0]))
        ->where(DB::raw ("DATE (created_at)") ,'<=',date($ids[1]));
    }
    public function end_at($filter)
    {
        $ids = explode('|', $filter);
        return $this
        ->where(DB::raw ("DATE (end_at)") ,'>=',date($ids[0]))
        ->where(DB::raw ("DATE (end_at)") ,'<=',date($ids[1]));
    }



    //sort




    public function sortname_fa($filter)
    {
        return $this->orderBy('name_fa', $filter);
    }
    public function sortid($filter)
    {

        return $this->orderBy('id', $filter);
    }
    public function sortname_en($filter)
    {

        return $this->orderBy('name_en', $filter);
    }
    public function sortstatus($filter)
    {

        return $this->orderBy('status', $filter);
    }
    public function sortapprove($filter)
    {

        return $this->orderBy('approve', $filter);
    }
    public function sortcreated_at($filter)
    {

        return $this->orderBy('created_at', $filter);
    }
    public function sortend_at($filter)
    {

        return $this->orderBy('end_at', $filter);
    }


}
