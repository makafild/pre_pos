<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

namespace App\Traits;

use App\Observers\VersionObserver;

trait VersionObserve
{
	public static function bootVersionObserve()
	{
		static::observe(new VersionObserver);
	}
}