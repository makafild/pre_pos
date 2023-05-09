<?php

namespace App\Http\Controllers\api\Customer\v1\Common;

use App\Http\Requests\api\Customer\v1\Common\Photo\StorePhotoRequest;
use App\Models\Common\File;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileController extends Controller
{
	/**
	 * @param Request $request
	 * @return File
	 */
	public function store(StorePhotoRequest $request)
	{
		$path = 'public/photos/users/' . str_random() . '.' . $request->extension;
		$photoPath = storage_path('app/' . $path);

        $photo = explode(',', $request->photo);
        $decoded = base64_decode($photo[1]);

        file_put_contents($photoPath, $decoded);

		$photo = new File();
		$photo->disk = 'local';
		$photo->path = $path;
		$photo->extension = $request->extension;
		$photo->save();

		return $photo;
	}
}
