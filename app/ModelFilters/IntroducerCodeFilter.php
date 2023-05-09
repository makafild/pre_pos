<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IntroducerCodeFilter extends ModelFilter
{

    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];
    protected $camel_cased_methods = false;


    protected $blacklist = ['secretMethod'];

    // This will filter 'company_id' OR 'company'
    public function title($title)
    {
        return $this->where('title', 'LIKE', "%$title%");
    }
    public function id($id)
    {

        return $this->where('id', 'LIKE', "%$id%");
    }
    public function company($company)
    {

        $this->related('Company' , function($query) use ($company) {
            return $query->where('name_fa', 'like' ,"%$company%");
        })->get();

    //    return $this->related('Company', 'name_fa', '=', "%$company%");
    }
    public function code($id)
    {
        return $this->where('code', 'LIKE', "%$id%");
    }
    public function status($status)
    {
        return $this->where('status', 'LIKE', "%$status%");
    }
    public function created_at($filter)
    {


        /* if ($ids[0] == $ids[1]) {
              $tarikh =   explode('-' , $ids[0]);
              if ($tarikh[1] <= 9) {
                $tarikh[1] = "0" . $tarikh[1];
              }
              if ($tarikh[2] <= 9) {
                $tarikh[2] = "0" . $tarikh[2];
              }
              $tarikh = implode('-' , $tarikh);*/

        $ids = explode('|', $filter);
        return $this
            ->where(DB::raw("DATE (created_at)"), '>=', date($ids[0]))
            ->where(DB::raw("DATE (created_at)"), '<=', date($ids[1]));


        //$q->where (DB::raw ("DATE (created_at)
        /*  return $this
               ->where('created_at' ,'>=',date($ids[0]))
                ->where('created_at' ,'<=',date($ids[1]));*/
        //  }
        //   return $this->whereBetween('created_at', $ids);
    }
    public function mobilePhone($phone)
    {
        return $this->where('mobile_phone', 'LIKE', "%$phone%");
    }



    public function secretMethod($secretParameter)
    {
        return $this->where('some_column', true);
    }

    //sort part
    public function sortid($filter)
    {
        return $this->orderBy('id', $filter);
    }
    public function sorttitle($filter)
    {

        return $this->orderBy('title', $filter);
    }
    public function sortCode($filter)
    {
        return $this->orderBy('code', $filter);
    }
    public function sortcompany($filter)
    {

        $this->related('Company' , function($query) use ($filter) {
         $query->orderBy( 'name_fa' , $filter) ;
        })->get();
    }
    public function sortstatus($filter)
    {
        return $this->orderBy('status', $filter);
    }

    public function sortcreated_at($filter)
    {
        return $this->orderBy('created_at', $filter);
    }






}
