<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GroupFilter extends ModelFilter
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
        public function company($id)
        {
            return $this->where('company_id', $id);
        }
        public function id($id)
        {
            return $this->where('id', $id);
        }
        public function role($phone)
        {
            return $this->where('name', 'LIKE', "%$phone%");
        }
        public function created_at($filter)
        {
            $ids = explode('|', $filter);
              return $this
              ->where(DB::raw ("DATE (created_at)") ,'>=',date($ids[0]))
              ->where(DB::raw ("DATE (created_at)") ,'<=',date($ids[1]));
        }

        public function mobilePhone($phone)
        {
            return $this->where('mobile_phone', 'LIKE', "%$phone%");
        }





        public function secretMethod($secretParameter)
        {
            return $this->where('some_column', true);
        }
    }


