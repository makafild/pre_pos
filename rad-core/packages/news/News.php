<?php

namespace Core\Packages\news;

use App\Models\User\User;
use App\Traits\VersionObserve;
use Carbon\Carbon;
use Core\Packages\common\File;
use Core\Packages\user\Users;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Traits\HelperTrait;
use EloquentFilter\Filterable;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class News
 *
 * @package App\Models\Common
 * @property int    $id
 * @property string $title
 * @property string $description
 * @property string $video_url
 *
 * @property int    $photo_id
 * @property File   $photo
 *
 * @property int    $company_id
 * @property User   $company
 *
 * @property int    $creator_id
 * @property User   $creator
 *
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static News CompanyId(integer $company_id)
 * @method static Slider Active()
 */
class News extends Model
{
	use  SoftDeletes;
    use Filterable;
    use HelperTrait;
	const STATUS_ACTIVE = 'active';
	const STATUS_INACTIVE = 'inactive';

	const STATUS = [
		self::STATUS_ACTIVE,
		self::STATUS_INACTIVE,
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'end_at',
		'deleted_at',
	];
    private static $_instance = null;
    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new News();
        }
        return self::$_instance;
    }
	public function Photo()
	{
		return $this->belongsTo(File::class);
	}

	public function Company()
	{
		return $this->belongsTo(Users::class)->withTrashed();
	}

	public function Creator()
	{
		return $this->belongsTo(Users::class);
	}
    public function destroyRecord($id){

        return $this->destroyRow($id);
    }
    public function list($id='')
    {
        $query = $this->orderBy('id', 'desc');
        if (!empty($id)) {
            $result = $this->find($id);

            if (!isset($result)) {
                throw new CoreException(' شناسه ' . $id . ' یافت نشد');
            }
            $query->with('photo')->where('id',$id);
            if ($this->ISCompany()) {
                $query->where('company_id',$this->ISCompany());
            }
            $result=$result->first();
        }else{
            if ($this->ISCompany()) {
                $query->where('company_id',$this->ISCompany());
            }
            $result=$query->get();
        }
        return $this->modelResponse(['data' => $result, 'count' => !empty($id)?1:count($result)]);
    }
	// ********************************* Scope *********************************

	public function scopeCompanyId($query, $company_id = NULL)
	{
		if ($company_id) {
			return $query->where('company_id', $company_id);
		}

		return $query->whereNull('company_id');
	}

	// ********************************* Attributes *********************************


	// ********************************* Scope *********************************

	public function scopeActive($query)
	{
		return $query->whereDate('start_at', '<=', Carbon::now())
			->whereDate('end_at', '>=', Carbon::now())
			->where('status', self::STATUS_ACTIVE);
	}
}
