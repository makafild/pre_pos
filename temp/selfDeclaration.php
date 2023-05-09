<?php

namespace App\Console\Commands;

use Cms\Packages\traverser\TraverserDelegationUse;
use Cms\Packages\traverse_ocr\TraverseOcr;
use Fanap\Platform\Fanapium;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Cms\Packages\toll_exemption\TollExemption;
use Cms\Packages\pay_back\PayBack;

class selfDeclaration extends Command
{

    const SELF_DECLARATION_DEADLINE_BEFORE_DAY = 10;
    const SELF_DECLARATION_DEADLINE_AFTER_DAY = 2;
    const ORC_SELECT_TYPE = 'accept';
    const LIMITED = 1000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'traverser:selfDeclaration';

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
        $selects = [
            'self_declarations.traverse_time as self_traverse_time',
            'self_declarations.id as self_id',
            'self_declarations.provider_id as issuer_user_id',
            'self_declarations.bill_number',
            'self_declarations.price as self_price',
            'pod_businesses.api_token',
            'traverse_ocrs.id',
            'gateways.freeway_serial',
            'traverse_ocrs.id as ocr_id',
            'traverse_ocrs.license',
            'traverse_ocrs.vehicle_class',
            'traverse_ocrs.traverse_time as ocr_traverse_time',
            'traverse_ocrs.orginal_traverse_time as ocr_orginal_traverse_time',
            'toll_prices.id as toll_price_id',
            'toll_prices.freeway_amount',
            'toll_prices.product_id',
            'toll_prices.start_date',
            'toll_prices.price as gateway_price',
            'toll_prices.gateway_serial as gateway'
        ];
        $prefix = config('database.connections.oracle.prefix');
        $rows = DB::table('traverse_ocrs')
            ->select($selects)
            ->join('toll_prices', function ($join) {
                $join->on('toll_prices.gateway_serial', '=', 'traverse_ocrs.gateway_serial');
                $join->on('toll_prices.vehicle_class', '=', 'traverse_ocrs.vehicle_class');
            })
            ->join('gateways', 'gateways.serial', '=', 'traverse_ocrs.gateway_serial')
            ->join('gateway_shares', function ($join) {
                $join->on('gateway_shares.gateway_serial', '=', 'traverse_ocrs.gateway_serial');
                $join->on('gateway_shares.vehicle_class', '=', 'traverse_ocrs.vehicle_class');
            })
            ->join('pod_businesses', function ($join) {
                $join->on('gateway_shares.business_id', '=', 'pod_businesses.business_id');
                $join->where('pod_businesses.type', 'sepandar');
            })
            ->leftJoin('self_declarations', function ($join) use ($prefix) {
                $join->on('self_declarations.license', '=', 'traverse_ocrs.license');
                $join->on('self_declarations.gateway_serial', '=', 'traverse_ocrs.gateway_serial');
                $join->on('self_declarations.vehicle_class', '=', 'traverse_ocrs.vehicle_class');
                /*
                 *deadline self declaration =>  10 days before traverse until 2 days later
                 */
                $join->on('self_declarations.traverse_time', '<=', DB::raw($prefix . 'traverse_ocrs.traverse_time + ' . master::SELF_DECLARATION_DEADLINE_AFTER_DAY));
                $join->on('self_declarations.traverse_time', '>=', DB::raw($prefix . 'traverse_ocrs.traverse_time - ' . master::SELF_DECLARATION_DEADLINE_BEFORE_DAY));
                $join->whereNull('self_declarations.ocr_id');
            })
            ->whereNotNull('self_declarations.traverse_time')
            ->where('traverse_ocrs.state', master::OCR_SELECT_TYPE)
            ->where('self_declarations.charged', 1)
            ->where('self_declarations.pay_back', 0)
            ->limit(master::LIMITED)
            ->where(function ($q) {
                $q->whereNull('traverse_ocrs.status');
                $q->orWhereIn('traverse_ocrs.status', ['gray', 'purple', 'pink', 'brown']);
            })
            ->orderBy('self_declarations.traverse_time', 'ASC')
            ->orderBy('traverse_ocrs.traverse_time', 'ASC')
            ->get();
        if ($rows->isNotEmpty()) {
            $ocrs = [];
            $ocrRecords = [];
            $selfIdPayBack = [];

//           $x=[];
//            foreach ($rows as $row) {
//                $row = master::_()->multiTollPrice($rows, $row);
//                $x[]=[
//                    $row->id =>$row->gateway_price
//                ];
//            }
//
//            dd($x);


            foreach ($rows as $row) {
                $row = master::_()->multiTollPrice ($rows, $row);
                $ocrs[] = $row->id;
                $user = Fanapium::_()->getUserById($row->issuer_user_id);
                if (!empty($user->error)) {
                    echo 'user_id invalid' . json_encode($row);
                    continue;
                }

                $row->issuer_id = $user->id;
                $discounts = master::_()->listAvailableDiscountPackages($row);
                $ocrRecords[$row->ocr_id]['ocr_traverse_time'] = $row->ocr_traverse_time;
                $ocrRecords[$row->ocr_id]['ocr_id'] = $row->ocr_id;
                $ocrRecords[$row->ocr_id]['self_id'] = $row->self_id;
                $ocrRecords[$row->ocr_id]['self_price'] = $row->self_price;
                $ocrRecords[$row->ocr_id]['api_token'] = $row->api_token;
                $ocrRecords[$row->ocr_id]['gateway'] = $row->gateway;
                $ocrRecords[$row->ocr_id]['product_id'] = $row->product_id;
                $ocrRecords[$row->ocr_id]['bill_number'] = $row->bill_number;
                $ocrRecords[$row->ocr_id]['toll_price_id'] = $row->toll_price_id;
                $ocrRecords[$row->ocr_id]['freeway_serial'] = $row->freeway_serial;
                $ocrRecords[$row->ocr_id]['issuer_user_id'] = $row->issuer_user_id;
                $ocrRecords[$row->ocr_id]['issuer_id'] = $row->issuer_id;
                $ocrRecords[$row->ocr_id]['gateway_price'] = $row->gateway_price;
                $ocrRecords[$row->ocr_id]['gateway_serial'] = $row->gateway;
                $ocrRecords[$row->ocr_id]['freeway_amount'] = $row->freeway_amount;
                $ocrRecords[$row->ocr_id]['license'] = $row->license;
                $ocrRecords[$row->ocr_id]['vehicle_class'] = $row->vehicle_class;
                $ocrRecords[$row->ocr_id]['self_declarations'][$row->self_id] = (array)$row;
                $ocrRecords[$row->ocr_id]['discounts'] = $discounts;
            }
        }

        if (!empty($ocrRecords)) {
            foreach ($ocrRecords as $oc => $ocrRecord) {
                DB::beginTransaction();
                try {
                    $calculateDiscount = master::_()->calculateDiscount($ocrRecord);
                    $haveTollExemption = master::_()->haveTollExemption($ocrRecord['license']);
                    $issueInvoice = master::_()->issueInvoice($haveTollExemption,$ocrRecord);
                    $usedSelf[$ocrRecord['ocr_id']] = false;
                    $discountPrice = $calculateDiscount['gatewayAmount'] + $calculateDiscount['sepandarAmount'];
                    if ($ocrRecord['self_price'] + $discountPrice != $ocrRecord['gateway_price']) {
                        if ($ocrRecord['self_price'] + $discountPrice > $ocrRecord['gateway_price']) {
                            $payBackAmount = $ocrRecord['self_price'] + $discountPrice - $ocrRecord['gateway_price'];
                            master::_()->increasePayBack($ocrRecord['license'], $payBackAmount);
                        } else {
                            if (in_array($ocrRecord['self_id'], $selfIdPayBack) == false) {
                                master::_()->increasePayBack($ocrRecord['license'], $ocrRecord['self_price']);
                                DB::table('self_declarations')->where(['id' => $ocrRecord['self_id'], 'ocr_id' => null])->update(['pay_back' => 1]);
                                $selfIdPayBack[] = $ocrRecord['self_id'];
                            }
                            DB::commit();
                            continue;
                        }
                    }
                    foreach ($ocrRecord['self_declarations'] as $self) {
                        $ocrsUpdate[] = $self['id'];
                        $usedSelf[$ocrRecord['ocr_id']] = DB::table('self_declarations')->where(['id' => $self['self_id'], 'ocr_id' => null])->update(['ocr_id' => $ocrRecord['ocr_id']]);
                        if ($usedSelf[$ocrRecord['ocr_id']]) {
                            $selfIdPayBack[] = $ocrRecord['self_id'];
                            break;
                        }
                    }
                    if ($usedSelf[$ocrRecord['ocr_id']] == true) {
                        master::_()->ocrChangeStatus($ocrRecord['ocr_id'], 'white');
                        if ($issueInvoice != false) {
                            DB::table('invoice_logs')->where('ocr_id', $ocrRecord['ocr_id'])->update(['state' => 'white']);
                        }
                        if (count($calculateDiscount)) {
                            $traverserDelegationUse = DB::table('traverser_delegation_uses')->where('ocr_id', $ocrRecord['ocr_id'])->first();
                            if (empty($traverserDelegationUse)) {
                                master::_()->usesDiscountPackages($row, $calculateDiscount['usesPackages']);
                            }
                        }
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    echo 'exception => ' . json_encode($e->getMessage()) . ' data => ' . json_encode($row) . "\n";
                    continue;
                }
            }
        }
    }
}
