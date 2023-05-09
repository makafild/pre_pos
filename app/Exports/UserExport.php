<?php
/**
 * Created by PhpStorm.
 * User: imohammad
 * Date: 3/30/18
 * Time: 5:23 AM
 */

namespace App\Exports;

use App\Models\User\CompanyCustomer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromCollection, WithHeadings
{
	public function headings(): array
	{
		\App::setLocale('fa');

		return collect(CompanyCustomer::first())->keys()->map(function ($item, $key) {
			return trans('messages.exports.customer.' . $item);
		})->toArray();
	}

	public function collection()
	{
		if (auth()->user()->can('userSuperIndex', CompanyCustomer::class)) {
			return CompanyCustomer::all();
		} elseif (auth()->user()->can('userIndex', CompanyCustomer::class)) {
			$companyId = auth()->user()->company_id;

			return CompanyCustomer::CompanyId($companyId)->get();
		}

		return CompanyCustomer::whereNull('updated_at')->whereNotNull('updated_at');
	}
}