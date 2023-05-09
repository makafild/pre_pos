<?php

namespace Core\Packages\tour_visit\src\request;

use Core\System\Http\Requests\FormRequestCustomize;

class TourVisitorsStoreRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tour_visits'        => 'required|array',
            'tour_visits.*.visitor_id'        => 'required|numeric|exists:users,id',
            'tour_visits.*.routes'        => 'required|array',
            'tour_visits.*.routes.*.id'        => 'numeric|required|exists:routes,id',
            'tour_visits.*.routes.*.dates' => 'required|array',
            'tour_visits.*.routes.*.dates.*' => 'required|numeric'
        ];
    }
}
