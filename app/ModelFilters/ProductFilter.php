<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;
use Psy\CodeCleaner\AssignThisVariablePass;

class ProductFilter extends ModelFilter
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



    public function name_fa($filter)
    {

        return $this->where('name_fa', 'like', '%' . $filter . '%');

    }
    public function referral_id_api($filter)
    {
        return $this->where('referral_id', 'like', '%' . $filter . '%');
    }
    public function name_en($filter)
    {
        return $this->where('name_en', 'like', '%' . $filter . '%');
    }
    public function referral_id($filter)
    {
        return $this->where('referral_id', $filter);
    }

    public function Creator($filter)
    {
        return $this->where('creator', 'like', '%' . $filter . '%');
    }

    public function page_num($filter)
    {
        return $this->where('number_of_page', '=', $filter);
    }
    public function company_ids($filter)
    {
        return $this->whereIn('company_id', $filter);
    }
    public function markup_price($filter)
    {
        return $this->where('markup_price', '=', $filter);
    }
    public function purchase_price($filter)
    {
        return $this->where('purchase_price', '=', $filter);
    }
    public function sales_price($filter)
    {
        return $this->where('sales_price', '=', $filter);
    }
    public function consumer_price($filter)
    {
        return $this->where('consumer_price', '=', $filter);
    }


    public function brand_name_fa($filter)
    {
        /* return $this->with(['brand'=>function($q) use ($filter) {
            return $q->where( 'name_fa', 'like', '%' . $filter . '%');
        }]);*/
        return $this->whereHas('Brand', function ($query) use ($filter) {
            $query->where('name_fa', 'like', '%' . $filter . '%');
        });
    }
    public function brand($filter)
    {
        /* return $this->with(['brand'=>function($q) use ($filter) {
            return $q->where( 'name_fa', 'like', '%' . $filter . '%');
        }]);*/



        // $filter = trim($filter , '[]' );
        //   $ids=explode(",", $filter);

        return $this->whereHas('Brand', function ($query) use ($filter) {
            $query->whereIn('id', $filter);
        });
    }
    public function type2($filter)
    {

        return $this->whereHas('type2', function ($query) use ($filter) {
            $query->where('constant_fa', 'like', '%' . $filter . '%');
        });
    }
    public function type1($filter)
    {
        return $this->whereHas('type1', function ($query) use ($filter) {
            $query->where('constant_fa', 'like', '%' . $filter . '%');
        });
    }
    public function category_title_api($filter)
    {
        return $this->whereHas('category', function ($query) use ($filter) {
            $query->where('title', 'like', '%' . $filter . '%');
        });
    }


    public function company_name_fa($filter)
    {

        return $this->whereHas('Users', function ($query) use ($filter) {
            $query->where('name_fa', 'like', '%' . $filter . '%');
        });
    }


    public function score($filter)
    {
        return $this->where('score', 'like', '%' . $filter . '%');
    }
    public function category_title($filter)
    {
        return $this->with(['constants' => function ($q) use ($filter) {
            return $q->where('kind', "category_title")->where('constant_fa', 'like', '%' . $filter . '%');
        }]);
    }
    public function status($filter)
    {
        return $this->where('status', $filter);
    }
    public function show_status($filter)
    {
        return $this->where('show_status', $filter);
    }
    public function per_slave($filter)
    {
        return $this->where('per_slave', 'like', '%' . $filter . '%');
    }
    public function updated_at($filter)
    {
        $ids = array_flip(array_flip(explode('|', $filter)));
        return $this->whereBetween('updated_at', $ids);
    }
    public function per_master($filter)
    {
        return $this->where('per_master', 'like', '%' . $filter . '%');
    }
    public function id($id)
    {

        return $this->where('id','like' ,"%$id%");
    }
    public function created_at($filter)
    { $ids = explode('|', $filter);
        return $this
            ->where(DB::raw("DATE (created_at)"), '>=', date($ids[0]))
            ->where(DB::raw("DATE (created_at)"), '<=', date($ids[1]));
    }

    public function price_at($filter){
        $ids = explode('|', $filter);

        return $this
        ->where(DB::raw("consumer_price"), '>=', $ids[0])
        ->where(DB::raw("consumer_price"), '<=', $ids[1]);
       ;
    }






    //function  multi sort

    public function sortid($filter)
    {
        return $this->orderBy('id', $filter);
    }
    public function sortreferral_id_api($filter)
    {
        return $this->orderBy('referral_id', $filter);
    }
    public function sortname_fa($filter)
    {
        return $this->orderBy('name_fa', $filter);
    }

    public function sortbrand_name_fa($filter)
    {
         return $this->with(['brand'=>function($q) use ($filter) {
            return $q->orderBy('name_fa', $filter);
        }]);
     /*   return $this->whereHas('Brand', function ($query) use ($filter) {
            $query->orderBy('name_fa', $filter);
        });*/
    }
    public function sortname_en($filter)
    {
        return $this->orderBy('name_en', $filter);
    }
    public function sortcategory_title_api($filter)
    {
        return $this->orderBy('categories.title', $filter);
    }
    public function sortCreator($filter)
    {
        return $this->orderBy('Creator', $filter);
    }
    public function sortpage_num($filter)
    {
        return $this->orderBy('number_of_page', $filter);
    }
    public function sortpurchase_price($filter)
    {
        return $this->orderBy('purchase_price', $filter);
    }
    public function sortsales_price($filter)
    {
        return $this->orderBy('sales_price', $filter);
    }
    public function sortconsumer_price($filter)
    {
        return $this->orderBy('consumer_price', $filter);
    }
    public function sortper_slave($filter)
    {
        return $this->orderBy('per_slave', $filter);
    }
    public function sortscore($filter)
    {
        return $this->orderBy('score', $filter);
    }
    public function sortstatus($filter)
    {
        return $this->orderBy('status', $filter);
    }
    public function sortshow_status($filter)
    {
        return $this->orderBy('show_status', $filter);
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
