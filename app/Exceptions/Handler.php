<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Traits\ApiResponses;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions as BaseExceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler
{
    use ApiResponses;

    public function __invoke(BaseExceptions $exceptions): BaseExceptions
    {
        // The most generic exceptions go last
        $this->renderUnauthorized($exceptions);
        $this->renderUnauthenticated($exceptions);
        $this->renderNotFound($exceptions);
        $this->renderValidation($exceptions);
        $this->renderGeneric($exceptions);
        return $exceptions;
    }

    protected function renderUnauthorized(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(
            fn (AccessDeniedHttpException $e) => $this->error(
                __('Unauthorized'),
                403,
            )
        );
    }

    protected function renderUnauthenticated(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(
            fn (AuthenticationException $e) => $this->error(
                __('Forbidden'),
                401,
            )
        );
    }

    protected function renderNotFound(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(
            fn (NotFoundHttpException $e) => $this->error(
                __(':resource cannot be found.', [
                    'resource' => ucfirst(Str::afterLast($e->getPrevious()?->getModel(), '\\')) ?: 'Resource',
                ]),
                404,
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

            return $this->error(
                $errors,
                400,
            );
        });
    }

    protected function renderGeneric(BaseExceptions $exceptions): void
    {
        $exceptions->renderable(
            fn (\Throwable $e) => $this->error(
                 __('Unknown error'),
                 400,
            )
        );
    }
}
