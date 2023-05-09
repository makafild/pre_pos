<?php

namespace core\Packages\survey\src\controllers;

use App\Models\Common\Survey;
use App\Models\Common\SurveyQuestion;
use App\Models\Common\SurveyAnswer;
use App\Models\Common\SurveySeen;
use Carbon\Carbon;
use Core\Packages\survey\src\request\SurveyRequest;
use Core\Packages\survey\src\request\DestroySurveyRequest;
use Core\Packages\user\Users;
use Core\System\Exceptions\CoreException;
use Core\System\Http\Controllers\CoreController;
use Hekmatinasser\Verta\Verta;
use DB;

use Illuminate\Http\Request;

class SurveyController extends CoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companyId = auth('api')->user()->company_id;
        $surveys = Survey::where('surveys.company_id', $companyId)
            ->orderBy('id', 'desc')
            ->jsonPaginate();

        return $surveys;
    }

    public function checkDeleteQuestion($surveyData, $request)
    {
        $allAnswersQuestion=[];
        $surveyAnswers = SurveyAnswer::where('survey_id', $surveyData['id'])->get();
        if ($surveyAnswers->isNotEmpty()) {
            $allAnswersQuestion = [];
            foreach ($surveyAnswers as $answers) {
                $questions = $answers['questions'];
                if (count($questions)) {
                    foreach ($questions as $question) {
                        $allAnswersQuestion[] = $question->id;
                    }
                }
            }
        }

        $existsQuestions = SurveyQuestion::where('survey_id', $surveyData['id'])->pluck('id')->all();

        $inputQuestions = [];
        foreach ($request->questions as $question) {
            if (isset($question['id']) && !empty($question['id'])) {
                $inputQuestions[] = $question['id'];
            }
        }

        if (count(array_diff($existsQuestions, $inputQuestions))) {
            foreach (array_diff($existsQuestions, $inputQuestions) as $question) {
                if (in_array($question, $allAnswersQuestion)) {
                    throw new CoreException("برای سوال با شناسه {$question} جواب ثبت شده (سوال مورد نظر قابل حذف نمی باشد)");
                }
            }
        }

        return array_diff($existsQuestions, $inputQuestions);
    }

    public function prepareData($request, $status, $surveyData = null)
    {
        $survey = new Survey();
        $survey->title = $request->title;
        $survey->type = $request->type;
        if (!empty($request->from_date)) {
            $survey->from_date = Verta::parse($request->from_date)->DateTime();
            $survey->to_date = Verta::parse($request->to_date)->DateTime();
        }

        $survey->Company()->associate(Users::where('id',auth('api')->user()->company_id)->first());

        if ($status == 'update') {
            $checkDeleteQuestion = $this->checkDeleteQuestion($surveyData, $request);
            $dataUpdate=$survey->toArray();
	    $survey = Survey::find($surveyData['id']);
            $survey->update($dataUpdate);


            DB::table('survey_province')->where('survey_id', $surveyData['id'])->delete();
            DB::table('survey_city')->where('survey_id', $surveyData['id'])->delete();
            DB::table('survey_area')->where('survey_id', $surveyData['id'])->delete();
            DB::table('survey_route')->where('survey_id', $surveyData['id'])->delete();
            DB::table('survey_customer')->where('survey_id', $surveyData['id'])->delete();

        } else {
            $survey->save();
        }
        if ($status == 'update') {
            SurveyQuestion::whereIn('id', $checkDeleteQuestion)->delete();

            foreach ($request->questions as $question) {
                if (isset($question['id']) && !empty($question['id'])) {
                    $surveyQuestion = SurveyQuestion::find($question['id']);
                } else {
                    $surveyQuestion = new SurveyQuestion();
                }

                $surveyQuestion->kind = $question['kind'];
                $surveyQuestion->question = $question['question'];

                if ($question['question'] != SurveyQuestion::KIND_CHECK_BOX)
                    $surveyQuestion->options = $question['options'];
                else
                    $surveyQuestion->options = NULL;

                if (isset($question['id']) && !empty($question['id'])) {
                    $surveyQuestion->update((array)$surveyQuestion);
                } else {
                    $survey->Questions()->save($surveyQuestion);
                }
            }
        } else {
            foreach ($request->questions as $question) {
                $surveyQuestion = new SurveyQuestion();
                $surveyQuestion->kind = $question['kind'];
                $surveyQuestion->question = $question['question'];

                if ($question['question'] != SurveyQuestion::KIND_CHECK_BOX)
                    $surveyQuestion->options = $question['options'];
                else
                    $surveyQuestion->options = NULL;

                $survey->Questions()->save($surveyQuestion);
            }
        }


        if (isset($request->cities) && count($request->provinces)) {
            $survey->Provinces()->sync(collect(array_unique($request->provinces))->all());
        }

        if (isset($request->cities) && count($request->cities)) {
            $survey->Cities()->sync(collect(array_unique($request->cities))->all());
        }

        if (isset($request->areas) && count($request->areas)) {
            $survey->Areas()->sync(collect(array_unique($request->areas))->all());
        }

        if (isset($request->routes) && count($request->routes)) {
            $survey->Routes()->sync(collect(array_unique($request->routes))->all());
        }

        if (isset($request->customers) && count($request->customers)) {
            $survey->Customers()->sync(collect($request->customers)->all());
        }

        return $survey->id;
    }


    public function store(SurveyRequest $request)
    {
        $surveyId = $this->prepareData($request, 'store');
        return [
            'status' => true,
            'message' => trans('messages.common.survey.store'),
            'id' => $surveyId,
        ];
    }

    public function update(SurveyRequest $request, $id)
    {
        $survey = Survey::where('id',$id)->where('company_id',auth('api')->user()->company_id)->first();
        if (empty($survey)) {
            return [
                'statue' => true,
                'message' => "شناسه $id یافت نشد"
            ];
        }

        $this->prepareData($request, 'update', $survey);

        return [
            'status' => true,
            'message' => trans('messages.common.survey.update'),
        ];
    }


    public function show($id)
    {
        ini_set('memory_limit', '512M');
        $survey = Survey::with([
            'Provinces',
            'cities',
            'areas',
            'routes',
            'customers',
            'questions',
            'answers',
            'answers.user.provinces',
            'answers.user.Cities',

        ])->where('id',$id)->where('company_id',auth('api')->user()->company_id)->first();
        if (!empty($survey)) {
            foreach ($survey['answers'] as $answer) {

                foreach ($answer['questions'] as $indexQuestion => $question) {

                    $answer->$indexQuestion = $question;
                }
            }
        }


        return $survey;
    }

    public function destroy(DestroySurveyRequest $request)
    {
        $ids = collect($request->surveys)->pluck('id');
        try {

            Survey::whereIn('id', $ids)->where('company_id',auth('api')->user()->company_id)->delete();
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return [
            'status' => true,
            'message' => trans('messages.common.survey.destroy'),
        ];
    }

}
