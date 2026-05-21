<?php

namespace R124LEfendi\InstagramPublisher;

use Illuminate\Support\ServiceProvider;
use R124LEfendi\InstagramPublisher\Console\Commands\InstagramPostCommand;
use R124LEfendi\InstagramPublisher\Services\InstagramService;

class InstagramPublisherServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Instagram Service as a Singleton
        $this->app->singleton(InstagramService::class, function ($app) {
            return new InstagramService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load migrations from package database directory
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views from package resources directory
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'instagram-publisher');

        // Load web routes from package routes directory
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Register Console Command and Assets for CLI
        if ($this->app->runningInConsole()) {
            // Publish Views for independent customization
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/instagram-publisher'),
            ], 'instagram-publisher-views');

            // Register Artisan Command
            $this->commands([
                InstagramPostCommand::class,
            ]);
        }
    }
}
