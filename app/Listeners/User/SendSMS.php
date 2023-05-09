<?php

namespace App\Listeners\User;

use App\Events\User\SendSMSEvent;
use App\Services\IranSMSService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSMS
{
	public $queue = 'notification';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function handle(SendSMSEvent $event)
    {
        error_log("ShouldQueue");
		\Log::info('SEND SMS');
		IranSMSService::send($event->message, $event->user);
	}
}
