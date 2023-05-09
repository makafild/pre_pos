<?php


namespace Core\Packages\visitor;
use App\BaseModel;

class UnvisitedReport extends BaseModel
{
    public $table = 'unvisited_report';

    protected $fillable =[
        'visitor_id',
        'customer_id',
        'status',
        'unvisited_description_id',
        'description'
    ];

    private static $_instance = null;
    public static function _()
    {
        if (self::$_instance == null) {
            self::$_instance = new UnvisitorReport();
        }
        return self::$_instance;
    }
}
