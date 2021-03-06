<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponser;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
    * A list of the exception types that should not be reported.
    *
    * @var array
    */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
    * Report or log an exception.
    *
    * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
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
        if($exception instanceof ValidationException)
        {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
        {
            $modelName = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse("Does not exist any {$modelName} model",404);
        }

        if($exception instanceof AuthenticationException)
        {
            return $this->unauthenticated($request, $exception);
        }

        if($exception instanceof \Illuminate\Auth\Access\AuthorizationException)
        {
            return $this->errorResponse($exception->getMessage() , 403);
        }

        if($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException)
        {
            return $this->errorResponse('The specific URL cannot be found' , 404);
        }

        if($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException)
        {
            return $this->errorResponse('The specific method is invalid' , 405);
        }

        if($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException)
        {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        if($exception instanceof \Illuminate\Database\QueryException)
        {
            $errorCode = $exception->errorInfo[1];
            if($errorCode == 1451)
            {
                return $this->errorResponse('Can not delete this resource it is related to other resource' , 409);
            }
        }

        if(config('app.debug'))
        {
            return parent::render($request, $exception);
        }
        return $this->errorResponse('Unexpected exception. Try later' , 500);

    }

    /**
    * Convert an authentication exception into an unauthenticated response.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Illuminate\Auth\AuthenticationException  $exception
    * @return \Illuminate\Http\Response
    */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse('Unauthenticated',422);
    }

    /**
    * Create a response object from the given validation exception.
    *
    * @param  \Illuminate\Validation\ValidationException  $e
    * @param  \Illuminate\Http\Request  $request
    * @return \Symfony\Component\HttpFoundation\Response
    */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();
        return $this->errorResponse($errors,422);
    }

}
