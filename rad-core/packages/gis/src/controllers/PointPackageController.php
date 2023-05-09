<?php

namespace core\Packages\gis\src\controllers;

use Core\Packages\point\Points;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\Point\src\request\PointRequest;

/**
 * Class PointPackageController
 *
 * @package Core\Packages\point\src\controllers
 */
trait PointPackageController
{

    private $_fillable = [
        'route_id',
        'lat',
        'lan',
        'state'
    ];

    public function list()
    {
        $result = Points::_()->list();
        return $this->responseHandler($result);
    }

    public function show($id)
    {
        $result = Points::_()->list($id);
        return $this->responseHandler($result);
    }

    public function store(PointRequest $request)
    {
        $payload = $request->only($this->_fillable);
        $result = Points::_()->store($payload);
        return $this->responseHandler($result);
    }

    public function update(PointRequest $request, $id)
    {
        $payload = $request->only($this->_fillable);
        $result = Points::_()->updateU($payload, $id);
        return $this->responseHandler($result);
    }
}
