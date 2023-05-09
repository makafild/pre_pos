<?php

namespace App\Notifications\App;

use App\Common\CompanyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class CompanyReportNotification extends Notification
{
    use Queueable;
	/**
	 * @var CompanyReport
	 */
	private $companyReport;

	/**
	 * Create a new notification instance.
	 *
	 * @param CompanyReport $companyReport
	 */
	public function __construct(CompanyReport $companyReport)
	{
		//
		$this->companyReport = $companyReport;
	}

	public function via($notifiable)
	{
		return [	FirebasePushChannel::class];
	}

	public function toOneSignal($notifiable)
	{
		return OneSignalMessage::create()
			->subject(trans('notification.company_report.title'))
			->body(trans('notification.company_report.body'))
			->setData('name', 'company_report')
			->setData('id', $this->companyReport->id);
	}
    public function toFcmPush($notifiable)
    {
        $content = [
            'message' => trans('notification.company_report.body'),
            'header' =>trans('notification.company_report.title') ,
            'name' =>  'company_report' ,
            "priority"=>"high",
            "data"=>[
                "payload"=>[
                    "name"=>"push",
                    "title"=> trans('notification.company_report.title'),
                    "body"=> trans('notification.company_report.body'),
                    "id"=> $this->companyReport->id,
                ]
            ]
        ];

        return $content;
    }
}
