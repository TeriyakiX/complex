<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

        $this->renderable(function (ValidationException $e, $request) {
            $errors = $e->errors();
            $errorCount = count($errors);

            $firstError = reset($errors)[0];

            $message = $firstError;

            if ($errorCount > 1) {
                $message .= ' (и еще ' . ($errorCount - 1) . ' ' . $this->getErrorWord($errorCount - 1) . ')';
            }

            // Ответ с ошибками
            return response()->json([
                'message' => $message,
                'errors'  => $errors,
            ], 400);
        });
    }

    protected function getErrorWord($count)
    {
        if ($count == 1) {
            return 'ошибка';
        }
        return 'ошибок';
    }
}
