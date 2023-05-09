<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;

class BrandFilter extends ModelFilter
{
    public $relations = [];
    protected $camel_cased_methods = false;
    public function name_fa($filter)
    {
        return $this->where( 'name_fa', 'like', "%$filter%");
    }
    public function name_en($filter)
    {
        return $this->where( 'name_en', 'like', "%$filter%");
    }

    public function id($id)
    {
        return $this->where('id', 'like',"%$id%");
    }
    public function created_at($filter)
    {
        $ids = explode('|', $filter);
              return $this
              ->where(DB::raw ("DATE (created_at)") ,'>=',date($ids[0]))
              ->where(DB::raw ("DATE (created_at)") ,'<=',date($ids[1]));
    }
//sort part///////////////////////////                   //////             ///////////////          //////                 ////////////////////////////////////////////////////////////////
    public function sortname_fa($filter)
    {
        return $this->orderBy('name_fa', $filter);
    }
    public function sortname_en($filter)
    {
        return $this->orderBy('id', $filter);
    }
    public function sortid($filter)
    {
        return $this->orderBy('id', $filter);
    }
    public function sortcreated_at($filter)
    {
        return $this->orderBy('id', $filter);
    }

}
