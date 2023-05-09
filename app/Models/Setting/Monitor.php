<?php

namespace App\Models\Setting;

use App\Models\User\User;
use Hamcrest\Core\Set;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 *
 * @package App\Models\Setting
 *
 * @property string $key
 * @property string $value
 *
 * @property int    $company_id
 * @property User   $company
 *
 * @method static Setting CompanyId(integer $company_id)
 */
class Monitor extends Model
{

    protected $table = 'monitors';


    public static function addMonitor($user_id,$message,$action)
    {
        $moni = new Monitor();
        $moni->user_id =$user_id;
        $moni->message =$message;
        $moni->action =$action;
        $moni->save();
    }



}
