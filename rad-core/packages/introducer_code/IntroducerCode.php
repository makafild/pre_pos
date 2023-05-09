<?php

namespace Core\Packages\Introducer_code;

use App\Models\User\User;
use Core\System\Exceptions\CoreException;
use App\BaseModel;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;


use Core\System\Http\Traits\HelperTrait;


use Core\System\Http\Traits\SecureDelete;
use eloquentFilter\Filterable;

class IntroducerCode extends BaseModel
{
    use SecureDelete;
    use HelperTrait;
    use Filterable;

    public $timestamps = true;
    private static $_instance = null;

    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new IntroducerCode();
        }
        return self::$_instance;
    }

    protected $fillable = [
        'code',
        'title',
        'company_id',
        'status'
    ];

    public function list()
    {
        $companyId = auth('api')->user()->company_id;
        $result = $query = $this;
        if ($companyId) {
            $result = $query->where('company_id', $companyId);
        }
       $result= $result->with('company')->orderBy('created_at', 'desc')->get();


        return $this->modelResponse(['data' => $result, 'count' => $result->count()]);
    }


    public function show($id)
    {
        $query = $this;
        $result = $query->where('id', $id);
        return $this->modelResponse(['data' => $result->first(), 'count' => 1]);
    }

    public function store($payload)
    {
        $findCode = $this->where('code', $payload['code'])->first();
        if (!empty($findCode)) {
            throw new CoreException('کد مورد نظر تکراری می باشد');
        }

        try {
            IntroducerCode::create([
                'code' => $payload['code'],
                'title' => $payload['title'],
                'company_id' =>$payload['company_id'],
                'status' => 'active'
            ]);

            return (object)[
                'message' => 'با موفقیت ثبت شد'
            ];
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    public function updateR($payload, $id)
    {
        $findCode = $this->find($id);
        if (empty($findCode)) {
            throw new CoreException('شناسه مورد نظر وجود ندارد');
        }

        try {
            IntroducerCode::where('id', $id)->update([
                'code' => $payload['code'],
                'title' => $payload['title'],
                'company_id' =>$payload['company_id']
            ]);
            return (object)[
                'message' => 'با موفقیت بروز رسانی شد'
            ];
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }

    public function deleteR($payload)
    {
        $this->whereIn('id', $payload['ids'])->delete();
        return (object)[
            'message' => 'با موفقیت حذف شد'
        ];

    }

    public function Company()
    {
        return $this->belongsTo(User::class,'company_id');
    }

    public function Customer()
    {
        return $this->hasMany(User::class,'introducer_code_id','code');
    }

}
