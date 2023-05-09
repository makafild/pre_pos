<?php

namespace Core\Packages\news\src\controllers;


use App\ModelFilters\ConstantFilter;
use App\ModelFilters\NewsFilter;
use Core\Packages\common\Constant;
use Core\Packages\news\src\request\DestroyRequest;
use Core\Packages\news\src\request\StoreRequest;
use Core\Packages\news\src\request\UpdateRequest;
use Core\Packages\news\News;
use Core\System\Http\Controllers\CoreController;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;

/**
 * Class UserPackageController
 *
 * @package Core\Packages\user\src\controllers
 */


class NewsPackageController extends CoreController
{
    private $_fillable = [
        'constant_en',
        'constant_fa',
        'kind',
    ];
    public function index(Request $request, $sort = "id", $order = "desc", $limit = 10)
    {
        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            $sort = array_key_first($sort_arr);
            $order = $sort_arr[$sort];
        }

        $companyId = auth('api')->user()->company_id;


        $news = News::select('news.*');
        if($companyId){
            $news=$news->where('news.company_id', auth('api')->user()->company_id);
        }
        $news = $news->with([
            'company',
        ])->filter($request->all(), NewsFilter::class)->orderBy($sort,$order)->jsonPaginate($limit);
        return $news;
    }

    public function show($id)
    {
        $result = News::_()->list($id);
        return $this->responseHandler($result);
    }

    public function store(StoreRequest $request)
    {
        $news = new News();

        $news->title = $request->title;
        $news->description = $request->description;
        $news->video_url = $request->video_url;
        $news->start_at = Verta::parse($request->start_at)->DateTime();
        $news->end_at = Verta::parse($request->end_at)->DateTime();


        $news->photo_id = $request->photo_id;

        $user = auth("api")->user();
        $news->Creator()->associate($user);

        if ($user->company_id) {
            $news->company_id = $user->company_id;
        }

        $news->save();

        return [
            'status'  => true,
            'message' => trans('messages.common.news.store'),
            'id'      => $news->id,
        ];
    }

    public function destroy(DestroyRequest $request){
    //  News::_()->destroyRecord( $request->id);
     $news= News::whereIn('id',$request->id);
      if ($this->ISCompany()) {
         $news->where('company_id',$this->ISCompany());
    }
    $news->delete();
        return [
            'status'  => true,
            'message' => trans('messages.common.news.destroy'),
        ];
    }

    public function update(UpdateRequest $request, $id){
        $news = News::findOrFail($id);

        $news->title = $request->title;
        $news->description = $request->description;
        $news->description = $request->description;
        $news->video_url = $request->video_url;
        $news->start_at = Verta::parse($request->start_at)->DateTime();
        $news->end_at = Verta::parse($request->end_at)->DateTime();
        $news->photo_id = $request->photo_id;
        $news->save();

        return [
            'status'  => true,
            'message' => trans('messages.common.news.update'),
            'id'      => $news->id,
        ];
    }

    public function states(){
        $data = [];
        foreach (Constant::_()::CONSTANT_KINDS as $CONSTANT_KIND) {
            $data[] = [
                'name'  => $CONSTANT_KIND,
                'title' => trans("translate.setting.constant.$CONSTANT_KIND"),
            ];
        };

        return response()->json(['kinds'=>$data]);
    }
    private function ISCompany()
    {
        if (auth('api')->user()['kind'] == 'admin')
            return 0;
        else
            return auth('api')->user()->company_id;
    }

}
