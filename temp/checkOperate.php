<?php

namespace App\Console\Commands;

use Cms\Packages\call_check_operate\CallCheckOperate;
use Cms\Packages\traverse_ocr\TraverseOcr;
use Cms\Packages\yellow_ocr\YellowOcr;
use Fanap\Platform\Fanapium;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class checkOperate extends Command
{
    const LIMITED = 10000;
    const MASKAN_ISSUER = 90628;   //maskan bank issuer_id

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkOperate:start';

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

    function ocrChangeState($id, $status)
    {
        return TraverseOcr::where('id', $id)->update(['state' => $status]);

    }

    function addCheckOperate($data)
    {
        CallCheckOperate::_()->insertRow($data);
        $this->ocrChangeState($data['traverse_ocr_id'], 'check_operate');
    }

    public function handle()
    {
        $selects = [
            'traverse_ocrs.id AS ocr_id',
            'traverse_ocrs.license As ocr_license',
            'traverse_ocrs.accuracy_front_license AS ocr_accuracy_front_license',
            'traverse_ocrs.accuracy_back_license AS ocr_accuracy_back_license',
            'traverse_ocrs.accuracy_vehicle_class AS ocr_accuracy_vehicle_class',
            'traverse_ocrs.gateway_serial AS ocr_gateway_serial',
            'traverse_ocrs.tsp_serial AS ocr_tsp_serial',
            'traverse_ocrs.traverse_time AS ocr_traverse_time',
            'traverse_ocrs.created_at AS ocr_created_at',
            'traverse_ocrs.license_validate AS ocr_license_validate',
            'traverse_ocrs.license_text_image AS ocr_license_text_image',
            'traverse_ocrs.state',
            'traverse_ocrs.status',
            'traverser_states.state AS traverser_state',
            DB::raw('tsp_config_back.accuracy_license AS config_accuracy_license_back'),
            DB::raw('tsp_config_front.accuracy_license AS config_accuracy_license_front'),
            'gateway_vehicles.accuracy_vehicle_class AS gate_accuracy_vehicle_class',
        ];

        $rows = DB::table('traverse_ocrs')
            ->select($selects)
            ->leftJoin('tsp_configs as tsp_config_back', function ($join) {
                $join->on(DB::raw('tsp_config_back.gateway_serial'), '=', 'traverse_ocrs.gateway_serial');
                $join->on(DB::raw('tsp_config_back.tsp_serial'), '=', 'traverse_ocrs.tsp_serial');
                $join->on(DB::raw('tsp_config_back.camera_serial'), '=', 'traverse_ocrs.camera_serial_back');
            })
            ->leftJoin('tsp_configs as tsp_config_front', function ($join) {
                $join->on(DB::raw('tsp_config_front.gateway_serial'), '=', 'traverse_ocrs.gateway_serial');
                $join->on(DB::raw('tsp_config_front.tsp_serial'), '=', 'traverse_ocrs.tsp_serial');
                $join->on(DB::raw('tsp_config_front.camera_serial'), '=', 'traverse_ocrs.camera_serial_front');
            })
            ->join('traverser_licenses', 'traverse_ocrs.license', '=', 'traverser_licenses.license')
            ->leftJoin('traverser_states', function ($join) {
                $join->on('traverser_states.sso_id', '=', 'traverser_licenses.sso_id');
                //maskan bank issuer_id
                $join->where('traverser_states.issuer_id', self::MASKAN_ISSUER);
            })
            ->join('gateway_vehicles', function ($join) {
                $join->on('gateway_vehicles.gateway_serial', '=', 'traverse_ocrs.gateway_serial');
            })
            ->where('traverse_ocrs.state', '=', 'unknown')//init state for traverse_ocrs is  unknown
            ->limit(self::LIMITED)
            ->get();
        if ($rows->isNotEmpty()) {
            foreach ($rows as $row) {

                if ($row->ocr_license_text_image == 'B_2' && (!isset($row->ocr_accuracy_back_license) || $row->ocr_accuracy_back_license < 65)) {
                    $this->ocrChangeState($row->ocr_id, 'tsp_report');
                } else {

                    $accuracy_front_license =
                    $accuracy_back_license =
                    $accuracy_vehicle_class =
                    $avg_accuracy_license_back_front =
                    $traverse_time =
                    $license = 1;
                    $license_text_image = '';

                    if ($row->ocr_license_text_image == 'FB_1' || $row->ocr_license_text_image == 'FB_2') {
                        $multiple = $row->config_accuracy_license_front * $row->config_accuracy_license_back / 100;
                        if (!($multiple < $row->ocr_accuracy_front_license && $multiple < $row->ocr_accuracy_back_license)) {
                            $avg_accuracy_license_back_front = 1;
                        }
                    } else {
                        if (isset($row->ocr_accuracy_front_license) && !empty($row->ocr_accuracy_front_license)) {
                            if ($row->ocr_accuracy_front_license < $row->config_accuracy_license_front) {
                                $accuracy_front_license = 0;
                            }
                        }

                        if (isset($row->ocr_accuracy_back_license) && !empty($row->ocr_accuracy_back_license)) {
                            if ($row->ocr_accuracy_back_license < $row->config_accuracy_license_back) {
                                $accuracy_back_license = 0;
                            }
                        }
                    }


                    if ($row->ocr_accuracy_vehicle_class < $row->gate_accuracy_vehicle_class) {
                        $accuracy_vehicle_class = 0;
                    }


                    if ($row->ocr_traverse_time > $row->ocr_created_at) {
                        $traverse_time = 0;
                    }


                    if ($row->ocr_license_validate == 1) {
                        $license = 0;
                    }


                    if ($row->ocr_license_text_image == 'B_1') {
                        $license_text_image = 'B_1';

                    }

                    if ($row->ocr_license_text_image == 'FBI_1') {
                        $license_text_image = 'FBI_1';
                    }

                    if (
                        $license_text_image != ''
                    ) {
                        $this->addCheckOperate([
                            'traverse_ocr_id' => $row->ocr_id,
                            'tsp_serial' => $row->ocr_tsp_serial,
                            'gateway_serial' => $row->ocr_gateway_serial,
                            'accuracy_front_license' => $accuracy_front_license,
                            'accuracy_back_license' => $accuracy_back_license,
                            'accuracy_vehicle_class' => $accuracy_vehicle_class,
                            'avg_accuracy_license_back_front' => $avg_accuracy_license_back_front,
                            'traverse_time' => $traverse_time,
                            'license' => $license,
                            'license_text_image' => $license_text_image
                        ]);
                    } else {
                        try {
                            if ($row->status == '' || $row->status == 'unknown') {
                                if ($row->traverser_state == 'yellow') {
                                    YellowOcr::create([
                                        'ocr_id' => $row->ocr_id,
                                        'license' => $row->ocr_license,
                                        'status' => 'pending',
                                    ]);
                                    TraverseOcr::where('id', $row->ocr_id)->update(['state' => 'pending']);
                                }
                            }else{
                                $this->ocrChangeState($row->ocr_id, 'accept');
                            }
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollback();
                            echo 'exception => '.json_encode($e->getMessage()).' row => '.json_encode($row). "\n";
                            continue;
                        }
                    }
                }
            }
        }
    }

}
