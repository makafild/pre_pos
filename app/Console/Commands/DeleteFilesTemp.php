<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteFilesTemp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DeleteFilesTemp:start';

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
     * @return int
     */
    public function handle()
    {

        $myfiles = array_diff(scandir(public_path() . "/upload/json/"), array('.', '..'));
        $dir = public_path() . "/upload/json/";
        foreach ($myfiles as $file) {
            unlink($dir . $file);
        }

        $filename = "backup-" .Carbon::now()->subDays(7)->format('Y-m-d') . ".gz";
        unlink(storage_path() . "/app/backup/". $filename);

    }
}
