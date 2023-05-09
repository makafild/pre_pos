<?php

namespace Core\Packages\shop;

use Carbon\Carbon;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Database\Eloquent\Model;

class Loglogin extends Model
{
    protected $fillable = [
        'user_id',
        'created_at'
    ];

    protected $table="log_login";
    protected $primaryKey="user_id";
    public $timestamps= false;

    protected $appends = ["Color"];




    public function getColorAttribute() {



        $time= Carbon::parse($this->attributes['created_at']);
        $created= $time->format('Y-m-d');
        $now=Carbon::now();
       $deff= $now->diffInDays($created);
       if($deff<3) return "green";
      else if($deff<8) return "yellow";
      else return "red";


   }



}
