<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class PromotionFilter extends ModelFilter
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
    public function kind($filter)
    {
        return $this->where('kind', $filter);
    }
    public function title($filter)
    {
        return $this->where('title', 'like', '%' . $filter . '%');
    }
    public function status($filter)
    {
        return $this->where('status', $filter);
    }
    public function company_name_fa($filter)
    {

        return $this->whereHas('company', function ($query) use ($filter) {
            $query->where('description', 'like', '%' . $filter . '%');
        });
    }

    public function created_at($filter)
    {
        $ids = array_flip(array_flip(explode('|', $filter)));
        //        dd($ids);
        return $this->whereBetween('created_at', $ids);
    }
    public function updated_at($filter)
    {
        $ids = array_flip(array_flip(explode('|', $filter)));
        //        dd($ids);
        return $this->whereBetween('updated_at', $ids);
    }

    public function sortid($filter)
    {
        return $this->orderBy('id', $filter);
    }
    public function sortkind($filter)
    {
        return $this->orderBy('kind', $filter);
    }
    public function sorttitle($filter)
    {
        return $this->orderBy('title', $filter);
    }
    public function sortstatus($filter)
    {
        return $this->orderBy('status', $filter);
    }
    public function sortcreated_at($filter)
    {
        return $this->orderBy('created_at', $filter);
    }
    public function sortupdated_at($filter)
    {
        return $this->orderBy('updated_at', $filter);
    }

}
