<?php

namespace Core\System\Export;

use Illuminate\Http\Request;
use Core\Packages\product\Product;
use App\ModelFilters\ProductFilter;
use Hekmatinasser\Verta\Facades\Verta;
use Maatwebsite\Excel\Concerns\FromCollection;

class KalaExportExcel implements FromCollection
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

        //fech data from db with releations
        $products = Product::select('products.*')
            ->with([
                'company',
                'brand',
                'category',
                'photos',
                'Type1',
                'Type2'
            ]);

        if (auth('api')->user()->kind == 'company') {
            $product = $products->where('products.company_id', auth('api')->id());
        }
        $products = $products->filter($this->request, ProductFilter::class)->orderBy('created_at', 'desc');


        $results = array();
        $products = $products->get()->toArray();
        //set tiltle for excel
        $results[] = [
            "id" => "شناسه",
            "referral_id" => "کدمرجع",
            "name_fa" => "نام فارسی",
            "name_en" => "نام انگلیسی",
            "company_name" => "نام شرکت",
            "brand_name" => "برند",
            "creator" => "پدیدآورنده",
            "number_of_page" => "تعداد صفحه",
            "type_1.0" => "مهارت",
            "type_2.constant_fa" => "ژانر",
            "category_title" => "گروه کالا",
            "markup_price" => "قیمت خرید",
            "price" => "قیمت فروش",
            "consumer_price" => "قیمت مشتری",
            "per_master" => "تعداد کل",
            "per_slave" => "تعداد جزء",
            "score" => "امتیاز",
            "status" => "وضعیت",
            "show_status_translate" => "وضعیت نمایش",
            "updated_at" => "تاریخ بروز رسانی",
            "created_at" => "تاریخ ایجاد"
        ];


        //add product to list
        foreach ($products as $product) {

            //fech filed constant_fa;
            $type_1 = array();
            if ($product['type1']) {
                foreach ($product['type1'] as $type) {
                    array_push($type_1, $type['constant_fa']);
                }
            }
            $results[] = [
                "id" => $product['id'],
                "referral_id" => $product['referral_id'],
                "name_fa" => $product['name_fa'],
                "name_en" => $product['name_en'],
                "company_name" => (isset($product['company']['title'])) ? $product['company']['title'] : 'پیدا نشد',
                "brand_name" => (isset($product['brand']['name_fa'])) ? $product['brand']['name_fa'] : 'پیدا نشد',
                "creator" => $product['creator'],
                "number_of_page" => $product['number_of_page'],
                "type_1.0" => implode(',', $type_1),
                "type_2.constant_fa" => $product['type2'],
                "category_title" => (isset($product['category']['title'])) ? $product['category']['title'] : 'پیدا نشد',
                "markup_price" => $product['markup_price'],
                "price" => $product['price'],
                "consumer_price" => $product['consumer_price'],
                "per_master" => $product['per_master'],
                "per_slave" => $product['per_slave'],
                "score" => $product['score'],
                "status" => $product['status_translate'],
                "show_status_translate" => $product['show_status_translate'],
                "updated_at" => Verta::instance($product['updated_at']),
                "created_at" => Verta::instance($product['created_at'])
            ];
        }


        $collection = collect();
        $collection->push($results);

        return $collection;


    }
}
