<?php

namespace Core\Packages\not_visited;

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

class NotVisited extends Model
{
    use  SoftDeletes;
    protected $table = "resone_not_visited";

}
