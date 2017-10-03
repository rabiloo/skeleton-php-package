<?php

namespace Rabiloo\Skeleton;

use Illuminate\Support\ServiceProvider;

class SkeletonServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $namespace = 'skeleton';

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', $this->namespace);
        $this->loadViewsFrom(__DIR__ . '/../resources/views', $this->namespace);
        $this->loadRoutesFrom(__DIR__ . '/../config/routes.php');

        if ($this->app->runningInConsole()) {
            // load migrations
            $this->loadMigrationsFrom(__DIR__ . '/../migrations');

            // Publish package's config file
            $this->publishes(
                [__DIR__ . '/../config/config.php' => config_path("{$this->namespace}.php")],
                "{$this->namespace}-config"
            );

            // Publish package's language files
            $this->publishes(
                [__DIR__ . '/../resources/lang' => resource_path("lang/vendor/{$this->namespace}")],
                "{$this->namespace}-lang"
            );

            // Publish package's view files
            $this->publishes(
                [__DIR__ . '/../resources/views' => resource_path("views/vendor/{$this->namespace}")],
                "{$this->namespace}-views"
            );

            // Publish package's assets files
            $this->publishes(
                [__DIR__ . '/../public' => public_path("vendor/{$this->namespace}")],
                "{$this->namespace}-assets"
            );
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', $this->namespace);
    }
}
