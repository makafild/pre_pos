<?php

namespace core\Packages\photo\src\controllers;

use Core\Packages\common\File;

use Core\Packages\photo\src\request\DestroyBrandRequest;
use core\Packages\photo\src\request\StoreFileRequest;
use core\Packages\photo\src\request\StorePhotosRequest;
use Core\Packages\photo\src\request\StoreRequest;
use Core\System\Http\Controllers\CoreController;

class PhotoPackageController extends CoreController
{
    public function index()
    {
        $sliders = File::latest()->jsonPaginate(10);


        return $sliders;
    }

    public function show($id)
    {
        /** @var File $photo */
        $photo = File::findOrFail($id);

        return $photo;
    }

    public function store(StoreRequest $request)
    {
        // Store Photo
        $photoPath = $request->photo->store('public/photos');
dd($photoPath);
        $photo = new File();
        $photo->disk = 'local';
        $photo->path = $photoPath;
        $photo->extension = $request->photo->extension();
        $photo->save();

        return [
            'status'  => true,
            'message' => trans('messages.common.photo.store'),
            'id'      => $photo->id,
        ];
    }

    public function file(DestroyBrandRequest $request)
    {
        // Store Photo
        $photoPath = $request->photo->store('public/files');

        $photo = new File();
        $photo->disk = 'local';
        $photo->path = $photoPath;
        $photo->extension = $request->photo->extension();
        $photo->save();

        return [
            'status'  => true,
            'message' => trans('messages.common.photo.store'),
            'id'      => $photo->id,
        ];
    }

    public function status()
    {
        $data = [];
        foreach (File::STATUS as $STATUS) {
            $data[] = [
                'name'  => $STATUS,
                'title' => trans("translate.common.file.$STATUS"),
            ];
        };

        return $data;
    }

}
