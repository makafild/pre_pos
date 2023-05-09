<?php


namespace Core\System\Exceptions;


use Exception;

class CoreExceptionOk extends Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return object
     */
    public function render($request)
    {
        return $this->sendJsonError($this->message);
    }

    public function sendJsonError($message)
    {
        return response()->json([
            'status' => true,
            'message' =>$message
        ], 200);
    }

}
