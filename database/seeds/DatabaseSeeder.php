<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(VehicleTableSeeder::class);
/*
        $this->call(GatewayTableSeeder::class);
        $this->call(PodBusinessTableSeeder::class);
        $this->call(TspConfigTableSeeder::class);
        $this->call(GatewayVehicleTableSeeder::class);
        $this->call(TollPriceTableSeeder::class);
*/
    }
}
