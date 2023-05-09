<?php

namespace App\Http\Controllers\api\Customer\v1\Common;

use App\Events\Notification\NotificationStoredEvent;
use App\Http\Requests\Common\Notification\DestroyNotificationRequest;
use App\Http\Requests\Common\Notification\StoreNotificationRequest;
use App\Models\Common\Notification;
use App\Models\User\OneSignalPlayer;
use App\Models\User\User;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$notifications = Notification::query();


		$datatable = datatables()->of($notifications)
			// created_at
			->editColumn('created_at', function (Notification $slider) {
				$v = new Verta($slider->created_at);

				return str_replace('-', '/', $v->formatDate());
			})
			->filterColumn('created_at', function ($query, $date) {
				$date = Verta::parse($date)->DateTime();

				$query->whereDate('created_at', $date);
			})
			->toJson();

		return $datatable;
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreNotificationRequest $request)
	{

		$notification             = new Notification();
		$notification->title      = $request->title;
		$notification->message    = $request->message;
		$notification->link       = $request->link;
		$notification->categories = $request->categories ? array_column($request->categories, 'id') : [];
		$notification->countries  = $request->countries ? array_column($request->countries, 'id') : [];
		$notification->provinces  = $request->provinces ? array_column($request->provinces, 'id') : [];
		$notification->cities     = $request->cities ? array_column($request->cities, 'id') : [];
		$notification->save();
		event(new NotificationStoredEvent($notification));

		return [
			'status'  => true,
			'message' => 'درخواست با موفقیت ثبت شد.',
		];
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  int                      $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param DestroyNotificationRequest $request
	 * @return array
	 */
	public function destroy(DestroyNotificationRequest $request)
	{
		$ids = collect($request->models)->pluck('id');

		/** @var Notification[] $notifications */
		$notifications = Notification::whereIn('id', $ids)
			->get()
			->keyBy('id');

		foreach ($ids as $id) {
			$notification = $notifications[$id];

			if (auth()->user()->can('destroy', $notification)) {

			} else {
				abort(500);
			}
		}

		Notification::whereIn('id', $ids)->delete();

		return [
			'status'  => true,
			'message' => trans('messages.common.notification.destroy'),
		];
	}
}
