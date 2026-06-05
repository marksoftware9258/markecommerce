<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Custom error reporting (e.g., Sentry, Bugsnag)
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException(Throwable $e): \Illuminate\Http\JsonResponse
    {
        [$message, $status, $errors] = match (true) {
            $e instanceof ValidationException => [
                'Validation failed.',
                422,
                $e->errors(),
            ],
            $e instanceof AuthenticationException => [
                'Unauthenticated. Please login.',
                401,
                [],
            ],
            $e instanceof AuthorizationException,
            $e instanceof UnauthorizedException => [
                'You do not have permission to perform this action.',
                403,
                [],
            ],
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => [
                $this->getNotFoundMessage($e),
                404,
                [],
            ],
            $e instanceof ThrottleRequestsException => [
                'Too many requests. Please slow down.',
                429,
                [],
            ],
            $e instanceof HttpException => [
                $e->getMessage() ?: 'HTTP Error.',
                $e->getStatusCode(),
                [],
            ],
            default => [
                config('app.debug') ? $e->getMessage() : 'Server Error. Please try again.',
                500,
                config('app.debug') ? ['trace' => collect($e->getTrace())->take(5)->toArray()] : [],
            ],
        };

        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    private function getNotFoundMessage(Throwable $e): string
    {
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            return "{$model} not found.";
        }

        return 'Resource not found.';
    }
}
