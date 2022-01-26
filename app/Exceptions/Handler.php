<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     * @param \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Expands arrays with keys that have dot notation
     *
     * @param array $array
     *
     * @return array
     */
    private function expandDotNotationKeys(array $array)
    {
        $result = [];

        foreach ($array as $key => $value) {
            Arr::set($result, $key, $value);
        }

        return $result;
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        $jsonResponse = parent::invalidJson($request, $exception);

        $original = (array) $jsonResponse->getData();
        $jsonResponse->setData(array_merge($original, [
            'errors'        => $this->expandDotNotationKeys((array) $original['errors']),
        ]));

        return $jsonResponse;
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
        if ($exception instanceof NotFoundHttpException) {
            return response()->json(['message'=>trans('exceptions.route_not_found')], 404);
        } else if($exception instanceof AuthorizationException) {
            return response()->json(['message'=>trans('exceptions.access_denied')], 403);
        }

        return parent::render($request, $exception);
    }
}
