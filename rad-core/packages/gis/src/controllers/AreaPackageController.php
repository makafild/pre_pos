<?php

namespace core\Packages\gis\src\controllers;

use Core\Packages\route\Areas;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\Route\src\request\RouteRequest;

/**
 * Class RoutePackageController
 *
 * @package Core\Packages\route\src\controllers
 */


trait  AreaPackageController
{

    private $_fillable = [
        'province_id',
        'city_id',
        'area',
    ];

    public function list(){
        $result = Areas::_()->list();
        return $this->responseHandler($result);
    }

    public function show($id){
        $result = Areas::_()->list($id);
        return $this->responseHandler($result);
    }

    public function store(RouteRequest $request){
        $payload = $request->only($this->_fillable);
        $result = Areas::_()->store($payload);
        return $this->responseHandler($result);
    }

    public function update(RouteRequest $request,$id){
        $payload = $request->only($this->_fillable);
        $result = Areas::_()->updateU($payload,$id);
        return $this->responseHandler($result);
    }
}
