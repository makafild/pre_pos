<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\Auth;

class StockroomFilter extends ModelFilter
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



        public function mobilePhone($phone)
        {
            
            return $this->where('mobile_phone', 'LIKE', "%$phone%");
        }





        public function secretMethod($secretParameter)
        {
            return $this->where('some_column', true);
        }
    }


