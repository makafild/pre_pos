<?php

namespace App\Http\Controllers\api\Customer\v1\Order;

use App\Http\Requests\api\Customer\v1\Order\StoreOrderRequest;
use App\Http\Requests\api\Customer\v1\Order\StorePeriodicOrderRequest;
use App\Models\Order\PeriodicDetail;
use App\Models\Order\PeriodicOrder;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PeriodicOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */

    public function index()
    {
        return PeriodicOrder::CustomerId(auth()->id())
            ->with([
                'company.photo',
            ])
            ->latest()
            ->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePeriodicOrderRequest $request
     * @return array
     */
    public function store(StorePeriodicOrderRequest $request)
    {
        // order by company
        $idOfProducts = collect($request->products)->pluck('id')->all();
        /** @var Product[] $productsEntity */
        $productsEntity = Product::whereIn('id', $idOfProducts)->get()->keyBy('id');

        $requestProducts = [];
        foreach ($request->products as $requestProduct) {

            $id = $requestProduct['id'];
            $company_id = $productsEntity[$id]->company_id;

            $requestProducts[$company_id][] = $requestProduct;
        }

        $orderIds = [];

        // store order
        foreach ($requestProducts as $companyId => $products) {
            $order = new PeriodicOrder();
            $order->status = PeriodicOrder::STATUS_REGISTERED;
            $order->company_id = $companyId;
            $order->customer_id = auth()->id();
            $order->days = $request->days;
            $order->save();

            foreach ($products as $product) {
                $detail = new PeriodicDetail();
                $detail->product_id = $product['id'];

                $detail->master = $product['master'];
                $detail->slave = $product['slave'];
                $detail->slave2 = $product['slave2'];

                $detail->master_unit_id = $productsEntity[$product['id']]->master_unit_id;
                $detail->slave_unit_id = $productsEntity[$product['id']]->slave_unit_id;
                $detail->slave2_unit_id = $productsEntity[$product['id']]->slave2_unit_id;

                if ($detail->slave2_unit_id && $detail->per_slave) {
                    // with slave 2
                    $detail->total = $detail->master * $detail->per_master + $detail->slave * $detail->per_slave + $detail->slave2;
                } else {
                    // without slave 2
                    $detail->total = $detail->master * $detail->per_master + $detail->slave;
                }

                // Save
                $order->Details()->save($detail);
            }

            $order->save();
            $orderIds[] = $order->id;
        }

        return [
            'status'  => true,
            'message' => trans('messages.api.customer.order.order.store', ['count' => count($orderIds)]),
            'id'      => $orderIds,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function show($id)
    {

        $order = PeriodicOrder::where('id', $id)->first();
        if (!$order)
            return [
                'status'  => false,
                'message' => 'سفارشی با این شناسه یافت نشد',
            ];

        return $order;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePeriodicOrderRequest $request
     * @return array
     */
    public function update($id, StorePeriodicOrderRequest $request)
    {
        // store order
        $order = PeriodicOrder::findOrFail($id);
        $order->status = PeriodicOrder::STATUS_REGISTERED;
        $order->days = $request->days;
        $order->save();

        foreach ($request->products as $product) {
            $detail = new PeriodicDetail();
            $detail->product_id = $product['id'];

            $detail->master = $product['master'];
            $detail->slave = $product['slave'];
            $detail->slave2 = $product['slave2'];

            $detail->master_unit_id = $productsEntity[$product['id']]->master_unit_id;
            $detail->slave_unit_id = $productsEntity[$product['id']]->slave_unit_id;
            $detail->slave2_unit_id = $productsEntity[$product['id']]->slave2_unit_id;

            if ($detail->slave2_unit_id && $detail->per_slave) {
                // with slave 2
                $detail->total = $detail->master * $detail->per_master + $detail->slave * $detail->per_slave + $detail->slave2;
            } else {
                // without slave 2
                $detail->total = $detail->master * $detail->per_master + $detail->slave;
            }

            // Save
            $order->Details()->save($detail);
        }

        $order->save();

        return [
            'status'  => true,
            'message' => trans('messages.api.customer.order.order.store', ['count' => count($orderIds)]),
            'id'      => $order->id,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function destroy($id)
    {
        $status = PeriodicOrder::CustomerId(auth()->id())
            ->with([
                'company.photo',
                'details',
                'details.product',
                'details.product.photo',
                'details.MasterUnit',
                'details.SlaveUnit',
                'details.Slave2Unit',
            ])
            ->where('id', $id)
            ->delete();

        if (!$status)
            return [
                'status' => false,
            ];

        return [
            'status'  => true,
            'message' => 'سفارش دوره‌ای با موفقیت حذف شد.',
        ];
    }
}
