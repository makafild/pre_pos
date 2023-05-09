<?php

namespace App\Console\Commands;

use App\BaseModel;
use Cms\Packages\gateway_share\GatewayShare;
use Cms\Packages\issuer\Issuer;
use Cms\Packages\pod_business\PodBusiness;
use Cms\Packages\traverse_ocr\TraverseOcr;
use Cms\Packages\traverser\TraverserDelegationUse;
use Cms\Packages\gateway\Gateway;
use Fanap\Platform\Fanapium;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class noSelfDeclaration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'traverser:noSelfDeclaration';
    private $ocr_status = ['gray', 'purple', 'pink', 'brown'];

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

//        $time = time();
//        $log = "\n[" . date("Y/m/d H:i:s") . "] - ";
//        $selects = [
//            'traverse_ocrs.id',
//            'traverse_ocrs.status',
//            'traverse_ocrs.event_id',
//            'traverse_ocrs.license',
//            'traverse_ocrs.id as ocr_id',
//            'traverse_ocrs.vehicle_class',
//            'traverse_ocrs.traverse_time as ocr_traverse_time',
//            'traverse_ocrs.orginal_traverse_time as ocr_orginal_traverse_time',
//            'traverse_ocrs.version',
//            'gateways.freeway_serial',
//            'pod_businesses.api_token',
//            'toll_prices.freeway_amount',
//            'toll_prices.id as toll_price_id',
//            'toll_prices.product_id',
//            'toll_prices.start_date',
//            'toll_prices.price as gateway_price',
//            'toll_prices.gateway_serial as gateway',
//            DB::raw('l.delegation'),
//            DB::raw('l.state'),
//            DB::raw('l.issuer_id'),
//            DB::raw('l.issuer_user_id'),
//            DB::raw('l.tl as index_issuer'),
//            'traverser_licenses.sso_id',
//            DB::raw('du.used')
//        ];
//
//        $prefix = config('database.connections.oracle.prefix');
//        $traverserDelegationUses = DB::table('traverser_delegation_uses')->select('sso_id', 'issuer_id', DB::raw('SUM(price) as used'))->groupBy(['sso_id', 'issuer_id']);
//
//        $traverserStates = "select sso_id,issuer_id, issuer_user_id, delegation,state,created_at, row_number() over (partition by sso_id  order by   case
//                                when state = 'green' then 1 when state = 'yellow' then 2 else 3 end, updated_at asc) as tl  from {$prefix}traverser_states where state is not null";
//
//        $rows = DB::table('traverse_ocrs')
//            ->select($selects)
//            ->join('toll_prices', function ($join) {
//                $join->on('toll_prices.gateway_serial', '=', 'traverse_ocrs.gateway_serial');
//                $join->on('toll_prices.vehicle_class', '=', 'traverse_ocrs.vehicle_class');
//            })
//            ->join('gateways', 'gateways.serial', '=', 'traverse_ocrs.gateway_serial')
//            ->join('traverser_licenses', 'traverse_ocrs.license', '=', 'traverser_licenses.license')
//            ->join('gateway_shares', function ($join) {
//                $join->on('gateway_shares.gateway_serial', '=', 'traverse_ocrs.gateway_serial');
//                $join->on('gateway_shares.vehicle_class', '=', 'traverse_ocrs.vehicle_class');
//            })
//            ->join('pod_businesses', function ($join) {
//                $join->on('gateway_shares.business_id', '=', 'pod_businesses.business_id');
//                $join->where('pod_businesses.type', 'sepandar');
//            })
//            ->leftJoin('self_declarations', function ($join) use ($prefix) {
//                $join->on('self_declarations.license', '=', 'traverse_ocrs.license');
//                $join->on('self_declarations.gateway_serial', '=', 'traverse_ocrs.gateway_serial');
//                $join->on('self_declarations.vehicle_class', '=', 'traverse_ocrs.vehicle_class');
//                /*
//                *deadline self declaration =>  10 days before traverse until 2 days later
//                */
//                $join->on('self_declarations.traverse_time', '<=', DB::raw($prefix . 'traverse_ocrs.traverse_time + ' . master::SELF_DECLARATION_DEADLINE_AFTER_DAY));
//                $join->on('self_declarations.traverse_time', '>=', DB::raw($prefix . 'traverse_ocrs.traverse_time - ' . master::SELF_DECLARATION_DEADLINE_BEFORE_DAY));
//                $join->where('self_declarations.pay_back', 0);
//                $join->whereNull('self_declarations.ocr_id');
//            })
//            ->leftJoin('pay_back', 'pay_back.license', '=', 'traverse_ocrs.license')
//            ->leftJoin(DB::raw("({$traverserStates}) l"),
//                function ($join) {
//                    $join->on('traverser_licenses.sso_id', '=', DB::raw('l.sso_id'));
//                }
//            )
//            ->leftJoin(DB::raw('(' . $traverserDelegationUses->toSql() . ') du'),
//                function ($join) {
//                    $join->on(DB::raw('l.sso_id'), '=', DB::raw('du.sso_id'));
//                    $join->on(DB::raw('l.issuer_id'), '=', DB::raw('du.issuer_id'));
//                }
//            )
//            ->whereNull('self_declarations.traverse_time')
//            ->where('traverse_ocrs.state', master::OCR_SELECT_TYPE)
//            ->limit(master::LIMITED)
//            ->where(function ($q) {
//                $q->whereNull('traverse_ocrs.status');
//                $q->orWhereIn('traverse_ocrs.status', $this->ocr_status);
//            })
//            ->orderBy('traverse_ocrs.status', 'DESC')
//            ->orderBy('traverse_ocrs.traverse_time', 'ASC')
//            ->get();
//        if ($rows->isNotEmpty()) {
//            $usedAmount = [];
//            $ocrs = [];
//            $x=[];
//            foreach ($rows as $row) {
//                $row = master::_()->multiTollPrice($rows, $row);
//                $x[]=[
//                    $row->id =>$row->gateway_price
//                ];
//            }
//            $rows = $rows->sortBy('index_issuer');
//
//            foreach ($rows as $row) {
////                DB::beginTransaction();
////                try {
//                if (in_array($row->id, $ocrs)) {
//                    continue;
//                }
//
//                $row = master::_()->multiIssuers($rows, $row);
//                $row = master::_()->multiTollPrice($rows, $row);
//                $ocrs[] = $row->id;
//                $discounts = master::_()->listAvailableDiscountPackages($row);
//                $row->discounts = $discounts;
//                $haveTollExemption = master::_()->haveTollExemption($row->license);
//                $invoiceLog = master::_()->issueInvoice($haveTollExemption,(array)$row);
//
//                if ($invoiceLog == false) {
////                    DB::rollback();
//                    continue;
//                }
//                $calculateDiscount = master::_()->calculateDiscount((array)$row);
//
//                $issueInvoice = DB::table('invoice_logs')->where('ocr_id', $row->id)->get();
//                if (count($calculateDiscount)) {
//                    master::_()->usesDiscountPackages($row, $calculateDiscount['usesPackages']);
//                }
//
//                switch ($row->state) {
//                    case 'green' :
//                        master::_()->ocrChangeStatus($row->id, 'white');
//                        if (in_array($row->status, $this->ocr_status)) {
//                            $updateInvoice=master::_()->updateInvoice($row['issuer_user_id'], $row->id);
//                            if ($updateInvoice == false) {
////                                DB::rollback();
//                                continue;
//                            }
//                        }
//                        if ($issueInvoice != false) {
//                            DB::table('invoice_logs')->where('ocr_id', $row->id)->update(['state' => 'white']);
//                        }
//                        break;
//                    case 'red' :
//                        master::_()->blackGrayStatus($row);
//                        break;
//                    case 'yellow' :
//                        if (!isset($usedAmount[$row->sso_id])) {
//                            @$usedAmount[$row->sso_id] = $row->used;
//                        }
//
//                        if ($row->delegation < ($row->gateway_price + (int)$usedAmount[$row->sso_id])) {
//                            master::_()->blackGrayStatus($row);
//                        } else {
//                            master::_()->ocrChangeStatus($row->id, 'white');
//                            if (in_array($row->status, $this->ocr_status)) {
//                                $updateInvoice= master::_()->updateInvoice($row['issuer_user_id'], $row->id);
//                                if ($updateInvoice == false) {
////                                    DB::rollback();
//                                    continue;
//                                }
//                            }
//                            if ($issueInvoice != false) {
//                                DB::table('invoice_logs')->where('ocr_id', $row->id)->update(['state' => 'white']);
//                            }
//                            TraverserDelegationUse::_()->insertRow([
//                                'ocr_id' => $row->id,
//                                'sso_id' => $row->sso_id,
//                                'issuer_id' => $row->issuer_id,
//                                'traverse_time' => $row->ocr_traverse_time,
//                                'price' => $haveTollExemption == true ? 0 : $invoiceLog['price'] - $invoiceLog['price_discount'],
//                                'gateway_serial' => $row->gateway,
//                            ]);
//                            @$usedAmount[$row->sso_id] += $row->gateway_price;
//                        }
//                        break;
//                    default :
//                        master::_()->blackGrayStatus($row);
//                }
//                if ($haveTollExemption == true) {
//                    master::_()->ocrChangeStatus($row->id, 'white');
//                }

//                    DB::commit();
//                } catch (\Exception $e) {
//                    echo 'exception => ' . json_encode($e->getMessage()) . ' data => ' . json_encode($row) . "\n";
//                    DB::rollback();
//                    continue;
//                }
//            }
//
//        }
//
//        echo $log . count($rows) . " time : " . (time() - $time) . " s";
    }
}

