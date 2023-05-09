<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class NewsSeen extends Model
{
	protected $fillable = [
		'user_id',
		'news_id',
	];
	public $timestamps = false;


	public static function Check($userId, $companyId)
	{
		$companyNews = News::CompanyId($companyId)
			->Active()
			->select('id')
			->get();

		$seenedNewsIds = self::where([
			'user_id' => $userId,
		])
			->get()
			->pluck('news_id');

		return $companyNews->whereNotIn('id', $seenedNewsIds)->count();
	}
}
