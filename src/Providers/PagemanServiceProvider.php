<?php

namespace Tsrgtm\Pageman\Providers; // Replace YourVendorName\Pageman

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // For potential authorization
use Illuminate\Support\Facades\Route; // For loading routes
use Tsrgtm\Pageman\Console\Commands\InstallPagemanCommand; // Replace YourVendorName\Pageman

class PagemanServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge the package's default configuration.
        // This allows users to only define the options they want to override
        // in their published copy of the config file.
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/pageman.php',
            'pageman' // 'pageman' is the key for config('pageman.key')
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the Artisan command for installation.
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallPagemanCommand::class,
            ]);
        }

        // Publishing configuration file.
        // This allows users to customize the package's configuration.
        $this->publishes([
            __DIR__ . '/../../config/pageman.php' => config_path('pageman.php'),
        ], 'pageman-config'); // Tag for selective publishing, e.g., php artisan vendor:publish --tag=pageman-config

        // Publishing database migrations.
        // This allows users to run the package's migrations.
        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'pageman-migrations');

        // Load migrations automatically if you don't want users to have to publish them.
        // $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Publishing views.
        // This allows users to customize the package's views.
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/pageman'),
        ], 'pageman-views');

        // Load views for the package.
        // This tells Laravel where to find the package's views.
        // The 'pageman' namespace allows you to use views like: view('pageman::some.view')
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'pageman');

        // Publishing assets (CSS, JS, images).
        // This allows users to publish static assets to their public directory.
        $this->publishes([
            __DIR__ . '/../../resources/assets' => public_path('vendor/pageman'),
        ], 'pageman-assets');

        // Publishing language files.
        // This allows users to override the package's translations.
        $this->publishes([
            __DIR__ . '/../../resources/lang' => $this->app->langPath('vendor/pageman'),
        ], 'pageman-lang');

        // Load translations for the package.
        // The 'pageman' namespace allows you to use translations like: __('pageman::messages.welcome')
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'pageman');

        // Load routes for the package.
        $this->registerRoutes();

        // Define Gates or Policies for authorization if your package needs them.
        // $this->defineGates();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        // Example: Grouping routes with a prefix and middleware from the config.
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php'); // For public-facing Pageman routes
            $this->loadRoutesFrom(__DIR__ . '/../../routes/admin.php'); // For Pageman admin panel routes
        });
    }

    /**
     * Get the Pageman route group configuration array.
     *
     * @return array
     */
    protected function routeConfiguration()
    {
        return [
            'prefix' => config('pageman.admin.route_prefix', 'pageman/admin'),
            'middleware' => [
                'web',
                \Tsrgtm\Pageman\Http\Middleware\PagemanAuthenticate::class // Using the class directly
                // Or, if you are sure the user has aliased it: 'pageman.auth'
            ],
            'as' => 'pageman.admin.'
        ];
    }

    /**
     * Define the Pageman authorization gates.
     *
     * @return void
     */
    protected function defineGates()
    {
        // Example Gate definition.
        // Pageman would use this to check if a user can access the admin panel.
        Gate::define('accessPagemanAdmin', function ($user) {
            // Assuming your User model has a canAccessPagemanAdmin method
            // as discussed in the trait setup.
            return $user && method_exists($user, 'canAccessPagemanAdmin') && $user->canAccessPagemanAdmin();
        });
    }
}
