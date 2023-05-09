<?php

namespace App\Http\Controllers\api\Customer\v1\User;

use App\Common\CompanyReport;
use App\Events\CompanyReport\ReportRequestedEvent;
use App\Http\Requests\api\Customer\v1\User\CompanyReport\StoreCompanyReportRequest;
use App\Models\User\CompanyCustomer;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompanyReportController extends Controller
{
	public function index()
	{
		/** @var CompanyReport $companyReport */
		$companyReport = CompanyReport::CustomerId(auth()->id())
			->with(['companies.photo'])
			->latest()
			->paginate(30);

		return $companyReport;
	}

	public function store(StoreCompanyReportRequest $request)
	{
		/** @var CompanyCustomer[] $companyCustomers */
		$companyCustomers = CompanyCustomer::CustomerId(auth()->id())
			->whereIn('company_id', $request->companies_id)
			->get();

		$availableCompaniesId = $companyCustomers->pluck('company_id');
		if (!count($availableCompaniesId)) {
			return [
				'status'  => false,
				'message' => 'شما در این شرکت‌ فعال نمی‌باشید.',
			];
		}

		/** @var CompanyReport $companyReport */
		$companyReport = CompanyReport::CustomerId(auth()->id())
			->latest()
			->first();

		if ($companyReport && $companyReport->created_at->diffInSeconds() < 120) {
			return [
				'status'  => false,
				'message' => 'باید برای درخواست مجدد ۱۲۰ ثانیه صبر کنید.',
			];
		}

		$companyReport = new CompanyReport();
		$companyReport->customer_id = auth()->id();
		$companyReport->save();

		$companyReport->Companies()->attach($availableCompaniesId);

		/** @var User[] $companies */
		$companies = User::whereIn('id', $availableCompaniesId)
			->get();

		event(new ReportRequestedEvent($companies, $companyReport));

		return [
			'status'  => true,
			'message' => 'درخواست شما با موفقیت ثبت شد. پس از دریافت اطلاعات به شما اطلاع داده می‌شود.',
			'id'      => $companyReport->id,
		];
	}

	public function show($id)
	{
		/** @var CompanyReport $companyReport */
		$companyReport = CompanyReport::with(['companies.photo'])
			->findOrFail($id);

		foreach ($companyReport->companies as $company) {
			$companyId = $company->id;
			$role = auth('mobile')->user()->roles()->where('company_id', $companyId)->first();
			if (!$role) {
				$role = Role::where('name', "$companyId@customer_api@default")->first();
			}

			if (!$role || !$role->hasPermissionTo('customer_api.Company.report_turn_overs')) {
				$turn_overs = $companyReport->turn_overs;
				$turn_overs[$companyId] = '';
				$companyReport->turn_overs = $turn_overs;
			}
			if (!$role || !$role->hasPermissionTo('customer_api.Company.report_account_balances')) {
				$account_balances = $companyReport->account_balances;
				$account_balances[$companyId] = NULL;
				$companyReport->account_balances = $account_balances;
			}
			if (!$role || !$role->hasPermissionTo('customer_api.Company.report_factors')) {
				$factors = $companyReport->factors;
				$factors[$companyId] = NULL;
				$companyReport->factors = $factors;
			}
			if (!$role || !$role->hasPermissionTo('customer_api.Company.report_return_cheques')) {
				$return_cheques = $companyReport->return_cheques;
				$return_cheques[$companyId] = NULL;
				$companyReport->return_cheques = $return_cheques;
			}
		}

		return $companyReport;
	}
}
