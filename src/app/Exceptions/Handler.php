<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
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
        // $this->reportable(function (Throwable $e) {
        //     $this->handleException($request, $e);
        // });
    }
    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return Response|JsonResponse
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        $rendered = parent::render($request, $exception);
        $errorCode = md5('SearchApiClone:' . time());
        $this->logException($exception, $rendered, $errorCode);

        if ($rendered->getStatusCode() == 422) {
            return response()->json([
                'error' => $exception->getMessage(),
                'error_info' => [
                    'code' => $rendered->getStatusCode(),
                    'message' => $rendered->getContent(),
                    "data" => $rendered->getData(),
                ],
            ], $rendered->getStatusCode());
        }

        if (!env('APP_DEBUG', true)) {
            return response()->json([
                'error' => "[{$errorCode}]" . ($this->isHttpException($exception) ? $exception->getMessage(
                    ) : 'Server Error'),
            ], 500);
        }

        return response()->json([
            'error' => $exception->getMessage(),
            'error_info' => [
                'code' => $rendered->getStatusCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->map(function ($trace) {
                    return Arr::except($trace, ['args']);
                })->all()
            ],
        ], $rendered->getStatusCode());
    }
    public function logException(Throwable $e, $rendered, $errorCode)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        if (method_exists($e, 'report')) {
            if ($e->report() !== false) {
                return;
            }
        }

        Log::error("[SearchApiClone][{$errorCode}] {$e->getMessage()}", [
            'render' => $rendered,
            'ex' => $e,
        ]);
    }
}
