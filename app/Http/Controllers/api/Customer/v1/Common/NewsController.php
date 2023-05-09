<?php

namespace App\Http\Controllers\api\Customer\v1\Common;

use App\Models\Common\News;
use App\Http\Controllers\Controller;
use App\Models\Common\NewsSeen;
use App\Traits\CheckAccess;

class NewsController extends Controller
{
	private const INDEX_PAGES = 20;
	private const TOP_PAGES   = 5;
    use CheckAccess;

    /**
     * List of news
     *
     * @return array
     */
    public function index()
    {
        $companyId = request('company_id');
        if ($companyId) {
            if (!$this->chAc($companyId)) {
                return [
                    'status'  => false,
                    'message' => 'شما به این صفحه دسترسی ندارید.',
                ];
            }
        }

		$news = News::CompanyId($companyId)->Active()
			//->Active()
			->with([
				'photo',
			])
			->latest()
			->paginate(self::INDEX_PAGES);

		return $news;
	}

	/**
	 * Show a single news
	 *
	 * @param $id
	 * @return News
	 */
	public function show(int $id)
	{
		/** @var News $news */
		$news = News::with([
			'photo',
			'company',
			'creator',
		])->findOrFail($id);

		NewsSeen::firstOrCreate([
			'user_id' => auth()->id(),
			'news_id' => $news->id,
		]);

		return $news;
	}

	public function top()
	{
		$companyId = request('company_id');

		return News::CompanyId($companyId)
			->Active()
			->with([
				'photo',
			])
			->orderBy('created_at', 'desc')
			->paginate(self::TOP_PAGES);
	}
}
