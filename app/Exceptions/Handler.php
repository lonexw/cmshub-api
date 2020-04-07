<?php

namespace App\Exceptions;

use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $message = $exception->getMessage() ?: '位置错误';

        if ($exception instanceof \App\Exceptions\GraphQLException) {
            if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
                return response()->json(['errmsg' => $message, 'errcode' => $exception->getCode()]);
            }
        }

        if ($exception instanceof \App\Exceptions\MyException) {
            if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
                return response()->json(['errmsg' => $message, 'errcode' => $exception->getCode()]);
            }
        }

        if ($exception instanceof NotFoundHttpException) {
            if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
                return response()->json(['errmsg' => 'not found', 'errcode' => 404]);
            }
        }

        if ($exception instanceof ValidationException) {
            if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
                return response()->json(['errmsg' => $exception->errors(), 'errcode' => 422]);
            }
        }

        return parent::render($request, $exception);
    }
}
