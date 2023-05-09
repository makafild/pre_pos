<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

namespace App\Observers;

class VersionObserver
{
	public function updating($model)
	{
		$model->row_version = $model->row_version + 1;
	}

	public function creating($model)
	{
		$model->row_version = $model->row_version + 1;
	}

	public function removing($model)
	{
		$model->row_version = $model->row_version + 1;
	}

	public function saving($model)
	{
		$model->row_version = $model->row_version + 1;
	}
}