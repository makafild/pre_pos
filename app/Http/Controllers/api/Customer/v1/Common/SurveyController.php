<?php

namespace App\Http\Controllers\api\Customer\v1\Common;

use App\Http\Requests\api\Customer\v1\Common\StoreSurveyAnswerRequest;
use App\Http\Requests\Common\Survey\StoreSurveyRequest;
use App\Models\Common\Survey;
use App\Models\Common\SurveyAnswer;
use App\Models\Common\SurveyQuestion;
use App\Models\Common\SurveySeen;
use App\Models\User\Role;
use App\Models\User\User;
use App\Traits\CheckAccess;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Core\Packages\survey\SurveyPositions;

class SurveyController extends Controller
{
    use CheckAccess;

    public function index()
    {
        $companyId = request('company_id');

        if ($companyId) {
            if (!$this->chAc($companyId)) {
                return [
                    'status' => true,
                    'data' => [],
                    'message' => 'شما به این صفحه دسترسی ندارید.',
                ];
            }
        }

        $userInfo = User::where('id', auth()->id())->with(['Provinces', 'Cities', 'Areas', 'Routes'])->first();

        $provinces = [];
        $cities = [];
        $areas = [];
        $routes = [];
        if (!empty($userInfo)) {
            $userInfo = $userInfo->toArray();
            if (!empty($userInfo['provinces'])) {
                foreach ($userInfo['provinces'] as $province) {
                    $provinces[] = $province['id'];
                }
            }

            if (!empty($userInfo['cities'])) {
                foreach ($userInfo['cities'] as $city) {
                    $cities[] = $city['id'];
                }
            }

            if (!empty($userInfo['areas'])) {
                foreach ($userInfo['areas'] as $area) {
                    $areas[] = $area['area_id'];
                }
            }

            if (!empty($userInfo['routes'])) {
                foreach ($userInfo['routes'] as $route) {
                    $routes[] = $route['route_id'];
                }
            }
        }
        $answers = SurveyAnswer::where([
            'user_id' => auth()->id(),
        ])
            ->whereHas('Survey', function ($query) use ($companyId) {
                $query->where('company_id', '=', $companyId);
            })
            ->get()
            ->pluck('survey_id')
            ->all();

        $surveys = Survey::CompanyId($companyId)
            ->whereNotIn('id', $answers)
            ->orderBy('updated_at', 'DESC')
            ->paginate();

        $surveysCompany = [];
        foreach ($surveys as $survey) {
            $surveysCompany[] = $survey['id'];
        }

        $surveyPositions = Survey::with([
            'Provinces',
            'cities',
            'areas',
            'routes',
            'customers'
        ])->get();

         // dd(auth()->id(),$provinces,$cities,$areas,$routes);
        $surveysFilter = [];
        foreach ($surveyPositions->toArray() as $surveyPosition) {

            if (
                count($surveyPosition['provinces']) &&
                count($surveyPosition['cities']) &&
                count($surveyPosition['areas']) &&
                count($surveyPosition['routes']) &&
                count($surveyPosition['customers'])
            ) {
                foreach ($surveyPosition['customers'] as $customer) {
                    if ($customer['id'] == auth()->id()) {


                        $surveysFilter[] = $surveyPosition['id'];
                        continue;
                    }
                }
            }else if (
                count($surveyPosition['provinces']) &&
                count($surveyPosition['cities']) &&
                count($surveyPosition['areas']) &&
                count($surveyPosition['routes'])
            ) {
                foreach ($surveyPosition['routes'] as $route) {
                    if (in_array($route['id'], $routes)) {
                        $surveysFilter[] = $surveyPosition['id'];
                        continue;
                    }
                }
            }

            else if (
                count($surveyPosition['provinces']) &&
                count($surveyPosition['cities']) &&
                count($surveyPosition['areas'])
            ) {
                foreach ($surveyPosition['areas'] as $area) {
                    if (in_array($area['id'], $areas)) {
                        $surveysFilter[] = $surveyPosition['id'];
                        continue;
                    }
                }
            }

           else if (
                count($surveyPosition['provinces']) &&
                count($surveyPosition['cities'])
            ) {
                foreach ($surveyPosition['cities'] as $city) {
                    if (in_array($city['id'], $cities)) {
                        $surveysFilter[] = $surveyPosition['id'];
                        continue;
                    }
                }
            }

            else if (
            count($surveyPosition['provinces'])
            ) {
                foreach ($surveyPosition['provinces'] as $province) {
                    if (in_array($province['id'], $provinces)) {
                        $surveysFilter[] = $surveyPosition['id'];
                        continue;
                    }
                }
            }

        }
	//dd($surveysFilter,$answers,$companyId,auth()->id());
        $date = date('Y-m-d', time());
        $surveys = Survey::CompanyId($companyId)->with([
         //   'Provinces',
          //  'cities',
         //   'areas',
         //   'routes',
          //  'customers'
        ])->whereNotIn('id', $answers)
            ->whereIn('id', $surveysFilter)
            ->where('from_date','<=',$date)
            ->where('to_date','>=',$date)
            ->orderBy('updated_at', 'DESC')
            ->paginate();

        return $surveys;
    }

    public function show($id)
    {
        /** @var Survey $survey */
        $survey = Survey::with([
          //  'Provinces',
         //   'cities',
          //  'areas',
          //  'routes',
           // 'customers',
            'questions',
          //  'answers',
          //  'answers.user',

        ])->findOrFail($id);

        SurveySeen::firstOrCreate([
            'user_id' => auth()->id(),
            'survey_id' => $survey->id,
        ]);

        return $survey;
    }

    public function store(StoreSurveyAnswerRequest $request, $id)
    {

        /** @var Survey $survey */
        $survey = Survey::findOrFail($id);


        /** @var User $company */
        $company = User::MyCompany()->findOrFail($survey->company_id);

        /** @var SurveyAnswer $answer */
        $answer = SurveyAnswer::firstOrNew([
            'survey_id' => $id,
            'user_id' => auth()->id(),
        ]);

        $answer->questions = $request->get('questions');

        $answer->save();

        return [
            'status' => true,
            'message' => trans('messages.api.customer.common.answer_store'),
        ];
    }
}
