<?php

namespace App\Models\Common;

use App\Models\User\OneSignalPlayer;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * Class Notification
 *
 * @package App\Models\Common
 * @property string $title
 * @property string $message
 * @property string $link
 *
 * @property array  $categories
 * @property array  $countries
 * @property array  $provinces
 * @property array  $cities
 *
 * @property string $status
 */
class Notification extends Model
{
	use Notifiable;

	const STATUS_PENDING = 'pending';
	const STATUS_DONE    = 'done';

	const STATUS = [
		self::STATUS_PENDING,
		self::STATUS_DONE,
	];

	protected $casts = [
		'categories' => 'array',
		'countries'  => 'array',
		'provinces'  => 'array',
		'cities'     => 'array',
	];

	public function getUsersId() :array
	{
		if($this->users)
			return $this->users;

		/** @var User[] $users */
		$users = User::Active()->select('id')->with('SignalPlayerIds');

		if ($this->countries)
			$users = $users->whereHas('countries', function ($query) {
				$query->whereIn('id', $this->countries);
			});
		if ($this->provinces)
			$users = $users->whereHas('provinces', function ($query) {
				$query->whereIn('id', $this->provinces);
			});
		if ($this->cities)
			$users = $users->whereHas('cities', function ($query) {
				$query->whereIn('id', $this->cities);
			});
		if ($this->categories)
			$users = $users->whereHas('categories', function ($query) {
				$query->whereIn('id', $this->categories);
			});
		$users = $users->get();

		\Log::info("------------------------------------ Notification {$this->id} ------------------------------------");
		\Log::info("users count: " . count($users));
		$loggedOutUsers = [];
		foreach ($users as $user) {
			if (!count($user->SignalPlayerIds)) {
				$loggedOutUsers[] = $user;
			} else {
				\Log::info("user {$user->id}: " . count($user->SignalPlayerIds));
			}
		}
		\Log::info("users not logged in: " . count($loggedOutUsers));
		\Log::info(collect($loggedOutUsers)->pluck('id')->all());

		$users = $users->pluck('id')->all();

		\Log::info($users);

		return $users;
	}

	public function routeNotificationForOneSignal()
	{
		\Log::info("------------one------------");
		\Log::info($this->getUsersId());

		$oneSignalPlayers = OneSignalPlayer::whereIn('user_id', $this->getUsersId())
			->where('provider', 'onesignal')
			->get()
			->pluck('player_id')
			->all();

		\Log::info("Onesignal players count: " . count($oneSignalPlayers));
		\Log::info($oneSignalPlayers);

		return $oneSignalPlayers;
	}

    public function routeNotificationForChabokPush()
    {
        \Log::info("-----------cha-------------");
        \Log::info($this->getUsersId());

        $oneSignalPlayers = OneSignalPlayer::whereIn('user_id', $this->getUsersId())
            ->where('provider', 'chabokpush')
            ->get()
            ->pluck('player_id')
            ->all();

        \Log::info("ChabokPush players count: " . count($oneSignalPlayers));
        \Log::info($oneSignalPlayers);

        return $oneSignalPlayers;
    }

    public function routeNotificationForFcm()
    {

        \Log::info("-----------fcm push-------------");
        \Log::info($this->getUsersId());

        $oneSignalPlayers = OneSignalPlayer::whereIn('user_id', $this->getUsersId())
            ->where('provider', 'fcm')
            ->get()
            ->pluck('player_id')
            ->all();


        $data = [];
        foreach ($oneSignalPlayers as $oneSignalPlayer) {
            $data[] = json_decode($oneSignalPlayer)->token;
        }
        $oneSignalPlayers = $data;

        
        \Log::info("fcm push players count: " . count($oneSignalPlayers));
        \Log::info($oneSignalPlayers);

        return $oneSignalPlayers;
    }
}
