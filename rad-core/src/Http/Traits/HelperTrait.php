<?php

namespace Core\System\Http\Traits;

use Core\System\Exceptions\CoreException;

trait HelperTrait
{
    public $_pagination = 9999;

    public $_COLOR = [
        "primary",
        "warning",
        "success",
        "danger",
        "info",
    ];

    public function show($id)
    {
        $result = Routes::find($id);
        if (!isset($result)) {
            throw new CoreException(' شناسه ' . $id . ' یافت نشد');
        }
        return $this->modelResponse(['data' => $result]);
    }

    public function insertRow($payload)
    {

        try {
            $response = $this->create($payload);
            return $this->modelResponse(['data' => $response, 'type' => 'create']);
        } catch (\Exception $e) {
            return $this->errorHandler($e->getMessage());
        }
    }

    public function destroyRow($id)
    {

        try {
            if (is_array($id)) {
                $record = $this->whereIn('id', $id);
            }
            if ($this->ISCompany())
                $record->where('company_id', $this->ISCompany());

            $record = $record->get();

            if (count($record) < 1) {
                throw new CoreException(' شناسه های ' . implode(",", $id) . ' یافت نشد');
            }

            $response = $record->each->delete();
            return $this->modelResponse(['data' => $response]);
        } catch (\Exception $e) {
            return $this->errorHandler($e->getMessage());
        }
    }

    public function updateRow($payload, $id,$company=true)
    {
        $record = $this->where('id',$id);

        if($company){
        if ($this->ISCompany())
        $record->where('company_id', $this->ISCompany());
        }

        $record=$record->first();

        if (!isset($record)) {
            throw new CoreException(' شناسه ' . $id . ' یافت نشد');
        }
        try {
            $record->update($payload);
            return $this->modelResponse(['data' => $record]);
        } catch (\Exception $e) {
            return $this->errorHandler($e);
        }
    }
    public function modelResponse($data,$message = '', $type = false, $error = false )
    {
        $messages = $message;
        if (!empty($error) && $error == true) {
            $messages = $message;
        }
        // if (!empty($messages) && !is_array($messages)) {
        //     $messages = [
        //         $messages
        //     ];
        // }
        return (object)[
            'message' => !empty($messages) ? $messages : [],
            'type' => $type,
            'count' => !empty($data['count']) ? $data['count'] : 0,
            'result' => (!empty($data['data'])) ? $data['data'] : []
        ];
    }

    // public function modelResponse($data,$message = '', $type = false, $error = false )
    // {
    //     if (!empty($error) && $error == true) {
    //         $messages = $message;
    //     }
    //     if (!empty($messages) && !is_array($messages)) {
    //         $messages = [
    //             $messages
    //         ];
    //     }
    //     return (object)[
    //         'message' => !empty($messages) ? $messages : [],
    //         'type' => $type,
    //         'count' => !empty($data['count']) ? $data['count'] : 0,
    //         'result' => (!empty($data['data'])) ? $data['data'] : []
    //     ];
    // }

    public function errorHandler($e)
    {
        if (isset($e->errorInfo)) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                $validations = \Lang::get('validation');
                preg_match("#for\skey\s'(\w+)'#", $e->getMessage(), $fields);
                if (!empty($fields)) {
                    $column = str_replace('_unique', '', $fields[1]);
                    if (isset($column, $validations['uniques'][$column])) {
                        throw new CoreException("فیلد {$validations['uniques'][$column]} تکراری می باشد");
                    } else {
                        throw new CoreException("فیلد مورد نظر تکراری می باشد");
                    }
                }
            }
        }
        throw new CoreException($e);
    }

    private function ISCompany()
    {
        if (auth('api')->user()['kind'] == 'admin')
            return 0;
        else
            return auth('api')->user()->company_id;
    }
}
