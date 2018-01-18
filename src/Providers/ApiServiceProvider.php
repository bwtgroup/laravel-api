<?php

namespace BwtTeam\LaravelAPI\Providers;

use BwtTeam\LaravelAPI\Debugger;
use BwtTeam\LaravelAPI\Response\ApiResponse;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();

        // Enable debugger when debug = true
        if ($this->app['config']['app.debug'])
        {
            $this->app->make(Debugger::class)->collectDatabaseQueries();
        }

        if(!$this->isLumen()) {
            $this->app->make('Illuminate\Contracts\Routing\ResponseFactory')->macro('api', function() {
                return call_user_func_array([ApiResponse::class, 'create'], func_get_args());
            });
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Debugger::class);
    }

    /**
     * Setup the config.
     */
    protected function setupConfig()
    {
        $configPath = __DIR__ . '/../../config/api.php';

        if (function_exists('config_path')) {
            $publishPath = config_path('api.php');
        } else {
            $publishPath = base_path('config/api.php');
        }

        $this->publishes([$configPath => $publishPath], 'config');
        $this->mergeConfigFrom($configPath, 'api');
    }

    /**
     * Check if package is running under Lumen app.
     *
     * @return bool
     */
    protected function isLumen()
    {
        return is_a(\app(), 'Laravel\Lumen\Application');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Debugger::class
        ];
    }
}
