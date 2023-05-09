<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Core\Packages\user\Users;

class crmUpdateUsers extends Command
{

    protected $signature = 'crmUpdateUsers:start';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $rows = master::_()->post([]);
        if ($rows->isSucceed) {
            foreach ($rows->content as $row) {
                dd('dd', $row);
            }
        }
    }
}
