<?php

namespace BwtTeam\LaravelAPI\Http;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Router;

class Kernel extends HttpKernel
{
    /**
     * Create a new HTTP kernel instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @param  \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function __construct(Application $app, Router $router)
    {
        $this->middlewarePriority = array_prepend($this->middlewarePriority, \BwtTeam\LaravelAPI\Middleware\Api::class);

        return parent::__construct($app, $router);
    }
}