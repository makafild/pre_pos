<?php

namespace App\Imports;

use App\Models\Setting\Constant;
use Core\Packages\user\Users;
use Core\Packages\user\Address;
use PhpParser\Node\Stmt\Foreach_;
use Core\Packages\product\Product;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;

class UsersImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        if ($row[5] == "فعال") {
            $product = Product::with('barcodes')->findOrFail($row[0]);


            if ($row[1]) {
                $unit = Constant::where('kind', 'unit')->where('constant_fa', $row[1])->first();
                if (isset($unit->id)) {
                    $product->master_status = 1;
                    $product->master_unit_id = $unit->id;
                }
            } else {
                $product->master_status = 0;
            }
            //************************************************** */
            if ($row[2]) {
                $unit = Constant::where('kind', 'unit')->where('constant_fa', $row[2])->first();
                if (isset($unit->id)) {
                    $product->slave_status = 1;
                    $product->slave_unit_id = $unit->id;
                }
            } else {
                $product->slave_status = 0;
            }
            //**************************************************************** */
            if ($row[4]) {
                $unit = Constant::where('kind', 'unit')->where('constant_fa', $row[4])->first();
                if (isset($unit->id)) {
                    $product->slave2_status = 1;
                    $product->slave2_unit_id = $unit->id;
                }
            } else {
                $product->slave2_status = 0;
            }
            //********************************************* */
            if ($row[3]) {

                $product->per_master = $row[3];
            }

            $product->save();


        }




        /* Log::info($row[0]);
        $product = Product::with('barcodes')->findOrFail($row[0]);
        $product->brand_id = 69;
        $product->save();


        /* if (isset($row[0])) {
            $customer = Users::customer()->where('referral_id', $row[0])->first();
            if (isset($customer)) {
                Address::where('user_id', $customer->id)
                    ->update(['address' => $row[1]]);
                /*$addressEntity = Address::where('user_id', $customer->id)->first();
                if ($customer->id == 13171)
                    dd($addressEntity);

                /*  $addressEntity->address = $row[1];
            $addressEntity->save();*
            } else {
                Log::info('customer not found ' . $row[0]);
            }
        }*/
    }
}
