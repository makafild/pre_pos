<?php

namespace core\Packages\gis\src\controllers;

use Core\Packages\route\Routes;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\Route\src\request\RouteRequest;

/**
 * Class RoutePackageController
 *
 * @package Core\Packages\route\src\controllers
 */


trait RoutePackageController
{

    private $_fillable = [
        'province_id',
        'city_id',
        'area',
        'street'
    ];

    public function list(){
       
        $result = Routes::_()->list();
        return $this->responseHandler($result);
    }

    public function show($id){
        $result = Routes::_()->list($id);
        return $this->responseHandler($result);
    }

    public function store(RouteRequest $request){
        $payload = $request->only($this->_fillable);
        $result = Routes::_()->store($payload);
        return $this->responseHandler($result);
    }

    public function update(RouteRequest $request,$id){
        $payload = $request->only($this->_fillable);
        $result = Routes::_()->updateU($payload,$id);
        return $this->responseHandler($result);
    }
}
