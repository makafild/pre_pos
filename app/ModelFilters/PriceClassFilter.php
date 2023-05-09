<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class PriceClassFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];
    protected $camel_cased_methods = false;
    //refial_id
    //name_fa
    //name_en
    //company.namefa
    // brand.namefa
    //category_title

    public function title($filter)
    {
        return $this->where( 'title', 'like', '%' . $filter . '%');

    }
    public function name($filter)
    {
        return $this->whereHas('company',function ($q) use ($filter) {
            return $q->where( 'name_fa', 'like', '%' . $filter . '%')->orWhere('name_en', 'like', '%' . $filter . '%');
        });
    }
    public function id($filter)
    {
        return $this->where('id','like' ,"%$filter%");
    }
    public function created_at($filter)
    {
        $ids = array_flip(array_flip(explode('|', $filter)));
//        dd($ids);
        return $this->whereBetween('created_at', $ids);
    }

//sort

    public function sorttitle($filter)
    {
        return $this->orderBy('title', $filter);
    }
    public function sortid($filter)
    {

        return $this->orderBy('id', $filter);
    }
    public function sortcreated_at($filter)
    {

        return $this->orderBy('created_at', $filter);
    }
    public function sortcompany($filter)
    {


        return $this->related('Company' , function($query) use ($filter) {
            return $query->orderBy('name_fa' ,$filter );
        });
    }


}
