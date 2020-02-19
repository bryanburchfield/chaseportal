<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapAppRoutes();
        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the application's "web" routes.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapAppRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/admindurationdashboard.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/admindistinctagentdashboard.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/agentdashboard.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/agentoutbounddashboard.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/inbounddashboard.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/outbounddashboard.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/trenddashboard.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/leaderdashboard.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/kpi.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/dashboards.php'));

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/tools.php'));
    }
}
