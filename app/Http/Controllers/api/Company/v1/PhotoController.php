<?php

namespace App\Http\Controllers\api\Company\v1;

use App\Http\Requests\api\Company\v1\Photo\StorePhotoRequest;
use App\Models\Common\File;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PhotoController extends Controller
{
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  StorePhotoRequest $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StorePhotoRequest $request)
	{
		foreach ($request->photos as $photo) {
			$photoId = $this->storePhoto($photo['kind'], $photo['file'], $photo['extension']);

			/** @var Product $product */
			$product = Product::where([
				'referral_id' => $photo['product_referral_id'],
				'company_id'  => auth()->id(),
			])->first();

			$product->photo_id = $photoId;
			$product->save();

			$product->Photos()->sync($photoId);
		}

		return [
			'status' => true,
		];
	}

	private function storePhoto($kind, $file, $extension)
	{
		$fileName = sprintf('%s.%s', str_random(), $extension);

		$basePath = sprintf('public/photos/company_%d', auth()->id());
		$filePath = sprintf('%s/%s', $basePath, $fileName);

		switch ($kind) {
			case 'base64':

				$file = explode(',', $file);
				$decoded = base64_decode($file[1]);

				\Storage::put($filePath, $decoded);

				break;
			case 'file':

				$filePath = $file->storeAs($basePath, $fileName);

				break;
		}

		$file = new File();
		$file->disk = 'local';
		$file->path = $filePath;
		$file->extension = $extension;
		$file->save();

		return $file->id;
	}
}
