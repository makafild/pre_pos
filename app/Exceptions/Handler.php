<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $errorCode = 401 ;
        if( app()->environment() == 'production' ){
            if($exception instanceof NotFoundHttpException)
            {
                $errorCode = 404 ;
            }elseif($exception instanceof FatalErrorException ){
                $errorCode = 500 ;
            }
            elseif($exception instanceof MethodNotAllowedException ){
                $errorCode = 403 ;
            }
            return response()->view('errors.error', [
                'errorCode' => $errorCode
            ],$errorCode);
        }else{
            return parent::render($request, $exception);
        }
    }
}
