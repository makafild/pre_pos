<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/*
 * assginedOperatorPackagesTocheckOperateClassification
 */


class police extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'police:start {license}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'police:start';

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
        $license = $this->argument('license');
            return $this->convertPoliceLicense($license);
    }


    function convertPoliceLicense($licensePolice)
    {
        $numbers = [
            '01' => '01',
            '02' => '02',
            '03' => '21',
            '04' => '03',
            '05' => '22',
            '06' => '04',
//            '07' => '0',
//            '08' => '0',
//            '09' => '0',
            '10' => '05',
//            '11' => '0',
            '12' => '17',
            '13' => '24',
            '14' => '19',
            '15' => '06',
            '16' => '23',
            '17' => '07',
//            '18' => '0',
            '19' => '08',
//            '20' => '0',
            '21' => '09',
//            '22' => '0',
            '23' => '20',
            '24' => '10',
            '25' => '18',
//            '26' => '0',
            '27' => '11',
            '28' => '12',
            '29' => '13',
            '30' => '14',
            '31' => '15',
            '32' => '16',
            '54' => '26',
            '69' => '25'
        ];


        if (strlen($licensePolice) == 9) {
            $part1 = substr($licensePolice, 7, 2);
            $part2 = substr($licensePolice, 2, 2);
            $part3 = substr($licensePolice, 0, 2);
            $part4 = substr($licensePolice, 4, 3);
            if (isset($numbers[$part2])) {
                $part2 = $numbers[$part2];
            } else {
                return false;
            }
            $license = $part1 . $part2 . $part3 . $part4;
            echo $license;
        }
        return false;
    }
}
