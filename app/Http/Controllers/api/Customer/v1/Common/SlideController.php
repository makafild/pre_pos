<?php

namespace App\Http\Controllers\api\Customer\v1\Common;

use App\Http\Controllers\Controller;
use App\Models\Common\Slider;

class SlideController extends Controller
{
    public function index(){

       return $slide = Slider::with('File')->where('status' , 'active')->get();
    }





    // public function index()
    // {

    //     $slides = Slider::Active()
    //         ->with('file')
    //         ->where(function ($query) {
    //             $query->whereHas('provinces', function ($query) {
    //                 return $query->where('id', auth('mobile')->user()->provinces->pluck('id'));
    //             });
    //         })->orWhere(function ($query) {

    //             $query->whereHas('cities', function ($query) {
    //                 return $query->where('id', auth('mobile')->user()->cities->pluck('id'));
    //             });
    //         })
    //         ->get();

    //     return $slides;
    // }
    /*public function index()
	{
		$slides = Slider::Active()
			->with('file')
			->where(function ($query) {
				$query
					->where(function ($query) {
						$query->whereDoesntHave('countries')
							->whereDoesntHave('provinces')
							->whereDoesntHave('cities');
					})

					->orWhere(function ($query) {
						$query->whereHas('provinces', function ($query) {
							return $query->where('id', auth('mobile')->user()->provinces->pluck('id'));
						})
							->whereDoesntHave('cities');
					})
					->orWhere(function ($query) {
						$query->whereHas('countries', function ($query) {
							return $query->where('id', auth('mobile')->user()->countries->pluck('id'));
						})->whereHas('provinces', function ($query) {
							return $query->where('id', auth('mobile')->user()->provinces->pluck('id'));
						})->whereHas('cities', function ($query) {
							return $query->where('id', auth('mobile')->user()->cities->pluck('id'));
						});
					});
			})
			->get();

		return $slides;
	}*/
}
