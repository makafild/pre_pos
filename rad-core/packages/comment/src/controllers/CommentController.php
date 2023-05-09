<?php

namespace core\Packages\comment\src\controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\ModelFilters\CommentsFilter;
use Core\Packages\comment\Comments;
use Core\Packages\comment\CommentRates;
use Core\System\Http\Controllers\CoreController;
use Core\Packages\comment\src\request\ReplayRequest;
use Core\Packages\comment\src\request\ConfirmRequest;
use Core\Packages\comment\src\request\DestroyRequest;

class CommentController extends CoreController
{

    public function list($type, Request $request)
    {
        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            foreach ($sort_arr as $key => $nameSort) {
                $request->request->add(["sort" . $key => $nameSort]); //add request
            }
        }
        $comments = Comments::with(['user', 'answerUser','company'])
            ->withCount(['like', 'dislike'])
            ->where('type', $type);

        if ($type == 'company') {
            $comments = $comments->where('company_id', auth('api')->id());
        }
        $comments->filter($request->all(), CommentsFilter::class);


        return $comments->orderBy('id', 'desc')->jsonPaginate();
    }
    public function sign(Request $request)
    {
        if ($request->has('sort')) {
            $sort_arr = $request->get('sort');
            foreach ($sort_arr as $key => $nameSort) {
                $request->request->add(["sort" . $key => $nameSort]); //add request
            }
        }
        $comments = Comments::with(['user', 'answerUser','company','product'])
            ->withCount(['like', 'dislike']);
        if (auth('api')->user()->company_id) {
            $comments = $comments->where('company_id', auth('api')->id());
        }
        $comments->where('confirm','0');


        return $comments->orderBy('id', 'desc')->get();
    }


    public function show($id)
    {
        $comment = Comments::with(['user', 'answerUser'])->withCount(['like', 'dislike'])->where('id', $id)->first();
        return $comment;
    }

    public function confirm(ConfirmRequest $request)
    {
        foreach ($request->comment_ids as $commentId) {
            Comments::where('id', $commentId)->update([
                'confirm' => $request->status,
                'confirm_by' => auth('api')->id(),
                'confirm_date' => Carbon::now()
            ]);
        }

        return [
            'status' => true,
            'message' => ' با موفقیت ثبت شد'
        ];
    }

    public function replay(ReplayRequest $request, $id)
    {
        if (auth('api')->user()->kind == 'company') {
            return [
                'status' => true,
                'message' => 'دسترسی غیر مجاز'
            ];
        }

        $row = Comments::where('id', $id);
        if (empty($row->first())) {
            return [
                'status' => true,
                'message' => 'شناسه کامنت یافت نشد'
            ];
        }

        $row->update([
            'answer_text' => $request->text,
            'answer_user_id' => auth('api')->id(),
            'answer_date' => Carbon::now()
        ]);

        return [
            'status' => true,
            'message' => ' با موفقیت ثبت شد'
        ];
    }

    public function destroy(DestroyRequest $request)
    {
        CommentRates::whereIn('comment_id', $request->comment_ids)->delete();
        Comments::whereIn('id', $request->comment_ids)->delete();

        return [
            'status' => true,
            'message' => ' با موفقیت حذف شد'
        ];
    }
}
