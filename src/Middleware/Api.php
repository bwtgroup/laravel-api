<?php

namespace BwtTeam\LaravelAPI\Middleware;

use BwtTeam\LaravelAPI\Exceptions\LumenHandler;
use Closure;
use BwtTeam\LaravelAPI\Exceptions\Handler;
use \Illuminate\Contracts\Debug\ExceptionHandler;

class Api
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $exceptionHandler = app(ExceptionHandler::class);
        if ($exceptionHandler instanceof Handler || $exceptionHandler instanceof LumenHandler) {
            $exceptionHandler->setType($exceptionHandler::TYPE_API);
        }
        return $next($request);
    }
}
