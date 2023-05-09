<?php

namespace App\Http\Controllers\api\Customer\v1\Comment;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use Carbon\Carbon;
use Core\Packages\comment\Comments;
use Core\Packages\comment\CommentRates;
use App\Http\Requests\api\Customer\v1\Comment\CommentRequest;
use App\Http\Requests\api\Customer\v1\Comment\RateRequest;

//use Carbon\Carbon;
//use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function list($type, $id = 0)
    {
        
        $comment = Comments::with(['user.photo', 'answerUser.photo'])
            ->withCount(['like', 'dislike'])
            ->with([
                'like' => function ($query) {
                    $query->where('user_id', '=', auth()->id());
                },
            ])
            ->with([
                'dislike' => function ($query) {
                    $query->where('user_id', '=', auth()->id());
                },
            ])
            ->where('type', $type)
            ->where('confirm', 1);
        if ($id != 0) {
            $typeName = $type == 'product' ? 'product_id' : 'company_id';
            $comment = $comment->where($typeName, $id);
        }


        return $comment->paginate();
    }

    public function store(CommentRequest $request)
    {
        $comment = new Comments();
        $comment->type = $request->type;
        $comment->user_id = auth()->id();

        if ($request->type == 'product') {
            $comment->product_id = $request->product_id;
            $getProduct = Product::where('id', $request->product_id)->first();
            if (!empty($getProduct)) {
                $comment->company_id = $getProduct['company_id'];
            };
        }

        if ($request->type == 'company') {
            $comment->company_id = $request->company_id;

        }

        $comment->text = $request->text;
        $comment->save();

        return [
            'status' => true,
            'message' => ' با موفقیت ثبت شد'
        ];
    }

    public function rate_store(RateRequest $request, $id)
    {
        $findRow = CommentRates::where('comment_id', $id)
            ->where('user_id', auth()->id());

        if (!empty($findRow->first())) {
            if (
                ($findRow->first()['action'] == 'like' && $request->action == 'like' && $request->value == 0) ||
                ($findRow->first()['action'] == 'dislike' && $request->action == 'dislike' && $request->value == 0)
            ) {
                $findRow->delete();
            }

            if (
                ($findRow->first()['action'] == 'like' && $request->action == 'dislike' && $request->value == 1) ||
                ($findRow->first()['action'] == 'dislike' && $request->action == 'like' && $request->value == 1)
            ) {
                $findRow->update(['action' => $request->action]);
            }
        } else {
            if ($request->value == 1) {
                $commentRate = new CommentRates();
                $commentRate->comment_id = $id;
                $commentRate->action = $request->action;
                $commentRate->user_id = auth()->id();
                $commentRate->save();
            }
        }

        return response([
            'status' => false,
            'message' => trans('باموفقیت ثبت شد'),
        ]);
    }

    public function rate_list($id)
    {
        $findRow = CommentRates::where('comment_id', $id);

        return response([
            'status' => false,
            'result' => [
                'like' => $findRow->where('type', 'like')->count(),
                'dislike' => $findRow->where('type', 'dislike')->count(),
            ]
        ]);
    }
}
