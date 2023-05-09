<?php

namespace App\Http\Controllers\api\Customer\v1\Company;

use App\Models\User\Role;
use App\Models\User\User;
use App\Models\User\Score;
use App\Traits\CheckAccess;
use Illuminate\Http\Request;
use App\Models\Product\Brand;
use App\Models\Common\NewsSeen;
use App\Models\Product\Product;
use App\Models\Product\Category;
use App\Models\Common\SurveySeen;
use function GuzzleHttp\Psr7\str;
use App\Models\Common\MessageList;
use App\Http\Controllers\Controller;
use Core\Packages\customer\Loglogin;
use App\Http\Requests\api\Customer\v1\Company\ScoreCompanyRequest;

class CompanyController extends Controller
{

    const INDEX_PAGES = 15;

    const PRODUCTS_PAGES = 15;

    const SUPERIOR_PAGES = 15;
    use CheckAccess;

    /**
     * @return mixed
     */
    public function index()
    {
        $cities = auth('mobile')->user()->Cities->pluck('id')->all();

        /** @var User $company */
        $company = User::MyCompany()
            ->with([
                'photo',
            ]);

        if (request('s')) {
            $search = request('s');
            $company->SearchName($search);
        }
        Loglogin::updateOrCreate(
            ["user_id" => auth('mobile')->user()->id],
            [
                "user_id" => auth('mobile')->user()->id,
                "created_at" => now()
            ]
        );
        return $company->paginate(self::INDEX_PAGES);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        if (!auth('mobile')->user()) {
          return  $company = User::Company()->with(['brands', 'photo', "contacts"])->whereHas('Cities' , function($query) use($id){
                return $query->where('user_id' , $id);
            })->findOrFail($id);
        }else{
             Loglogin::updateOrCreate(
            ["user_id" => auth('mobile')->user()->id],
            [
                "user_id" => auth('mobile')->user()->id,
                "created_at" => now()
            ]
        );
        /** @var User $company */
        $company = User::Company()->with(['brands', 'photo', 'cities', "contacts"])->findOrFail($id);
        $cities = auth('mobile')->user()->Cities->pluck('id')->all();
        if (!$company->cities->whereIn('id', $cities)->first()) {
            return [
                'status' => false,
                'message' => 'این شرکت برای شهر شما تعریف نشده است.'
            ];
        }

        $messageList = MessageList::ToMe()
            ->From($company->id)
            ->whereNull('seen_at')
            ->first();
        $company['unread_message'] = (string)($messageList ? 1 : 0);
        $company['unread_survey'] = (string)SurveySeen::Check(auth()->id(), $company->id);
        $company['unread_news'] = (string)NewsSeen::Check(auth()->id(), $company->id);

        return $company;
    }

    }

    /**
     * @return array
     */
    public function products($id)
    {
        User::Company()->findOrFail($id);

        $products = Product::CompanyId($id)
            ->with([
                'brand',
                'category',
                'photo',
                'company',
                'labels',

                'MasterUnit',
                'SlaveUnit',
                'Slave2Unit',

                'promotions',

                'PriceClasses.Customers' => function ($query) {
                    $query->where('id', auth()->id());
                },
            ]);

        if (request('s')) {
            $search = request('s');

            $products->where(function ($query) use ($search) {
                $query->searchName($search);
                $query->orWhereHas('brand', function ($query) use ($search) {
                    $query->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_fa', 'like', "%{$search}%");
                });
            });
        }

        if (request('order')) {
            $products->Order(request('order'));
        }

        return $products->paginate(self::PRODUCTS_PAGES);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function tree($id)
    {
        $categories = Product::CompanyId($id)
            ->select('category_id')
            ->groupBy('category_id')
            ->get();

        // TODO. Category with parent
        $nodes = Category::/*whereIn('id', $categories)
			->*/get()
            ->toTree();

        return $nodes;
    }

    /**
     * @param ScoreCompanyRequest $request
     * @param                     $id
     * @return array
     */
    public function score(ScoreCompanyRequest $request, $id)
    {
        /** @var User $company */
        $company = User::Company()->findOrFail($id);

        if (!$this->chAc($id)) {
            return [
                'status'  => false,
                'message' => 'شما به این صفحه دسترسی ندارید.',
                'score'   => $company->score,
            ];
        }



        /** @var Score $score */
        $score = Score::firstOrNew([
            'user_id'      => auth()->id(),
            'recipient_id' => $id,
        ]);
        $score->score = $request->score;
        $score->save();

        $avgScore = Score::where('recipient_id', '=', $company->id)->avg('score');

        $company->score = $avgScore;
        $company->save();

        return [
            'status'  => true,
            'message' => trans('messages.api.customer.company.score', [
                'score' => $score->score,
                'name'  => $company->name_fa,
            ]),
            'score'   => $avgScore,
        ];
    }

    /**
     * @param                     $id
     * @return array
     */
    public function getScore($id)
    {

        /** @var User $user */
        $user = User::Company()->findOrFail($id);

        /** @var Score $score */
        $score = Score::where([
            'user_id'      => auth()->id(),
            'recipient_id' => $user->id,
        ])->first();

        if ($score) {
            return [
                'score' => $score->score,
            ];
        }

        return [
            'score' => 0,
        ];
    }

    public function superior(Request $request )
    {
        if(auth('mobile')->user() == null){
            $city = '8';
            if($request->city_id){
                $city = $request->city_id ;
            }
            return $companies = User::with('photo' , 'ContactsPhone')->whereHas('Cities' , function($query) use($city){
               return $query->where('city_id' , $city);
            })->orderBy('score' , 'desc')->paginate(self::SUPERIOR_PAGES);

        }else{
             $companies = User::MyCompany()
            ->with([
                'photo',
                'ContactsPhone',
            ])
            ->orderBy('score', 'desc')
            ->paginate(self::SUPERIOR_PAGES);
        return $companies;
        }



    }

    public function city()
    {
        if (!request()->has('brand_id')) {
            return [
                'status' => false,
                'message' => "کد برند را وارد نمایید"
            ];
        }

        $brandId = request()->get('brand_id');

        $userInfo = User::with(['Cities'])->where('id', auth('api')->id())->first();
        $userCities = $userInfo['cities']->pluck('id');
        $userInfo = User::where('kind', 'company')->whereHas('Cities', function ($query) use ($userCities) {
                $query->whereIn('id', $userCities);
            })->whereHas('Brands', function ($query) use ($brandId) {
                $query->where('id', $brandId);
            })->jsonPaginate();

        return $userInfo;
    }
}
