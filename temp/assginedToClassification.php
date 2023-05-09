<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/*
 * assginedOperatorPackagesTocheckOperateClassification
 */


class assginedToClassification extends Command
{
    const  ITOLL_SSO_ID = 6979385;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assginedToClassification:start {counter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'assginedToClassification:start';

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
        $dataCheckOperateClassifications = [];
        $prefix = config('database.connections.oracle.prefix');
        $chunk = !empty($this->argument('counter')) ? $this->argument('counter') : 1000;
        $rows = DB::table('assigned_operator_packages')
            ->leftJoin("check_operate_classifications","check_operate_classifications.ocr_id","=","assigned_operator_packages.traverse_ocr_id")
            ->whereRaw(" (( admin_check <> '1' and end_of_work= '1' and  {$prefix}assigned_operator_packages.status <> 'unknown') or (admin_check <> '1' and end_of_work <> '1' and {$prefix}assigned_operator_packages.status <> 'unknown') )")
            ->where('send', 0)
            ->whereNull('ocr_id')
            ->limit($chunk)
            ->pluck('traverse_ocr_id')->toArray();
        if (count($rows)) {
            DB::beginTransaction();
            try {
                foreach ($rows as $ocr_id) {
                    $dataCheckOperateClassifications[] = [
                        'sso_id' => self::ITOLL_SSO_ID,
                        'ocr_id' => $ocr_id,
                        'status' => 'pending',
                        'delivered' => 1,
                        'changed' => 0,
                        'license' => '',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                DB::table('assigned_operator_packages')
                    ->whereIn('traverse_ocr_id' , $rows)
                    ->update(['send' => 1]);
                DB::table('check_operate_classifications')->insert($dataCheckOperateClassifications);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
            }
        }
    }

}
