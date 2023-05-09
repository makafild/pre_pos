<?php

namespace App\Listeners\Order;

use App\Models\Order\Order;
use Core\Packages\report\ReportsSaleProductRoute;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateReportProductSale
{
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
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $orders = Order::with('Customer.Route', 'Details')->whereIn('id', $event->order_id)->get();
        foreach ($orders as $order) {
            if(!isset($order->customer->routes[0]->route_id)) continue;
            $route = $order->customer->routes[0]->route_id;
            foreach ($order->details as $detali) {
                $report = ReportsSaleProductRoute::where('product_id', $detali->product_id)->where('route_id', $route)->first();
                if ($report) {
                    $report->num_facktor += 1;
                    $report->num_master_sale += $detali->master;
                    $report->num_slave_sale += $detali->slave;
                    $report->num_slave2_sale += $detali->slave2;
                    $report->sum_num_slave2_sale += $detali->total;
                    $report->price_num_slave2_sale += $detali->price;
                    $report->save();
                } else {

                    $reports = new ReportsSaleProductRoute();
                    $reports->route_id = $route;
                    $reports->product_id = $detali->product_id;
                    $reports->num_facktor = 1;
                    $reports->num_master_sale = $detali->master;
                    $reports->num_slave_sale = $detali->slave;
                    $reports->num_slave2_sale = $detali->slave2;
                    $reports->sum_num_slave2_sale = $detali->total;
                    $reports->price_num_slave2_sale = $detali->price;
                    $reports->save();
                }
            }
        }
    }
}
