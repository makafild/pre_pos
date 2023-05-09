<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class repairInvoicePrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $prefix = config('database.connections.oracle.prefix');

        $invoiceLogs = DB::table('invoice_logs')
            ->select([
                'traverse_ocrs.id as ocr_id',
                'traverse_ocrs.orginal_traverse_time',
                'invoice_logs.*',
                'toll_prices.start_date',
                'toll_prices.price'
            ])
            ->join('traverse_ocrs', 'traverse_ocrs.id', '=', 'invoice_logs.ocr_id')
            ->join('toll_prices', function ($join) {
                $join->on('toll_prices.gateway_serial', '=', 'traverse_ocrs.gateway_serial');
                $join->on('toll_prices.vehicle_class', '=', 'traverse_ocrs.vehicle_class');
            })->orderBy('start_date','desc')->get();
        /*
    * multi prices
    */

        /*
       * end multi prices
       */
        $ocrs=[];
        foreach ($invoiceLogs as $il => $invoiceLog) {
            if (in_array($invoiceLog->ocr_id, $ocrs)) {
                continue;
            }
            foreach ($invoiceLogs as $rowPrice) {
                if ($invoiceLog->ocr_id == $rowPrice->ocr_id) {
                    if($rowPrice->start_date<=$rowPrice->orginal_traverse_time){
                        $invoiceLog->price = $rowPrice->price;
                        break;
                    }
                }
            }
            $ocrs[] = $invoiceLog->ocr_id;
            $sharing = json_decode($invoiceLog->share);
            $sharing->customerInvoiceItemVOs[0]->price = $invoiceLog->price;
            $sharing->subInvoices[0]->invoiceItemVOs[0]->price = $invoiceLog->price;
            $invoiceLog->share = json_encode($sharing);
            $updateId = $invoiceLog->id;
            unset($invoiceLog->id);
            DB::table('invoice_logs')->where('id', $updateId)->update((array)$invoiceLog);
        }
    }
}
