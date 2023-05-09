<?php

namespace App\Http\Controllers\api\Company\v1;

use App\Http\Requests\api\Company\v1\Customer\StoreCustomerRequest;
use App\Http\Requests\api\Company\v1\Customer\UpdateCustomerRequest;
use App\Models\Product\Product;
use App\Models\User\CompanyCustomer;
use App\Models\User\PriceClass;
use Carbon\Carbon;
use Core\System\Helper\CrmSabz;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{

		$companyId = auth()->id();
		/** @var CompanyCustomer[] $companyCustomers */
		$companyCustomers = CompanyCustomer::CompanyId($companyId)
			->paginate();

		return $companyCustomers;
	}


	public function store(StoreCustomerRequest $request)
	{

        CrmSabz::_()->checkMobilePhone($request->mobile_number, null);

		$companyId = auth()->id();
		$customerIds = array_column($request->customers, 'referral_id');

		/** @var CompanyCustomer[] $companyCustomersEntity */
		$companyCustomersEntity = CompanyCustomer::CompanyId($companyId)
			->ReferralId($customerIds)
			->get()
			->keyBy('referral_id');

		foreach ($request->customers as $customer) {

			$referral_id = $customer['referral_id'];

			/** @var CompanyCustomer $companyCustomer */
			if (isset($companyCustomersEntity[$referral_id]) && $companyCustomersEntity[$referral_id])
				$companyCustomer = $companyCustomersEntity[$referral_id];
			else
				$companyCustomer = new CompanyCustomer();

			$companyCustomer->referral_id = $customer['referral_id'];
			$companyCustomer->email = $customer['email'];
			$companyCustomer->mobile_number = $customer['mobile_number'];

			$companyCustomer->first_name = $customer['first_name'];
			$companyCustomer->last_name = $customer['last_name'];
			$companyCustomer->national_id = $customer['national_id'];

			$companyCustomer->company_id = auth('mobile')->user()->company_id;

//			$priceClass = PriceClass::ReferralId($customer['price_classes'])
//				->first();
			$companyCustomer->price_class_id = $customer['price_classes'];// $priceClass->id;

			$companyCustomer->address = [
				'address' => $customer['address'],
				'lat'     => $customer['lat'],
				'long'    => $customer['lng'],
			];

			$companyCustomer->save();

            $storeCrm =CrmSabz::_()->storeCrm('create','app',$request, $customer->id);
            if ($storeCrm) {
                $customer->crm_registered = 1;
                $customer->referral_id =$storeCrm->id ;
                $customer->save();
            }
		}

		return [
			'status'  => true,
			'message' => trans('messages.api.company.customer.store'),
		];
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  UpdateCustomerRequest $request
	 * @param  int                   $referral_id
	 * @return \Illuminate\Http\Response
	 */
	public function update(UpdateCustomerRequest $request)
	{
	  CrmSabz::_()->checkCorectData($request);
		$companyId = auth()->id();


		$customerIds = array_column($request->customers, 'referral_id');

		/** @var CompanyCustomer[] $companyCustomersEntity */
		$companyCustomersEntity = CompanyCustomer::CompanyId($companyId)
			->ReferralId($customerIds)
			->get()
			->keyBy('referral_id');

		foreach ($request->customers as $customer) {

			/** @var CompanyCustomer $companyCustomer */
			$referral_id = $customer['referral_id'];
			$companyCustomer = $companyCustomersEntity[$referral_id];

			$companyCustomer->referral_id = $customer['referral_id'];
			$companyCustomer->email = $customer['email'];
			$companyCustomer->mobile_number = $customer['mobile_number'];

			$companyCustomer->first_name = $customer['first_name'];
			$companyCustomer->last_name = $customer['last_name'];

			$companyCustomer->company_id = auth('mobile')->user()->company_id;

			foreach ($customer->price_classes as $price_class) {
				$priceClass = PriceClass::ReferralId($price_class['referral_id'])
					->first();

				$companyCustomer->price_class_id = $priceClass->id;
				$companyCustomer->price_class_price = $price_class['price'];
			}

			$companyCustomer->save();
            $storeCrm =CrmSabz::_()->storeCrm('update','app',$request, $customer->id);
            if ($storeCrm) {
                $customer->crm_registered = 1;
                $customer->referral_id =$storeCrm->id ;
                $customer->save();
            }
		}

		return [
			'status'  => true,
			'message' => trans('messages.api.company.customer.update'),
		];
	}
}
