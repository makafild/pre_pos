<?php

namespace App\Listeners\CompanyReport;

use App\Common\CompanyReport;
use App\Events\CompanyReport\ReportRequestedEvent;
use App\Models\User\User;
use App\Notifications\App\CompanyReportNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GetReportListener 
{
	/**
	 * The name of the queue the job should be sent to.
	 *
	 * @var string|null
	 */
	public $queue = 'get_company_report';

	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  ReportRequestedEvent $event
	 * @return void
	 */
	public function handle(ReportRequestedEvent $event)
	{
		\Log::info("handle request");

		$main_turn_overs = [];
		$main_account_balances = [];
		$main_factors = [];
		$main_return_cheques = [];

		for ($i = 0; $i < count($event->companies); $i++) {
			$turn_overs = NULL;
			$account_balances = NULL;
			$factors = NULL;
			$return_cheques = NULL;

			$token = $event->companies[$i]->CompanyToken ? $event->companies[$i]->CompanyToken->token : NULL;
			$referralId = $event->companyReport->customer->getReferralIdBy($event->companies[$i]->id);
			if ($referralId && $token) {
				\Log::info("ready for request");
				$url = $event->companies[$i]->api_url . "/prosha/Customer/GetCustomerAccountReport?customerCode={$referralId}";
				$headers = [
					"Accept: application/json",
					"Content-Type: application/json",

					"Authorization: {$token}",
				];

				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL            => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING       => "",
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_TIMEOUT        => 60,
					CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST  => "GET",
					CURLOPT_HTTPHEADER     => $headers,
				));
				\Log::info("send request");

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);

				if ($response) {
					\Log::info($response);

					$response = json_decode($response);
					$response = json_decode(json_encode($response), true);
					$turn_overs = $response['customerAccount'];
					$account_balances = $response['creditPrice'];
					$factors = $response['customerInvoiceRemain'];
					$return_cheques = $response['customerReturnCheque'];
				} else {
					\Log::info($err);
				}
			}

			$main_turn_overs[$event->companies[$i]->id] = $turn_overs;
			$main_account_balances[$event->companies[$i]->id] = $account_balances;
			$main_factors[$event->companies[$i]->id] = $factors;
			$main_return_cheques[$event->companies[$i]->id] = $return_cheques;

		}

		\Log::info("finish request");

		/** @var CompanyReport $companyReport */
		$companyReport = CompanyReport::where('id', $event->companyReport->id)
			->first();
		$companyReport->turn_overs = $main_turn_overs;
		$companyReport->account_balances = $main_account_balances;
		$companyReport->factors = $main_factors;
		$companyReport->return_cheques = $main_return_cheques;
		$companyReport->save();

		// send notification
		$notification = new CompanyReportNotification($companyReport);
		$companyReport->customer->notify($notification);
	}
}
