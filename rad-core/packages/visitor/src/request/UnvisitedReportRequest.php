<?php

namespace Core\Packages\visitor\src\request;
use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize ;
use Illuminate\Validation\Rule;

class UnvisitedReportRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'visitor_id' => 'required|exists:users,id|' .
                Rule::unique('unvisited_report')
                    ->where('visitor_id', $this->visitor_id)
                    ->where('customer_id', $this->customer_id),
            'customer_id' => 'required|exists:users,id',
            'status' => 'required|in:visited,unvisited',
            'unvisited_report_id'=>'nullable|integer',
            'description' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'visitor_id.unique' => 'مشتری تعریف شده برای ویزیتور مورد نظر تکراری می باشد'
        ];
    }
}
