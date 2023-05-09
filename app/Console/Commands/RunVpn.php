<?php

namespace App\Console\Commands;

use App\Events\User\SendSMSEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Core\Packages\user\Users;
use Core\Packages\user\Address;
use Core\Packages\customer\CompanyCustomer;
use Illuminate\Support\Facades\Log;
use Core\System\Helper\CrmSabz;

class RunVpn extends Command
{

    protected $signature = 'vpn:start';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $command1 = "openfortivpn -c /etc/openfortivpn/config >/dev/null 2>&1 < /dev/null &";
        $command2 = "chmod 777 -R /home/admin/domains/hanahco.com/public_html/dev-kheylisabz/storage/";
        $returnVar = NULL;
        $output = NULL;
        exec($command1, $output, $returnVar);
        exec($command2, $output, $returnVar);
    }
}
