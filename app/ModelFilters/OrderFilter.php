<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;

class OrderFilter extends ModelFilter
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
    public function company_name_fa($filter)
    {
        return $this->whereHas('company', function ($q) use ($filter) {
            return $q->where('name_fa', 'like', '%' . $filter . '%');
        });
    }
    public function customer_first_name($filter)
    {
        return $this->whereHas('customer', function ($q) use ($filter) {
            return $q->where('first_name', 'like', '%' . $filter . '%')->orWhere('last_name', 'like', '%' . $filter . '%');
        });
    }
    public function address($filter)
    {


        $this->related('customer.addresses' , function($query) use ($filter) {
            return $query->where('address', 'like' ,"%$filter%");
        })->get();
    }
    public function province($filter)
    {


        $this->related('customer.provinces' , function($query) use ($filter) {
            return $query->where('name', 'like' ,"%$filter%");
        })->get();
    }
    public function city($filter)
    {
        $this->related('customer.cities' , function($query) use ($filter) {
            return $query->where('name', 'like' ,"%$filter%");
        })->get();
    }
    public function area($filter)
    {
        $this->related('customer.areas' , function($query) use ($filter) {
            return $query->where('area', 'like' ,"%$filter%");
        })->get();
    }
    public function routes($filter)
    {
        $this->related('customer.routes' , function($query) use ($filter) {
            return $query->where('route', 'like' ,"%$filter%");
        })->get();
    }
    public function customer_introducer_code($filter)
    {
        return $this->whereHas('customer', function ($q) use ($filter) {
            return $q->where('introducer_code_id', 'like', '%' . $filter . '%')->orWhere('last_name', 'like', '%' . $filter . '%');
        });
    }
    public function customer_referral_id_api($filter)
    {
        return $this->whereHas('customer', function ($q) use ($filter) {
            return $q->where('id',  $filter);
        });
    }
    public function customer_mobile_number($filter)
    {
        return $this->whereHas('customer', function ($q) use ($filter) {
            return $q->where('mobile_number', 'like', '%' . $filter . '%');
        });
    }
    public function referral_id_api($filter)
    {
        return $this->where('referral_id', 'like', '%' . $filter . '%');
    }
    public function tracking_code($filter)
    {
        return $this->where('tracking_code', '=', $filter);
    }
    public function transfer_number($filter)
    {
        return $this->where('transfer_number', $filter);
    }
    public function final_price($filter)
    {
        return $this->whereHas('details', function ($q) use ($filter) {
            return $q->where('final_price', 'like', '%' . $filter . '%');
        });
    }
    public function updated_at($filter)
    {
        return $this->whereHas('details', function ($q) use ($filter) {
            return $q->where('updated_at', 'like', '%' . $filter . '%');
        });
    }
    public function FirstPriority($filter)
    {

        return $this->whereHas('OrderCompanyPriorities', function ($q) use ($filter) {
            return $q->whereHas('company', function ($q1) use ($filter) {
                return $q1->where('name_fa', 'like', '%' . $filter . '%');
            });
        });
    }

    public function FirstBrand($filter)
    {
        /*return $this->whereHas(['details.product.brand' => function ($q) use ($filter) {
            return $q->where('name_fa', 'like', '%' . $filter . '%');
        }]);*/

        return $this->whereHas('details', function ($q) use ($filter) {
            return $q->whereHas('product', function ($q1) use ($filter) {
                return $q1->whereHas('brand', function ($q2) use ($filter) {
                    return $q2->where('name_fa', 'like', '%' . $filter . '%');
                });
            });
        });
    }

    public function registered_source($filter)
    {
        return $this->where('registered_source', 'like', '%' . $filter . '%');
    }
    public function payment_confirm($filter)
    {
        return $this->where('payment_confirm', 'like', '%' . $filter . '%');
    }
    public function imei($filter)
    {
        return $this->where('imei', 'like', '%' . $filter . '%');
    }
    public function reject_text($filter)
    {
        return $this->whereHas('RejectText', function ($q) use ($filter) {
            return $q->where('constant_fa', 'like', '%' . $filter . '%');
        });
    }
    public function status($filter)
    {
        return $this->where('status', 'like', '%' . $filter . '%');
    }

    public function id($filter)
    {

        return $this->where('id', 'like',"%$filter%");
    }
    public function created_at($filter)
    {
        $ids = explode('|', $filter);
        return $this
            ->where(DB::raw("DATE (created_at)"), '>=', date($ids[0]))
            ->where(DB::raw("DATE (created_at)"), '<=', date($ids[1]));
    }
    public function reference_date($filter)
    {
        $ids = explode('|', $filter);
        return $this
        ->where(DB::raw("DATE (reference_date)"), '>=', date($ids[0]))
        ->where(DB::raw("DATE (reference_date)"), '<=', date($ids[1]));

    }
    public function date_of_sending_translate($filter)
    {
        $ids = explode('|', $filter);
        return $this
        ->where(DB::raw("DATE (date_of_sending)"), '>=', date($ids[0]))
        ->where(DB::raw("DATE (date_of_sending)"), '<=', date($ids[1]));

    }
    public function change_status_date($filter)
    {
        $ids = explode('|', $filter);
        return $this
        ->where(DB::raw("DATE (change_status_date)"), '>=', date($ids[0]))
        ->where(DB::raw("DATE (change_status_date)"), '<=', date($ids[1]));


    }


    //function list sort
    public function sortid($filter)
    {
        return $this->orderBy('id', $filter);
    }
    public function sortreferral_id_api($filter)
    {
        return $this->orderBy('referral_id', $filter);
    }
    public function sortcompany_name_fa($filter)
    {
        return $this->orderBy('company_name_fa', $filter);
    }
    public function sortcustomer_referral_id($filter)
    {
        return $this->orderBy('customer_referral_id', $filter);
    }
    public function sortcustomer_first_name($filter)
    {
        return $this->orderBy('customer_first_name', $filter);
    }
    public function sortcustomer_mobile_number($filter)
    {
        return $this->orderBy('customer_mobile_number', $filter);
    }
    public function sortcustomer_introducer_code($filter)
    {
        return $this->orderBy('customer_introducer_code', $filter);
    }
    public function sortregistered_source($filter)
    {
        return $this->orderBy('registered_source', $filter);
    }
    public function sortFirstPriority($filter)
    {
        return $this->orderBy('FirstPriority', $filter);
    }
    public function sortFirstBrand($filter)
    {
        return $this->orderBy('FirstBrand', $filter);
    }
    public function sortfinal_price($filter)
    {
        return $this->orderBy('final_price', $filter);
    }
    public function sortpayment_confirm($filter)
    {
        return $this->orderBy('payment_confirm', $filter);
    }
    public function sorttransfer_number($filter)
    {
        return $this->orderBy('transfer_number', $filter);
    }
    public function sortimei($filter)
    {
        return $this->orderBy('imei', $filter);
    }
    public function sortreject_text($filter)
    {
        return $this->orderBy('reject_text', $filter);
    }
    public function sortstatus($filter)
    {
        return $this->orderBy('status', $filter);
    }
    public function sortdate_of_sending_translate($filter)
    {
        return $this->orderBy('date_of_sending_translate', $filter);
    }
    public function sortchange_status_date($filter)
    {
        return $this->orderBy('id', $filter);
    }
    public function sortreference_date($filter)
    {
        return $this->orderBy('id', $filter);
    }
    public function sortcreated_at($filter)
    {
        return $this->orderBy('id', $filter);
    }
}
