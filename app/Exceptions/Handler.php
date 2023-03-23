<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        return $this->jsonRender($request, $e);
    }

    protected function jsonRender(Request $request, \Throwable $exception): \Illuminate\Http\JsonResponse
    {
        $error_data = $exception->getMessage();

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $error_data = $exception->errors();
        }

        $error_trace = null;

        if (config('app.debug')) {
            $error_trace = json_decode(json_encode($exception->getTrace(), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        }

        return \response()->json([
            'error' => $error_data,
            'trace' => $error_trace,
        ], 400);
    }
}
