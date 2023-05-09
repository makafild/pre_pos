<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class CommentsFilter extends ModelFilter
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







    public function id($filter)
    {
        return $this->where('id', $filter);
    }
    public function textView($filter)
    {
        return $this->where('text', 'like', '%' . $filter . '%');
    }
    public function product_id($filter)
    {
        return $this->where('product_id', $filter);
    }
    public function like($filter)
    {
        return $this->where('like_count', $filter);
    }
    public function dislike($filter)
    {
        return $this->where('dislike_count', $filter);
    }
    public function confirm($filter)
    {
        return $this->where('confirm', $filter);
    }

   /* public function full_name($filter)
   // {
        return $this->whereHas('Users', function ($query) use ($filter) {
            $query->where('full_name', 'like', '%' . $filter . '%');
     //   });
    }*/

    public function updated_at($filter)
    {
        $ids = array_flip(array_flip(explode('|', $filter)));
        return $this->whereBetween('updated_at', $ids);
    }


    public function created_at($filter)
    {
        $ids = array_flip(array_flip(explode('|', $filter)));
        //        dd($ids);
        return $this->whereBetween('created_at', $ids);
    }




    public function sortid($filter)
    {
        return $this->orderBy('id', $filter);
    }

    public function sorttextView($filter)
    {
        return $this->orderBy('text', $filter);
    }

    public function sortproduct_id($filter)
    {
        return $this->orderBy('product_id', $filter);
    }

    public function sortlike($filter)
    {
        return $this->orderBy('like_count', $filter);
    }

    public function sortdislike($filter)
    {
        return $this->orderBy('dislike_count', $filter);
    }
    public function sortconfirm($filter)
    {
        return $this->orderBy('confirm', $filter);
    }
    public function sortupdated_at($filter)
    {
        return $this->orderBy('updated_at', $filter);
    }

    public function sortcreated_at($filter)
    {
        return $this->orderBy('created_at', $filter);
    }
}
