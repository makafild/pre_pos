<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Cms\Packages\check_operate_sharing\CheckOperateSharing;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class checkOperateClassification extends Command
{
    const DATE = '2019-07-22';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkOperateClassification:start {counter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'checkOperateClassification:start';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $chunk = !empty($this->argument('counter')) ? $this->argument('counter') : 1000;
        $division = 0;
        $providers = CheckOperateSharing::where('percent', '<>', 0)->get()->toArray();
        $traverseOcrs = [];
        $query = DB::table('traverse_ocrs')
            ->leftJoin('check_operate_classifications', 'check_operate_classifications.ocr_id', '=', 'traverse_ocrs.id')
            ->whereNull('check_operate_classifications.ocr_id')
            ->where('traverse_ocrs.state', 'check_operate')
            ->whereDate('traverse_time', '>=', self::DATE);

        $countOcrs = $query
            ->count();

        if ($countOcrs <= $chunk) {
            $chunk = $countOcrs;
        }

        foreach ($providers as $xx => $provider) {
            $limit = ceil(($provider['percent'] * $chunk) / 100);
            $rows =$query
                ->offset($division)
                ->limit($limit)
                ->pluck('traverse_ocrs.id')
                ->toArray();
            $traverseOcrs[$provider['sso_id']] = $rows;
            $division += $limit;
        }
        if (!empty($traverseOcrs)) {
            DB::beginTransaction();
            try {
                $data = [];
                foreach ($traverseOcrs as $bussiness_id => $ocrs) {
                    if (!empty($ocrs)) {
                        foreach ($ocrs as $ocr) {
                            $data[] = [
                                'sso_id' => $bussiness_id,
                                'ocr_id' => $ocr,
                                'status' => 'pending',
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ];
                        }
                    }
                }
                DB::table('check_operate_classifications')->insert($data);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
            }
        }
    }
}
