<?php

namespace BwtTeam\LaravelAPI\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use BwtTeam\LaravelAPI\Response\ApiResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;

class LumenHandler extends ExceptionHandler
{
    const TYPE_WEB = 1;
    const TYPE_API = 2;

    /**
     * @var int Response type
     */
    protected $type = self::TYPE_WEB;

    /**
     * Get the type of response
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type of response
     *
     * @param int $type Response type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Render an exception into a response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if ($this->type == self::TYPE_API) {
            $e = $this->prepareException($e);
            $statusCode = $this->recognizeStatusCode($e);
            return new ApiResponse($e, $statusCode);
        }
        return parent::render($request, $e);
    }

    /**
     * Prepare exception for rendering.
     *
     * @param  \Exception  $e
     * @return \Exception
     */
    protected function prepareException(Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        } elseif ($e instanceof AuthorizationException) {
            $e = new HttpException(403, $e->getMessage());
        }

        return $e;
    }

    /**
     * Recognition response code from Exception
     *
     * @param Exception $e
     *
     * @return int
     */
    protected function recognizeStatusCode(Exception $e)
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        } else if ($e instanceof AuthenticationException) {
            return 401;
        } else if ($e instanceof ValidationException) {
            return 422;
        }

        $e = FlattenException::create($e);

        return $e->getStatusCode();
    }
}
