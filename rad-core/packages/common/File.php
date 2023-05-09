<?php

namespace Core\Packages\common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

/**
 * Class Photo
 *
 * @package App\Models\Common
 *
 * @property int    $id
 * @property string $disk
 * @property string $path
 * @property string $extension
 * @property string $url
 */
class File extends Model
{
	const STATUS_HAVE = 'have';
	const STATUS_NOT_HAVE = 'not_have';

	const STATUS = [
		self::STATUS_HAVE,
		self::STATUS_NOT_HAVE,
	];

	protected $appends = [
		'url',
	];

	public static function store(UploadedFile $photoFile)
	{
		// Store Photo
		$photoPath = $photoFile->store('public/files');

		$photo = new File();
		$photo->disk = 'local';
		$photo->path = $photoPath;
		$photo->extension = $photoFile->extension();
		$photo->save();

		return $photo;
	}

	public function getUrlAttribute()
	{
		/*$url = \Storage::disk($this->disk)->url($this->attributes['path']);

		if ($this->disk == 'local') {
			$url = asset($url);
		}*/
		$url=env('APP_URL').'/app/'.$this->attributes['path'];

		return $url;
	}
}
