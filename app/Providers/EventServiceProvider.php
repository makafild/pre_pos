<?php

namespace App\Providers;

use App\Events\Order\RegisterOrder;
use Illuminate\Support\Facades\Event;
use App\Events\Order\OrderSendToRobot;
use Illuminate\Auth\Events\Registered;
use App\Listeners\Order\SendToRobotOrder;
use App\Listeners\Order\UpdateReportProductSale;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\Order\ChangeStatusEvent'            => [
	    //'App\Listeners\Order\SendNotificationListener',
            'App\Listeners\Order\OrderConfirmedListener',
        ],
        'App\Events\PeriodicOrder\PeriodicOrderTimeEvent' => [
            'App\Listeners\PeriodicOrder\SendNotificationListener',
        ],

        'App\Events\CompanyReport\ReportRequestedEvent'   => [
            'App\Listeners\CompanyReport\GetReportListener',
        ],
        'App\Events\Notification\NotificationStoredEvent' => [
            'App\Listeners\Notification\SendNotificationListener',
        ],
        'App\Events\Message\MessageStoredEvent'           => [
            'App\Listeners\Message\SendNotificationListener',
        ],
        'App\Events\User\SendSMSEvent'                  => [
            'App\Listeners\User\SendSMS',
        ],
        RegisterOrder::class => [
          UpdateReportProductSale::class  ,
        ],
        OrderSendToRobot::class => [
            SendToRobotOrder::class  ,
          ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
