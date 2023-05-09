<?php

namespace App\Console\Commands;

use App\BaseModel;
use Cms\Packages\gateway_share\GatewayShare;
use Cms\Packages\issuer\Issuer;
use Cms\Packages\pod_business\PodBusiness;
use Cms\Packages\traverse_ocr\TraverseOcr;
use Cms\Packages\traverser\TraverserDelegationUse;
use Cms\Packages\pay_back\PayBack;
use Cms\Packages\self_declaration\SelfDeclaration;
use Cms\Packages\gateway\Gateway;
use Fanap\Platform\Fanapium;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class selfDeclarationToPayBack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'selfDeclarationToPayBack:start {counter}';

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
        $chunk = !empty($this->argument('counter')) ? $this->argument('counter') : 1000;
        $prefix = config('database.connections.oracle.prefix');

        $selects = [
            'id',
            'license',
            'price',
            'traverse_time'
        ];

        $rows = SelfDeclaration::select($selects)
            ->where('pay_back', 0)
            ->whereNull('ocr_id')
            ->limit($chunk)
            ->get();

        if ($rows->isNotEmpty()) {
            foreach ($rows as $row) {
                if (Carbon::createFromTimestamp(strtotime('+ 10 days', strtotime($row['traverse_time'])))->format('Y-m-d 00:00:00') < Carbon::now()->format('Y-m-d 00:00:00')) {
                    $payBack = PayBack::where('license', $row['license'])->first();
                    if (empty($payBack)) {
                        PayBack::create(['license' => $row['license'], 'amount' => $row['price']]);
                    } else {
                        PayBack::where('license', $row['license'])->update(['amount' => DB::raw($prefix . "pay_back.amount + {$row['price']}")]);
                    }
                    SelfDeclaration::where('id', $row['id'])->update(['pay_back' => 1]);
                }
            }
        }
    }
}
