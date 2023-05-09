<?php

namespace App\Events\CompanyReport;

use App\Common\CompanyReport;
use App\Models\User\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReportRequestedEvent
{
	use Dispatchable, InteractsWithSockets, SerializesModels;
	/**
	 * @var User[]
	 */
	public $companies;
	/**
	 * @var CompanyReport
	 */
	public $companyReport;

	/**
	 * Create a new event instance.
	 *
	 * @param User[]        $companies
	 * @param CompanyReport $companyReport
	 */
	public function __construct($companies, CompanyReport $companyReport)
	{
		$this->companies = $companies;
		$this->companyReport = $companyReport;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return \Illuminate\Broadcasting\Channel|array
	 */
	public function broadcastOn()
	{
		return new PrivateChannel('channel-name');
	}
}
