<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/*
 * assginedOperatorPackagesTocheckOperateClassification
 */


class text extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'text:start {license}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'text:start';

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
        return $this->convertLicenseToText($license);
    }

    function convertLicenseToText($licensePolice)
    {
        $letters = [
            '01' => 'الف',
            '02' => 'ب',
            '03' => 'پ',
            '04' => 'ت',
            '05' => 'ث',
            '06' => 'ج',
            '07' => 'چ',
            '08' => 'ح',
            '09' => 'خ',
            '10' => 'د',
            '11' => 'ذ',
            '12' => 'ر',
            '13' => 'ز',
            '14' => 'ژ',
            '15' => 'س',
            '16' => 'ش',
            '17' => 'ص',
            '18' => 'ض',
            '19' => 'ط',
            '20' => 'ظ',
            '21' => 'ع',
            '22' => 'غ',
            '23' => 'ف',
            '24' => 'ق',
            '25' => 'ک',
            '26' => 'گ',
            '27' => 'ل',
            '28' => 'م',
            '29' => 'ن',
            '30' => 'و',
            '31' => 'ه',
            '32' => 'ی',
            '54' => 'S',
            '69' => 'D'
        ];
        $part1 = substr($licensePolice, 0, 2);
        $part2 = substr($licensePolice, 2, 2);
        $part3 = substr($licensePolice, 4, 3);
        $part4 = substr($licensePolice, 7, 2);
        if (isset($letters[$part2])) {
            $part2 = $letters[$part2];
        } else {
            return false;
        }
        //$licenseText = $part4 . ' - ' . $part3 . ' ' . $part2 . ' ' . $part1;
        $licenseText = $part4 . '-' . $part3 . $part2 . $part1;
        echo $licenseText;
    }


}
