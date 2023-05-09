<?php

namespace Core\System\Export;

use Illuminate\Http\Request;
use Core\Packages\order\Order;
use Core\Packages\product\Product;
use App\ModelFilters\ProductFilter;
use Core\Packages\gis\Routes;
use Core\Packages\user\Users;
use Hekmatinasser\Verta\Verta;
use Maatwebsite\Excel\Concerns\FromCollection;

class Type3ExportExcel implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $request;

    public function __construct(Request $request)
    {

        $this->request = $request->all();
    }

    public function collection()
    {

        ini_set('memory_limit', '512M');

        $company_ids = array();
        if (auth('api')->user()->kind == 'company') {
            $company_ids = [auth('api')->id()];
        } else {
            $company_ids = Users::where('kind', 'company')->get()->pluck('id');
        }
        $filter_area = [2];
        $routes = Routes::with(['area', 'area.city', 'area.province'])
            ->get();

        //set tiltle for excel
        $results[] = [
            "00" => 'ایدی شرکت',
            "01" => 'نام شرکت',
            "02" => 'نام کالا',
            "07" => 'مجموع تعداد فاکتور',
            "08" => 'تعداد مبنای فروش رفته',
            "09" => 'تعداد جزئه فروش رفته',
            "010" => 'تعداد جزئه 2 فروش رفته',
            "011" => 'تعداد کل جزئه 2 فروش رفته',
            "012" => 'تعداد واحد فروش رفته',
            "013" => 'مبلغ خالص فروش',
        ];

        foreach ($routes as $route) {
            $name=$route->route ."/";
            $name.=$route->area->area."/";
            $name.=$route->area->city->name."/";
            $name.=$route->area->province->name."";
          $results[0][$route->id]=$name;
        }
        dd($results);
        foreach ($company_ids as $company_id) {
            $company = Users::with([
                'Provinces',
                'Areas',
                'Routes',
                'Areas',
                'Cities'

            ])->where('id', $company_id)->first();
            $products = Product::where('company_id', $company_id)->get();
            dd($company->toArray());

            foreach ($products as $product) {

                $results[] = [
                    "0" => $company_id,
                    "1" => $company->name_fa,
                    "2" => $product->name_fa,
                    "3" => $company->name_fa,
                    "4" => $company->name_fa,
                    "5" => $company->name_fa,
                    "6" => $company->name_fa,
                    "7" => $company->name_fa,
                    "8" => $company->name_fa,
                    "9" => $company->name_fa,
                    "10" => $company->name_fa,
                    "11" => $company->name_fa,
                    "12" => $company->name_fa,
                    "13" => $company->name_fa
                ];
            }
        }
        $collection = collect();
        $collection->push($results);

        return $collection;
    }
}
