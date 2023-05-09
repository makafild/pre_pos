<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Cms\Packages\traverse_ocr\TraverseOcr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class unassignedTag extends Command
{

    const LIMITED = 100 ;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unassignedTag:start';

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
        $rows = DB::table('traverse_ocrs')
            ->select('traverse_ocrs.tag_serial', 'license_tags.issuer_id', 'traverse_ocrs.license')
            ->join('license_tags', function ($join) {
                $join->on('license_tags.license', '=', 'traverse_ocrs.license');
                $join->on('license_tags.tag_serial', '!=', 'traverse_ocrs.tag_serial');
            })
            ->limit(self::LIMITED)
            ->get();
        if ($rows->isNotEmpty()) {
            foreach ($rows as $row) {
                try {
                    DB::table('unassigned_tags')->insert([
                        'tag_serial' => $row->tag_serial,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'issuer_id' => $row->issuer_id,
                        'license' => $row->license,
                    ]);
                } catch (\Exception $e) {

                }
            }
        }
    }
}
