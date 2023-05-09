<?php

namespace Core\Packages\group;

use Core\Packages\user\Users;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Core\System\Http\Traits\SecureDelete;
use Illuminate\Database\Eloquent\SoftDeletes;


class Group extends Model
{
    use SecureDelete;
    use Filterable;
    const WEB_MASTER = 'web';
    const WEB_COMPANY = 'company';
    const APP_CUSTOMER = 'app';
    const APP_VISITOR = 'visit_app';
    const FOR_KINDS = [
        self::WEB_MASTER,
        self::APP_CUSTOMER,
        self::APP_VISITOR,
        self::WEB_COMPANY
    ];

    public function user()
    {
        return $this->hasMany(Users::class, 'group_id');
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $table = "groups";
}
