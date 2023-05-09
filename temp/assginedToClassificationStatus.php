<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Cms\Packages\check_operate_classification\CheckOperateClassification;
use Cms\Packages\traverse_ocr\TraverseOcr;


/*
 * assginedOperatorPackagesTocheckOperateClassification
 */


class assginedToClassificationStatus extends Command
{
    const  ITOLL_SSO_ID = 6979385;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assginedToClassificationStatus:start {counter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'assginedToClassificationStatus:start';

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
        $rows = DB::table('check_operate_classifications')
            ->select([
                'check_operate_classifications.ocr_id',
                'assigned_operator_packages.status',
                'assigned_operator_packages.license'
            ])
            ->join("assigned_operator_packages", 'assigned_operator_packages.traverse_ocr_id', '=', 'check_operate_classifications.ocr_id')
            ->where('send', 1)
            ->where('check_operate_classifications.status', 'pending')
            ->where('assigned_operator_packages.status', '<>', 'unknown')
            ->limit($chunk)
            ->get();
        if (count($rows)) {
            DB::beginTransaction();
            try {
                foreach ($rows as $item) {
                    $traverseOcr = TraverseOcr::where('id', $item->ocr_id)
                        ->where('state', 'check_operate')
                        ->first();
                    CheckOperateClassification::where(['ocr_id' => $item->ocr_id])->update(['license' => $item->license]);
                    if ($item->status == 'accept') {
                        $validationLicense = CheckOperateClassification::_()->validationLicense($item->license);
                        if ($validationLicense == 'valid') {
                            $duplicateOcr = DB::table(DB::raw('V_DUPLICATE_TRAVERSE1'))
                                ->where('license', $item->license)
                                ->first();
                            if (!empty($duplicateOcr)) {
                                TraverseOcr::where('id', $item->ocr_id)->update(['state' => 'reject', 'status' => 'reject']);
                            } else {
                                if ($traverseOcr['status'] == 'white') {
                                    DB::table('invoice_logs')
                                        ->where('ocr_id', $item->ocr_id)->update(['deleted' => 1]);

                                    DB::table('self_declarations')
                                        ->where('ocr_id', $item->ocr_id)->update(['ocr_id' => null]);

                                    DB::table('traverser_delegation_uses')
                                        ->where('id', $item->ocr_id)->delete();

                                    DB::table('discount_package_uses')
                                        ->where('ocr_id', $item->ocr_id)->delete();
                                }
                                TraverseOcr::where('id', $item->ocr_id)->update(['state' => 'accept', 'status' => null, 'traverse_time' => Carbon::now()]);
                            }
                        }


                    }

                    if ($item->status == 'reject') {
                        if ($traverseOcr['status'] == 'white') {
                            DB::table('invoice_logs')
                                ->where('ocr_id', $item->ocr_id)->update(['deleted' => 1]);

                            DB::table('self_declarations')
                                ->where('ocr_id', $item->ocr_id)->delete();

                            DB::table('traverser_delegation_uses')
                                ->where('id', $item->ocr_id)->delete();

                            DB::table('discount_package_uses')
                                ->where('ocr_id', $item->ocr_id)->delete();
                        }
                        TraverseOcr::where('id', $item->ocr_id)->update(['state' => 'reject', 'status' => 'reject']);
                    }

                    if ($item->license != $traverseOcr['license']) {
                        CheckOperateClassification::where(['ocr_id' => $item->ocr_id])->update(['changed' => 1, 'license' => $item->license]);
                        if ($item->status == 'accept') {
                            TraverseOcr::where('id', $item->ocr_id)->update(['license' => $item->license]);
                        }
                    }
                    CheckOperateClassification::where(['ocr_id' => $item->ocr_id])->update(['status' => $item->status]);

                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
            }
        }
    }

}
