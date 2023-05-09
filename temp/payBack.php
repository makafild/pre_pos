<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Cms\Packages\pod_business\PodBusiness;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Fanap\Platform\Fanapium;
use Cms\Packages\traverser\TraverserLicense;
use Cms\Packages\self_declaration\SelfDeclaration;

class payBack extends Command
{
    const OCR_SELECT_TYPE = 'accept';
    const SELF_DECLARATION_DEADLINE_BEFORE_DAY = 10;
    const SELF_DECLARATION_DEADLINE_AFTER_DAY = 2;
    const SELF_DECLARATION_DEADLINE_SECOND = 10;
    const SELF_DECLARATION_DEADLINE_THIRD = 12;
    const SELF_DECLARATION_DEADLINE_FOURTH = 15;
    const LIMITED = 20000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payBack:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan payBack:start';

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
            'pay_back.license as pay_back_license',
            'pay_back.id as pay_back_id',
            'pay_back.amount as pay_back_amount',
            'traverse_ocrs.id',
            'traverse_ocrs.status',
            'traverse_ocrs.event_id',
            'traverse_ocrs.license',
            'traverse_ocrs.vehicle_class',
            'pod_businesses.api_token',
            'pod_businesses.user_id',
            'traverse_ocrs.id as ocr_id',
            'traverse_ocrs.traverse_time',
            'traverse_ocrs.orginal_traverse_time as ocr_orginal_traverse_time',
            'toll_prices.product_id',
            'toll_prices.start_date',
            'toll_prices.price as gateway_price',
            'toll_prices.gateway_serial as gateway',
//            DB::raw('l.delegation'),
//            DB::raw('l.state'),
//            DB::raw('l.issuer_id'),
//            DB::raw('l.issuer_user_id'),
//            DB::raw('l.tl as index_issuer'),
            'traverser_licenses.sso_id',
            'traverse_ocrs.version',
//            DB::raw('du.used')
        ];
        $prefix = config('database.connections.oracle.prefix');
        $traverserDelegationUses = DB::table('traverser_delegation_uses')->select('sso_id', 'issuer_id', DB::raw('SUM(price) as used'))->groupBy(['sso_id', 'issuer_id']);
        $traverserStates = "select sso_id,issuer_id, issuer_user_id, delegation,state,created_at, row_number() over (partition by sso_id  order by   case 
                                when state = 'green' then 1 when state = 'yellow' then 2 else 3 end, updated_at asc) as tl  from {$prefix}traverser_states where state is not null";

        $rows = DB::table('traverse_ocrs')
            ->select($selects)
            ->join('toll_prices', function ($join) {
                $join->on('toll_prices.gateway_serial', '=', 'traverse_ocrs.gateway_serial');
                $join->on('toll_prices.vehicle_class', '=', 'traverse_ocrs.vehicle_class');
            })
            ->join('traverser_licenses', 'traverse_ocrs.license', '=', 'traverser_licenses.license')
            ->join('gateway_shares', 'gateway_shares.gateway_serial', '=', 'traverse_ocrs.gateway_serial')
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
                $join->on('self_declarations.traverse_time', '<=', DB::raw($prefix . 'traverse_ocrs.traverse_time + ' . self::SELF_DECLARATION_DEADLINE_AFTER_DAY));
                $join->on('self_declarations.traverse_time', '>=', DB::raw($prefix . 'traverse_ocrs.traverse_time - ' . self::SELF_DECLARATION_DEADLINE_BEFORE_DAY));
                $join->whereNull('self_declarations.ocr_id');
            })
            ->leftJoin('pay_back', 'pay_back.license', '=', 'traverse_ocrs.license')
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
            ->whereNull('self_declarations.traverse_time')
            ->where('traverse_ocrs.state', self::OCR_SELECT_TYPE)
            ->limit(self::LIMITED)
            ->where(function ($q) {
                $q->whereNull('traverse_ocrs.status');
                $q->orWhereIn('traverse_ocrs.status', ['gray', 'purple', 'pink', 'brown']);
            })
            ->orderBy('traverse_ocrs.status', 'DESC')
            ->orderBy('traverse_ocrs.traverse_time', 'ASC')
            ->get();
        $user = Fanapium::_()->getUserProfile();
        if ($user == null || $user->hasError == true) {
            return $user;
        }
        if ($rows->isNotEmpty()) {
            $ocrs = [];
            $rows = $rows->sortBy('index_issuer');
            foreach ($rows as $row) {
                if (in_array($row->id, $ocrs)) {
                    continue;
                }
//                $row = master::_()->multiIssuers($rows, $row);
                $row = master::_()->multiTollPrice($rows, $row);
                $ocrs[] = $row->id;
                if (!empty($row->pay_back_license)) {

                    if ($row->pay_back_amount >= $row->gateway_price) {
                        $payment_id = $row->pay_back_id . '_' . $row->ocr_id . '_' . Carbon::now()->timestamp;
                        $checkPaymentId = DB::table('payment_uniques')->where(['payment_id' => $payment_id, 'provider_id' => (int)$row->user_id])->exists();
                        if ($checkPaymentId == true) {
                            echo 'payment_id not unique!!!', $checkPaymentId . "\n";
                            continue;
                        }

                        $thingCreate = Fanapium::_()->createThings([
                            'name' => $row->license,
                            'type' => 'vehicle',
                            'metadata' => json_encode(['vehicle_class' => $row->vehicle_class])
                        ]);
                        if (!empty($thingCreate->error) && $thingCreate->error != 'conflict_thing_username') {
                            $thingGet = Fanapium::_()->getThingsByIdenty('name', $row->license);
                            if (!empty($thingGet->error)) {
                                if ($thingGet->error != 'conflict_thing_username') {
                                    DB::rollback();
                                    echo 'things Error!!!' . "\n";
                                    continue;
                                }
                            }
                        }

                        $checkLicense = TraverserLicense::where('license', $row->license)->exists();
                        if (!$checkLicense) {
                            TraverserLicense::_()->insertRow(['license' => $row->license]);
                        }
                        $charged = 0;
                        $increaseCredit = Fanapium::_()->increaseCredit([
                            'amount' => $row->gateway_price,
                            'userId' => (int)$user->result->userId,
                            'wallet' => 'SEPANDAR_WALLET',
                            'billNumber' => $payment_id,
                            'description' => 'Sepandar'
                        ]);
                        if ($increaseCredit->hasError == false) {
                            $charged = 1;
                        }
                        $selfDeclaration = [
                            'provider_id' => (int)$row->user_id,
                            'bill_number' => $payment_id,
                            'charged' => $charged,
                            'gateway_serial' => (int)$row->gateway,
                            'price' => $row->gateway_price,
                            'traverse_time' => $row->traverse_time,
                            'license' => $row->license,
                            'payment_id' => $payment_id,
                            'vehicle_class' => $row->vehicle_class,
                            'type' => 'pay_back',
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                        try {
                            SelfDeclaration::insert($selfDeclaration);

                            DB::table('payment_uniques')->insert([
                                'payment_id' => $payment_id,
                                'provider_id' => (int)$row->user_id
                            ]);

                            DB::table('pay_back')->where('id', $row->pay_back_id)->update([
                                'amount' => $row->pay_back_amount - $row->gateway_price,
                            ]);

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollback();
                            echo 'SelfDeclaration update Error!!!' . json_encode($e->getMessage()) . "\n";
                            continue;
                        }
                    }
                }
            }
        }
    }
}
