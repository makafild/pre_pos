<?php

namespace Core\Packages\report;

use Core\Packages\gis\Routes;
use Core\Packages\product\Product;
use Illuminate\Database\Eloquent\Model;


class ReportsSaleProductRoute extends Model
{

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $table = "reports_sale_product_route";

    public function Product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');

    }
    public function Route()
    {
        return $this->hasOne(Routes::class, 'id', 'route_id');

    }
}
