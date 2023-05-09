<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Cms\Packages\gateway\Gateway;
use Cms\Packages\discount_package\DiscountPackage;
use Cms\Packages\toll_exemption\TollExemption;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Fanap\Platform\Fanapium;
use Cms\Packages\gateway_share\GatewayShare;
use Cms\Packages\pod_business\PodBusiness;
use Cms\Packages\traverse_ocr\TraverseOcr;
use Cms\Packages\pay_back\PayBack;
use App\BaseModel;

class master extends Command
{
    const OCR_SELECT_TYPE = 'accept';
    const SELF_DECLARATION_DEADLINE_AFTER_DAY = 2;
    const SELF_DECLARATION_DEADLINE_BEFORE_DAY = 10;
    const LIMITED = 20000;

    private static $_instance = null;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'master functions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new master;
        }
        return self::$_instance;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */

    function ocrChangeStatus($id, $status)
    {
        return TraverseOcr::where('id', $id)->update(['status' => $status]);
    }

    function haveTollExemption($license)
    {
        $haveTollExemption = false;
        $tollExemption = TollExemption::select('license')
            ->where('license', $license)
            ->where('status', 'accept')
            ->first();
        if (!empty($tollExemption)) {
            $haveTollExemption = true;
        }
        return $haveTollExemption;
    }

    public function listAvailableDiscountPackages($row)
    {
        $gatewayArray = Gateway::all()->pluck('name', 'serial')->toArray();;
        $discountPackages = [];
        $gatewaysPackages = DB::table('discount_package_gateways')->get();
        $pakages = DiscountPackage::where('status', 'active')->get();
        foreach ($pakages as $package) {
            $gw = [];
            foreach ($gatewaysPackages as $gatewaysPackage) {
                if ($gatewaysPackage->discount_package_id == $package->id) {
                    $gw[] = [
                        'id' => $gatewaysPackage->gateway_serial,
                        'name' => $gatewayArray[$gatewaysPackage->gateway_serial]
                    ];
                }
            }
            $package->gateways = [];
            if (count($gw)) {
                $package->gateways = $gw;
            }
            $discountPackages[] = $package->toArray();
        }
        $discounts = [];
        foreach ($discountPackages as $discountPackage) {
            $continue = false;
            if ($discountPackage['unlimit'] == 1) {
                if ($row->ocr_orginal_traverse_time >= $discountPackage['package_from_date'] && $row->ocr_orginal_traverse_time <= $discountPackage['package_to_date']) {
                    $continue = true;
                }
            } else {
                //if 'traverse_count' field is fill(set a number > 0) !!!!
                if ($discountPackage['traverse_count'] > 0) {

                    $packageDiscountUses = DB::table('discount_package_uses')
                        ->where('discount_package_id', $discountPackage['id'])
                        ->where('license', $row->license)
                        ->count();
                    if (($discountPackage['traverse_from_state'] == 'no_limit' && $discountPackage['traverse_to_state'] == 'no_limit') ||
                        ($discountPackage['traverse_from_date'] == 'no_limit' && empty($discountPackage['traverse_to_state'])) ||
                        ($discountPackage['traverse_to_state'] == 'no_limit' && empty($discountPackage['traverse_from_state']))) {
                        if ($packageDiscountUses < $discountPackage['traverse_count']) {
                            $continue = true;
                        }
                    }
                    if (($discountPackage['traverse_from_state'] == 'no_limit' && $discountPackage['traverse_to_state'] == 'to_date') ||
                        ($discountPackage['traverse_to_state'] == 'to_date' && empty($discountPackage['traverse_from_state']))) {
                        if ($packageDiscountUses < $discountPackage['traverse_count'] && $row->ocr_orginal_traverse_time <= $discountPackage['traverse_to_date']) {
                            $continue = true;
                        }
                    }
                    if (($discountPackage['traverse_from_state'] == 'from_date' && $discountPackage['traverse_to_state'] == 'no_limit')
                        || ($discountPackage['traverse_from_state'] == 'from_date' && empty($discountPackage['traverse_to_state']))) {
                        if ($packageDiscountUses < $discountPackage['traverse_count'] && $row->ocr_orginal_traverse_time >= $discountPackage['traverse_from_date']) {
                            $continue = true;
                        }
                    }
                    if ($discountPackage['traverse_from_state'] == 'from_date' && $discountPackage['traverse_to_state'] == 'to_date') {
                        if ($packageDiscountUses < $discountPackage['traverse_count'] && $row->ocr_orginal_traverse_time <= $discountPackage['traverse_to_date'] && $row->ocr_orginal_traverse_time >= $discountPackage['traverse_from_date']) {
                            $continue = true;
                        }
                    }
                }
            }
            if ($continue) {
                if (count($discountPackage['gateways'])) {
                    foreach ($discountPackage['gateways'] as $discountPackageGateway) {
                        if ($row->gateway == $discountPackageGateway['id']) {
                            $includeDiscount = false;
                            if (!empty($discountPackage['license_number']) && !empty($discountPackage['vehicle_class']) && !empty($discountPackage['license_letter'])) {
                                if (substr($row->license, -2) == $discountPackage['license_number'] && $row->vehicle_class == $discountPackage['vehicle_class'] && substr($row->license, 2, 2) == $discountPackage['license_letter']) {
                                    $includeDiscount = true;
                                }
                            } elseif (!empty($discountPackage['license_number']) && !empty($discountPackage['license_letter'])) {
                                if (substr($row->license, -2) == $discountPackage['license_number'] && substr($row->license, 2, 2) == $discountPackage['license_letter']) {
                                    $includeDiscount = true;
                                }
                            } elseif (!empty($discountPackage['license_letter']) && !empty($discountPackage['vehicle_class'])) {
                                if (substr($row->license, 2, 2) == $discountPackage['license_letter'] && $row->vehicle_class == $discountPackage['vehicle_class']) {
                                    $includeDiscount = true;
                                }

                            } elseif (!empty($discountPackage['license_number']) && !empty($discountPackage['vehicle_class'])) {
                                if (substr($row->license, -2) == $discountPackage['license_number'] && $row->vehicle_class == $discountPackage['vehicle_class']) {
                                    $includeDiscount = true;
                                }
                            } elseif (!empty($discountPackage['license_number'])) {
                                if (substr($row->license, -2) == $discountPackage['license_number']) {
                                    $includeDiscount = true;
                                }
                            } elseif (!empty($discountPackage['license_letter'])) {
                                if (substr($row->license, 2, 2) == $discountPackage['license_letter']) {
                                    $includeDiscount = true;
                                }
                            } elseif (!empty($discountPackage['vehicle_class'])) {
                                if ($row->vehicle_class == $discountPackage['vehicle_class']) {
                                    $includeDiscount = true;
                                }
                            } else {
                                $includeDiscount = true;
                            }

                            if ($includeDiscount) {
                                $discounts[$discountPackage['id']] = [
                                    'gatewaySerial' => $discountPackageGateway['id'],
                                    'sepandarDiscount' => $discountPackage['sepandar_share'],
                                    'gatewayDiscount' => $discountPackage['gateway_share'],
                                    'voucherPercent' => $discountPackage['voucher_percent']
                                ];
                            }
                        }
                    }
                }
            }
        }
        return $discounts;
    }

    public function calculateDiscount($row)
    {
        $config = DB::table('configs')->first();
        $maximumDiscount = empty($config) ? 60 : $config->maximum_discount;
        $maxDiscount = ($maximumDiscount * $row['gateway_price']) / 100;
        $usesPackagesBulkArray = [];
        $sepandarAmount = 0;
        $gatewayAmount = 0;
        $gatewayDiscount = 0;
        $sepandarDiscount = 0;
        $haveTollExemption = $this->haveTollExemption($row['license']);


        if ($haveTollExemption == true) {
            $usesPackagesBulkArray[] = [
                'ocr_id' => $row['ocr_id'],
                'license' => $row['license'],
                'discount_package_id' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        } else {
            if (count($row['discounts'])) {
                foreach ($row['discounts'] as $package_id => $discount) {

                    $usesPackagesBulkArray[] = [
                        'ocr_id' => $row['ocr_id'],
                        'license' => $row['license'],
                        'discount_package_id' => $package_id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];

                    $payableAmount = (integer)($row['gateway_price'] - (($row['gateway_price'] * $discount['voucherPercent'] / 100)));
                    if ($discount['sepandarDiscount'] > 0 && $discount['gatewayDiscount'] > 0) {
                        $sepandarAmount += ($row['gateway_price'] - $payableAmount) / 2;
                        $gatewayAmount += ($row['gateway_price'] - $payableAmount) / 2;
                        $sepandarDiscount += $discount['sepandarDiscount'];
                        $gatewayDiscount += $discount['gatewayDiscount'];
                        if ($maxDiscount < ($sepandarAmount + $gatewayAmount)) {
                            $sepandarAmount = $maxDiscount / 2;
                            $gatewayAmount = $maxDiscount / 2;
                            break;
                        }
                    } else {
                        if ($discount['sepandarDiscount'] > 0) {
                            $sepandarAmount += $row['gateway_price'] - $payableAmount;
                            $sepandarDiscount += $discount['sepandarDiscount'];
                            if ($maxDiscount < $sepandarAmount) {
                                $sepandarAmount = $maxDiscount;
                                break;
                            }
                        }
                        if ($discount['gatewayDiscount'] > 0) {
                            $gatewayAmount += $row['gateway_price'] - $payableAmount;
                            $gatewayDiscount += $discount['gatewayDiscount'];
                            if ($maxDiscount < $gatewayAmount) {
                                $gatewayAmount = $maxDiscount;
                                break;
                            }
                        }
                    }

                }
            }
        }
        return [
            'sepandarAmount' => $sepandarAmount,
            'gatewayAmount' => $gatewayAmount,
            'sepandarDiscount' => $sepandarDiscount,
            'gatewayDiscount' => $gatewayDiscount,
            'usesPackages' => $usesPackagesBulkArray
        ];
    }

    public function usesDiscountPackages($row, $usesPackages)
    {
        $traverserDelegationUse = DB::table('discount_package_uses')->where('ocr_id', $row->id)->first();
        if (empty($traverserDelegationUse)) {
            DB::table('discount_package_uses')->insert($usesPackages);
        }
    }

    public function issueInvoice($haveTollExemption, $row)
    {
        if (isset($row['gateway'])) {
            $row['gateway_serial'] = $row['gateway'];
        }
        $calculateDiscount = $this->calculateDiscount($row);
        $gatewayShares = GatewayShare::with('business')
            ->where(['toll_price_id' => $row['toll_price_id']])
            ->get()
            ->toArray();

        $gatewaySharesBusinesses = [];
        foreach ($gatewayShares as $gatewayShare) {
            $amount = $gatewayShare['amount'];
            if ($gatewayShare['type'] == 'percent') {
                $amount = ($row['gateway_price'] * $gatewayShare['amount']) / 100;
            }
            $gatewaySharesBusinesses[$gatewayShare['business']['type']] = [
                'business_id' => $gatewayShare['business_id'],
                'gateway_serial' => (int)$row['gateway'],
                'vehicle_class' => (int)$row['vehicle_class'],
                'amount' => (int)$amount
            ];
        }
        $freewayBusiness = DB::table('freeways')
            ->select('pod_businesses.*')
            ->join('pod_businesses', 'pod_businesses.sso_id', '=', 'freeways.company_sso_id')
            ->where('freeways.serial', $row['freeway_serial'])
            ->first();
        $gatewaySharesBusinesses['freeway'] = [
            'business_id' => $freewayBusiness->business_id,
            'gateway_serial' => (int)$row['gateway'],
            'vehicle_class' => (int)$row['vehicle_class'],
            'amount' => (int)$row['freeway_amount']
        ];
        $description = "قطعه : " . $row['gateway_serial'] . " - پلاک : " . $row['license'] .
            " - زمان تردد : " . $row['ocr_orginal_traverse_time'] . " -  تاریخ ایجاد فاکتور : " .Carbon::now();
        $invoiceLog = [];
        foreach ($gatewaySharesBusinesses as $keyBusiness => $gatewayShareBusiness) {
            $businessToken = PodBusiness::_()->where('business_id', $gatewayShareBusiness['business_id'])->first();
            $invoiceLog[$keyBusiness]['user_id'] = $businessToken->user_id;
            $invoiceLog[$keyBusiness]['price'] = $row['gateway_price'];
            $discount = 0;
            if ($calculateDiscount['sepandarDiscount'] > 0 && $calculateDiscount['gatewayDiscount'] > 0) {
                $discount = ($gatewaySharesBusinesses[$keyBusiness]['amount'] * ($calculateDiscount['sepandarDiscount'])) / 100;// or  + $calculateDiscount['gatewayDiscount']
            } else {
                if ($calculateDiscount['sepandarDiscount'] > 0) {
                    if ($keyBusiness != 'freeway') {
                        $discount = ($gatewaySharesBusinesses[$keyBusiness]['amount'] * $calculateDiscount['sepandarDiscount']) / 100;
                    }
                }
                if ($calculateDiscount['gatewayDiscount'] > 0) {
                    if ($keyBusiness == 'freeway') {
                        $discount = ($gatewaySharesBusinesses[$keyBusiness]['amount'] * $calculateDiscount['gatewayDiscount']) / 100;
                    }
                }
            }
            $invoiceLog[$keyBusiness]['price_discount'] = $haveTollExemption == true ? $gatewayShareBusiness['amount'] : (int)$discount;
            $invoiceLog[$keyBusiness]['ocr_id'] = $row['ocr_id'];
            $invoiceLog[$keyBusiness]['api_token'] = $businessToken->api_token;
            $invoiceLog[$keyBusiness]['created_at'] = Carbon::now();
            $invoiceLog[$keyBusiness]['updated_at'] = Carbon::now();
            $invoiceLog[$keyBusiness]['product_id'] = (int)$row['product_id'];
            $invoiceLog[$keyBusiness]['business_id'] = (int)$gatewayShareBusiness['business_id'];
            $invoiceLog[$keyBusiness]['business_share'] = $gatewayShareBusiness['amount'];
            $billNumber = $row['ocr_id'] . '_' . $gatewayShareBusiness['business_id'];
            $invoiceLog[$keyBusiness]['bill_number'] = $billNumber;
            if ($keyBusiness != 'sepandar') {
                $issueMultiInvoice[$keyBusiness]['uri'] = '/nzh/biz/issueMultiInvoice';
                $issueMultiInvoice[$keyBusiness]['parameters'] = [
                    'data' => [json_encode([
                        'currencyCode' => 'IRR',
                        'addressId' => '0',
                        'preferredTaxRate' => '0',
                        'verificationNeeded' => 'false',
                        'preview' => 'false',
                        'description' => $description,
                        'mainInvoice' => [
                            'guildCode' => 'TRANSPORTATION_GUILD',
                            'billNumber' => $billNumber,
                            'description' => $description,
                            'invoiceItemVOs' => [
                                [
                                    'productId' => $row['product_id'],
                                    'price' => ($keyBusiness == 'sepandar') ? $gatewaySharesBusinesses['sepandar']['amount'] : 0,
                                    'quantity' => '1',
                                    'description' => $description
                                ]
                            ]
                        ],
                        'subInvoices' => [
                            [
                                'businessId' => $gatewayShareBusiness['business_id'],
                                'guildCode' => 'TRANSPORTATION_GUILD',
                                'billNumber' => $billNumber,
                                'description' => $description,
                                'invoiceItemVOs' => [
                                    [
                                        'productId' => $row['product_id'],
                                        'price' => $gatewayShareBusiness['amount'],
                                        'quantity' => '1',
                                        'description' => $description
                                    ]
                                ],
                            ]
                        ],
                        'customerInvoiceItemVOs' => [
                            [
                                'productId' => $row['product_id'],
                                'price' => (int)$gatewayShareBusiness['amount'],
                                'quantity' => '1',
                                'description' => $description
                            ]
                        ],
                    ])]
                ];
            } else {
                $issueMultiInvoice[$keyBusiness]['uri'] = '/nzh/biz/issueInvoice';
                $issueMultiInvoice[$keyBusiness]['parameters'] = [
                    'billNumber' => [$billNumber],
                    'description' => [$description],
                    'productId[]' => [$row['product_id']],
                    'quantity[]' => ['1'],
                    'productDescription[]' => [$description],
                    'price[]' => [$gatewayShareBusiness['amount']],
                    'guildCode' => ['TRANSPORTATION_GUILD'],
                    'preferredTaxRate' => [0]
                ];
            }
            $invoiceLog[$keyBusiness]['share'] = json_encode($issueMultiInvoice[$keyBusiness]);
        }
        sort($invoiceLog);
        try {
            $response = DB::table('invoice_logs')->insert($invoiceLog);
            return $response;
        } catch (\Exception $e) {
            return false;
        }
    }

    function blackGrayStatus($row)
    {
        $config = DB::table('configs')->first();
        $config = json_decode($config->messages);
        $states = [];
        $counter = 0;
        foreach ($config->messages as $message) {
            if ($message->name != 'gray') {
                $counter += $message->time_limit;
                $states[$message->name] = $counter;
            }
        }
        $checkPassedDate = Carbon::now()->format('Y-m-d');
        $status = '';
        $message = [
            'ocr_id' => $row->id,
            'license' => $row->license,
            'message' => json_encode([
                'license' => $row->license,
                'vehicle_class' => $row->vehicle_class,
                'event_id' => $row->event_id,
                'price' => $row->gateway_price,
                'traverse_time' => $row->ocr_orginal_traverse_time,
                'gateway' => $row->gateway
            ])
        ];
        if (is_null($row->status)) {
            $status = 'gray';
            $message['type'] = 'sepandar1';
        } elseif (in_array($row->status, ['gray', 'purple', 'pink', 'brown']) == true) {
            $flag = false;
            if (Carbon::createFromTimestamp(strtotime('+' . $states ['black'] . ' days', strtotime($row->ocr_traverse_time)))->format('Y-m-d') < $checkPassedDate) {
                $status = 'black';
                $message['type'] = 'sepandar5';
            } else {
                if (strpos($config->state, 'purple') !== false) {
                    if ($flag == false) {
                        if (Carbon::createFromTimestamp(strtotime('+' . $states ['purple'] . ' days', strtotime($row->ocr_traverse_time)))->format('Y-m-d') < $checkPassedDate) {
                            $status = 'purple';
                            $message['type'] = 'sepandar4';
                            $flag = true;

                        }
                    }
                }
                if (strpos($config->state, 'pink') !== false) {
                    if ($flag == false) {
                        if (Carbon::createFromTimestamp(strtotime('+' . $states ['pink'] . ' days', strtotime($row->ocr_traverse_time)))->format('Y-m-d') < $checkPassedDate) {
                            $status = 'pink';
                            $message['type'] = 'sepandar3';
                            $flag = true;
                        }
                    }
                }
                if (strpos($config->state, 'brown') !== false) {
                    if ($flag == false) {
                        if (Carbon::createFromTimestamp(strtotime('+' . $states ['brown'] . ' days', strtotime($row->ocr_traverse_time)))->format('Y-m-d') < $checkPassedDate) {
                            $status = 'brown';
                            $message['type'] = 'sepandar2';
                        }
                    }
                }
            }
        }
        if (!empty($status)) {
            if ($row->status != $status) {
                master::_()->ocrChangeStatus($row->id, $status);
            }
            if ($row->state !== 'yellow' && isset($message['type'])) {
                $chkSms = DB::table('sms_message_queues')
                    ->where(['ocr_id' => $row->id, 'type' => $message['type']])->first();
                if (empty($chkSms)) {
                    BaseModel::createSmsMessageQueues($message);
                }
            }
        }
    }

    function multiIssuers($rows, $row)
    {
        $f = false;
        if ($row->state == 'green') {
            foreach ($rows as $rowGreen) {
                if ($row->id == $rowGreen->id) {
                    if ($rowGreen->index_issuer == 1) {
                        $row->issuer_user_id = $rowGreen->issuer_user_id;
                        $row->issuer_id = $rowGreen->issuer_id;
                        $row->delegation = $rowGreen->delegation;
                        $row->state = $rowGreen->state;
                        $row->used = $rowGreen->used;
                        $f = true;
                        break;
                    }
                }
            }
        }
        if ($f == false) {
            if ($row->state == 'yellow') {
                foreach ($rows as $rowYellow) {
                    if ($rowYellow->id == $row->id) {
                        if ($rowYellow->state == 'yellow' and ($rowYellow->delegation >= ($rowYellow->gateway_price + $rowYellow->used))) {
                            $row->issuer_user_id = $rowYellow->issuer_user_id;
                            $row->issuer_id = $rowYellow->issuer_id;
                            $row->delegation = $rowYellow->delegation;
                            $row->state = $rowYellow->state;
                            $row->used = $rowYellow->used;
                            $f = true;
                            break;
                        }
                    }
                }
            }
        }
        if ($f == false) {
            if ($row->state == 'red') {
                foreach ($rows as $rowRed) {
                    if ($row->id == $rowRed->id) {
                        if ($rowRed->index_issuer == 1) {
                            $row->issuer_user_id = $rowRed->issuer_user_id;
                            $row->issuer_id = $rowRed->issuer_id;
                            $row->delegation = $rowRed->delegation;
                            $row->state = $rowRed->state;
                            $row->used = $rowRed->used;
                            $f = true;
                            break;
                        }
                    }
                }
            }
        }
        return $row;
    }

    function multiTollPrice($rows, $row)
    {
        $rows = $rows->sortByDesc('start_date');
        foreach ($rows as $rowPrice) {
            if ($row->id == $rowPrice->id) {
                if ($rowPrice->start_date <= $rowPrice->ocr_orginal_traverse_time) {
                    $row->start_date = $rowPrice->start_date;
                    $row->toll_price_id = $rowPrice->toll_price_id;
                    $row->product_id = $rowPrice->product_id;
                    $row->gateway_price = $rowPrice->gateway_price;
                    $row->gateway = $rowPrice->gateway;
                    $row->freeway_amount = $rowPrice->freeway_amount;
                    break;
                }
            }
        }
        return $row;
    }

    function increasePayBack($license, $amount)
    {
        $prefix = config('database.connections.oracle.prefix');
        $payBack = PayBack::where('license', $license)->first();
        if (empty($payBack)) {
            PayBack::create(['license' => $license, 'amount' => $amount]);
        } else {
            PayBack::where('license', $license)->update(['amount' => DB::raw($prefix . "pay_back.amount + {$amount}")]);
        }
    }

    public function updateInvoice($ocrId, $userId)
    {
        $podBusiness = PodBusiness::where('user_id', $userId)->first();
        if (!empty($podBusiness->api_token)) {
            $invoices = DB::table('invoice_logs')->where('ocr_id', $ocrId)->get();
            if ($invoices->isNotEmpty()) {
                foreach ($invoices as $in => $invoice) {
                    $checkJson = json_decode($invoice->share);
                    if (!empty($checkJson->parameters)) {
                        if (!empty($checkJson->parameters->data)) {
                            $invoice->share = preg_replace('/"description":\s*"[^"]+?([^\/"]+)/', '"description":"apiToken-' . $podBusiness->api_token . '"', str_replace('\\', '', $invoice->share));
                        } else {
                            $invoice->share = preg_replace('/"productDescription\[\]":\[.*?\]/', '"productDescription[]":"apiToken-' . $podBusiness->api_token . '"', str_replace('\\', '', $invoice->share));
                        }
                    }
                    try {
                        DB::table('invoice_logs')->where('ocr_id', $ocrId)->update([
                            'api_token' => $podBusiness->api_token,
                            'user_id' => $userId,
                            'share' => $invoice->share
                        ]);
                    } catch (\Exception $e) {
                        return false;
                    }
                }
            }
        }
    }

}
