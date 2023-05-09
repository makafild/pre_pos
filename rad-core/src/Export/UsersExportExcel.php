<?php

namespace Core\System\Export;

use Illuminate\Http\Request;
use Core\Packages\user\Users;
use Core\Packages\product\Product;
use App\ModelFilters\CustomerFilter;
use Hekmatinasser\Verta\Facades\Verta;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsersExportExcel implements FromCollection
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


        $cities = auth('api')->user()->Cities->pluck('id')->all();
        $customer = Users::where('users.kind', Users::KIND_CUSTOMER)
            ->select('users.*')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('visitors');
            })
            ->where('kind', 'customer')
            ->with([
                'referrals' => function ($query) {
                    if (auth('api')->user()->company_id)
                        return $query->where('company_id', auth('api')->user()->company_id);
                    else
                        return $query->where('company_id', 1)->where('company_id', '<>', 1);
                },
                'Provinces',
                'Cities',
                'Areas',
                'Routes',
                'UserRole',
                'Categories'

            ])->whereCities($cities);
        $customer->filter($this->request, CustomerFilter::class)->whereCities($cities)->orderBy('created_at', 'desc')->get();


        $results = array();
        $customers = $customer->get()->toArray();
        //set tiltle for excel
        $results[] = [
            "id" => "شناسه",
            "full_name" => "نام",
            "mobile_number" => "موبایل",
            "provinces[0].name" => "استان",
            "cities[0].name" => "شهر",
            "areas[0].area" => "نام منطقه",
            "routes[0].route" => "نام مسیر",
            "introducer_code.code" => "کدمعروف",
            "introduction_source.constant_fa" => "منبع ورودی",
            "description" => "توضیحات",
            "categories[0].constant" => "صنف",
            "customer_grade.constant_fa" => "رتبه مشتری",
            "customer_group.constant_fa" => "فعالیت تخصصی",
            "status_translate" => "وضعیت",
            "approval" => "تصویب",
            "roles_name" => "دسته بندی مشتری",
            "score" => "امتیاز",
            "created_at" => "تاریخ ایجاد"
        ];


        //add product to list
        foreach ($customers as $custom) {


            $provinces = array();
            if ($custom['provinces']) {
                foreach ($custom['provinces'] as $province) {
                    array_push($provinces, $province['name']);
                }
            }
            $citys = array();
            if ($custom['cities']) {
                foreach ($custom['cities'] as $city) {
                    array_push($citys, $city['name']);
                }
            }
            $areas = array();
            if ($custom['areas']) {
                foreach ($custom['areas'] as $area) {
                    array_push($areas, $area['area']);
                }
            }

            $users_routes = array();
            if ($custom['routes']) {
                foreach ($custom['routes'] as $user_route) {
                    array_push($users_routes, $user_route['route']);
                }
            }
            $users_Roles = array();
            if ($custom['user_role']) {
                foreach ($custom['user_role'] as $user_role) {
                    array_push($users_Roles, (isset($user_role['name'])) ? $user_role['name'] : '');
                }
            }
            $categories = array();
            if ($custom['user_role']) {
                foreach ($custom['categories'] as $categorie) {
                    array_push($categories, $categorie['constant']);
                }
            }


            $results[] = [
                "id" => $custom['id'],
                "full_name" => $custom['full_name'],
                "mobile_number" => $custom['mobile_number'],
                "provinces[0].name" => implode(',', $provinces),
                "cities[0].name" => implode(',', $citys),
                "areas[0].area" => implode(',', $areas),
                "routes[0].route" => implode(',', $users_routes),
                "introducer_code.code" => $custom['introducer_code_id'],
                "introduction_source.constant_fa" => $custom['introduction_source'],
                "description" => $custom['description'],
                "categories[0].constant" => implode(',', $categories),
                "customer_grade.constant_fa" => $custom['customer_grade'],
                "customer_group.constant_fa" => $custom['customer_group'],
                "status_translate" => $custom['status_translate'],
                "approval" => ($custom['approve'] == 1) ? "بله" : "خیر",
                "roles_name" => implode(',', $users_Roles),
                "score" => $custom['score'],
                "created_at" => Verta::instance($custom['created_at'])
            ];
        }

        $collection = collect();
        $collection->push($results);

        return $collection;


    }
}
