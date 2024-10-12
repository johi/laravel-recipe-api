<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions as BaseExceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler
{
    protected int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK;

    public function __invoke(BaseExceptions $exceptions): BaseExceptions
    {
        // The most generic exceptions go last
        $this->renderUnauthorized($exceptions);
        $this->renderNotFound($exceptions);
        $this->renderValidation($exceptions);
        $this->renderGeneric($exceptions);

        return $exceptions;
    }

    protected function renderUnauthorized(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(
            fn (AuthenticationException $e) => $this->response(
                messages: __('Unauthorized'),
                code: 401,
            )
        );
    }

    protected function renderNotFound(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(
            fn (NotFoundHttpException $e) => $this->response(
                messages: __(':resource cannot be found.', [
                    'resource' => ucfirst(Str::afterLast($e->getPrevious()?->getModel(), '\\')) ?: 'Resource',
                ]),
                code: 404,
            )
        );
    }

    protected function renderValidation(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(function (ValidationException $e) {
            $errors = [];

            foreach ($e->errors() as $key => $value) {
                foreach ($value as $message) {
                    $errors[] = [
                        'message' => $message,
                        'source' => $key,
                    ];
                }
            }

            return $this->response(
                messages: $errors,
                code: 422,
            );
        });
    }

    protected function renderGeneric(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(
            fn (\Throwable $e) => $this->response(
                messages: __('Unknown error'),
                code: 400,
            )
        );
    }

    protected function response(string|array $messages, int $code): JsonResponse
    {
        if (is_string($messages)) {
            $messages = [
                [
                    'message' => $messages,
                    'source' => null,
                ]
            ];
        }

        return response()->json(
            data: [
                'message' => $messages,
                'status' => $code,
            ],
            options: $this->jsonFlags,
        );
    }
}