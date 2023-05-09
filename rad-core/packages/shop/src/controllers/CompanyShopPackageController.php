<?php

namespace core\Packages\shop\src\controllers;


use App\User;
use Illuminate\Http\Request;
use Core\Packages\shop\Users as userShop;
use EloquentFilter\Filterable;
use Hekmatinasser\Verta\Verta;
use Core\Packages\shop\Address;
use Core\Packages\shop\Contact;
use App\ModelFilters\UserFilter;
use Core\Packages\role\UserRoles;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\company\src\request\UpdateRequest;
use Core\Packages\company\src\request\ApproveStateRequest;
use Core\Packages\company\src\request\ChangeStatusRequest;
use Core\Packages\company\src\request\StoreCompanyRequest;
use core\Packages\company\src\request\ChangeCustomerStatusRequest;
// use Core\System\Exceptions\CoreExceptionOk;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */
class CompanyShopPackageController extends CoreController
{
    use Filterable;
    const INDEX_PAGES = 15;
    const PRODUCTS_PAGES = 15;
    const SUPERIOR_PAGES = 15;
    protected $simpleFilters = ['id', 'created_at'];

    protected $simpleSorts = ['id', 'email', 'created_at'];

    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {


        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }
        if ($request->has('limit')) {
            $limit = $request->get('limit');
        }
        $company = userShop::where('kind' , 'vendor')->with('Cities')->filter($request->all(), UserFilter::class)->orderBy($sort, $order);





        return $company->filter($request->all())->jsonPaginate($limit);
    }

    public function show($id)
    {

        $company = userShop::
            with([
                'Brands',
                'Group',
                'Countries',
                'Provinces',
                'Cities',
                'Addresses',
                'Contacts',
                'Photo',
                'background',
            ])->findOrFail($id);
        return $company->setAppends([
            'status_translate',
        ]);
//        if (auth()->user()->can('companyShow', $company)) {
//            return $company->setAppends([
//                'created_at_translate',
//                'status_translate',
//            ]);
//        } else {
//            abort(500);
//        }
    }

    public function store(StoreCompanyRequest $request)
    {

        // Store User
        $user = new userShop();

        $user->email = $request->email;
        $user->mobile_number = $request->mobile_number;
        $user->manager_mobile_number = $request->manager_mobile_number;
        $user->password = bcrypt($request->password);

        $user->name_fa = $request->name_fa;
        $user->name_en = $request->name_en;
        $user->economic_code = $request->economic_code;
        $user->api_url = $request->api_url;
        $user->gateway_token = $request->gateway_token;
        $user->lat = $request->lat;
        $user->long = $request->long;

        $user->kind = userShop::KIND_VENDOR;
        $user->end_at = $request->end_at;
        if($request->group_id)
        $user->group_id = $request->group_id;
        $user->background_id = $request->background_id;
        $user->photo_id = $request->photo_id;
        $user->show_users_list = isset($request->show_users_list)?$request->show_users_list:1;

        $user->Creator()->associate(auth('api')->user());

        $user->save();


        $user->company_id = $user->id;
        $user->save();

        $user->Countries()->sync(collect($request->countries)->all());
        $user->Provinces()->sync(collect($request->provinces)->all());
        $user->Cities()->sync(collect($request->cities)->all());

        $user->Brands()->sync(collect($request->brands)->all());


        // Store Addresses
        foreach ($request->addresses as $address) {

            $addressEntity = new Address();
            $addressEntity->address = $address['address'] ?? '';
            $addressEntity->postal_code = $address['postal_code'] ?? '';
            $addressEntity->city_id = $request->cities[0];
//			$addressEntity->lat = $address['lat'];
//			$addressEntity->long = $address['long'];

            $addressEntity->User()->associate($user);

            $addressEntity->save();
        }

        // Store Contacts
        foreach ($request->contacts as $contact) {
            $contactEntity = new Contact();
            $contactEntity->kind = $contact['kind'] ?? '';
            $contactEntity->value = $contact['value'] ?? '';

            $contactEntity->User()->associate($user);

            $contactEntity->save();
        }
$roleId='';
        if (!empty($request->role_id)) {
            $roleId =$request->role_id;

        }

        if (!empty($roleId)) {

            UserRoles::create(['user_id' => $user->id, 'role_id' => $roleId]);
        }

        return [
            'status' => true,
            'message' => trans('messages.company.company.store'),
            'id' => $user->id,
        ];
    }

    public function update(UpdateRequest $request, $id)
    {

        $user = userShop::findOrFail($id);

        $user->email = $request->email;
        $user->mobile_number = $request->mobile_number;
        $user->manager_mobile_number = $request->manager_mobile_number;
        if ($request->password)
            $user->password = bcrypt($request->password);

        $user->name_fa = $request->name_fa;
        $user->name_en = $request->name_en;
        $user->economic_code = $request->economic_code;
        $user->api_url = $request->api_url;

        $user->gateway_token = $request->gateway_token;
        $user->lat = $request->lat;
        $user->long = $request->long;

        $user->kind = userShop::KIND_VENDOR;
        $user->end_at = Verta::parse($request->end_at)->DateTime();
        $user->group_id = $request->group_id;
        $user->photo_id = $request->photo_id;
        $user->background_id = $request->background_id;
        $user->show_users_list = isset($request->show_users_list)?$request->show_users_list:1;

        $user->save();


        // Save location
        $user->Countries()->sync(collect($request->countries)->pluck('id')->all());
        $user->Provinces()->sync(collect($request->provinces)->pluck('id')->all());
        $user->Cities()->sync(collect($request->cities)->pluck('id')->all());

        $user->Brands()->sync(collect($request->brands)->pluck('id')->all());


        // Remove One to Many Question
        $remainId = collect($request->addresses)->pluck('id');
        $diffId = $user->addresses->pluck('id')
            ->diff($remainId)
            ->toArray();
        Address::whereIn('id', $diffId)->delete();

        // Store Addresses
        foreach ($request->addresses as $address) {
            if (isset($address['id']) && $address['id'])
                $addressEntity = Address::find($address['id']);
            else
                $addressEntity = new Address();

            $addressEntity->address = $address['address'] ?? '';
            $addressEntity->postal_code = $address['postal_code'] ?? '';
//			$addressEntity->lat = $address['lat'];
//			$addressEntity->long = $address['long'];

            $addressEntity->User()->associate($user);

            $addressEntity->save();
        }


        // Remove One to Many Question
        $remainId = collect($request->contacts)->pluck('id');
        $diffId = $user->contacts->pluck('id')
            ->diff($remainId)
            ->toArray();
        Contact::whereIn('id', $diffId)->delete();

        // Store Contacts
        foreach ($request->contacts as $contact) {
            if (isset($contact['id']) && $contact['id'])
                $contactEntity = Contact::find($contact['id']);
            else
                $contactEntity = new Contact();

            $contactEntity->kind = $contact['kind'] ?? '';
            $contactEntity->value = $contact['value'] ?? '';

            $contactEntity->User()->associate($user);

            $contactEntity->save();
        }

	$roleId='';
        if (!empty($request->role_id)) {
            $roleId =$request->role_id;

        }


        UserRoles::where('user_id', $id)->delete();
        if (!empty($roleId)) {
            UserRoles::create(['user_id' => $id, 'role_id' => $roleId]);
        }

        return [
            'status' => true,
            'message' => trans('messages.company.company.update'),
            'id' => $user->id,
        ];
    }

    public function states()
    {
        return response()->json(['status' => userShop::_()::STATUS]);
    }

    public function destroy(Request $request , userShop $user)
    {


        $user->whereIn('id',$request->id)->delete();
        // throw new CoreExceptionOk();
        return response(
            [ 'status' => true,
            'message' =>'با موفقیت پاک شد' ,
            ]
        );
        // $ids = $request->id;
        // /** @var User[] $companiesEntity */
        // $companiesEntity = Users::Company()
        //     ->whereIn('id', $ids)
        //     ->get()
        //     ->keyBy('id');

//        foreach ($ids as $id) {
//            $company = $companiesEntity[$id];
//
//            if (auth()->user()->can('companyDestroy', $company)) {
//
//            } else {
//                abort(500);
//            }
//        }

        // Users::Company()
        //     ->whereIn('id', $ids)
        //     ->delete();

        // return [
        //     'status' => true,
        //     'message' => trans('messages.company.company.destroy'),
        // ];


    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        foreach ($request->id as $id) {
            /** @var Users $company */
            $company = userShop::Company()->findOrFail($id);
//            if ($company->isInactive()) {
//                foreach ($company->tokens as $token) {
//                    $token->revoke();
//                }
//                $company->CompanyToken->delete();
//            }

            $company->status = $request->status;
            $company->save();
        }


        return [
            'status' => true,
            'message' => trans('messages.company.company.changeStatus'),
        ];
    }

    public function list()
    {
        $companies = userShop::Company()->whereRaw('id = company_id')
            ->get();


        return $companies;
    }

    public function city()
    {
        if (empty(request()->has('brand_id'))) {
            return [
                'status' => false,
                'message' => "کد برند را وارد نمایید"
            ];
        }

        $brandId = request()->get('brand_id');

        $userInfo = userShop::with(['Cities'])->where('id', auth('api')->id())->first();
        $userCities = $userInfo['cities']->pluck('id');

        $userInfo = userShop::
        with('Cities')->
        with('Brands')->
        where('kind', 'company')->
        whereHas('Cities', function ($query) use ($userCities) {
            $query->whereIn('id', $userCities);
        })->
        whereHas('Brands', function ($query) use ($brandId) {
            $query->where('id', $brandId);
        })->
        jsonPaginate();

        return $userInfo;
    }

    public function approveStates(ApproveStateRequest $request)
    {
        foreach ($request->user_ids as $id) {
            $user = userShop::findOrFail($id);
            $user->approve = $request->status;
            $user->save();
        }
        return [
            'status' => true,
            'message' => trans('messages.customer.customer.changeStatus'),
        ];
    }

    public function changeStates(Request $request)
    {
        foreach ($request->id as $id) {
            $user = userShop::findOrFail($id);
            $user->status = $request->value;
            $user->save();
        }
        return [
            'status' => true,
            'message' => trans('messages.company.company.changeStatus'),
        ];
    }

}
