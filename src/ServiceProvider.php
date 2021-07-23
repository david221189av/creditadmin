<?php

namespace Terranet\Administrator;

use Collective\Html\FormFacade;
use Collective\Html\HtmlFacade;
use Collective\Html\HtmlServiceProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Pingpong\Menus\MenuFacade;
use Pingpong\Menus\MenusServiceProvider;
use App\Providers\AdminServiceProvider;
use Terranet\Administrator\Providers\ArtisanServiceProvider;
use Terranet\Administrator\Providers\ContainersServiceProvider;
use Terranet\Administrator\Providers\EventServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $baseDir = realpath(__DIR__.'/../');

        // Publish & Load routes
        $this->publishes([
            "{$baseDir}/publishes/routes.php" => base_path('routes/admin.php'),
        ], 'routes');

        // Publish & Load configuration
        $this->publishes(["{$baseDir}/publishes/config.php" => config_path('administrator.php')], 'config');
        $this->mergeConfigFrom("{$baseDir}/publishes/config.php", 'administrator');

        // Publish Mix files
        $this->publishes(["{$baseDir}/publishes/mix" => base_path('adminarchitect-mix')], 'assets');

        // Publish & Load views, assets
        $this->publishes(["{$baseDir}/publishes/views" => base_path('resources/views/vendor/administrator')], 'views');
        $this->loadViewsFrom("{$baseDir}/publishes/views", 'administrator');

        // Publish & Load translations
        $this->publishes(
            ["{$baseDir}/publishes/translations" => base_path('resources/lang/vendor/administrator')],
            'translations'
        );
        $this->loadTranslationsFrom("{$baseDir}/publishes/translations", 'administrator');

        // Publish default Administrator Starter Kit: modules, dashboard panels, policies, etc...
        $this->publishes(
            ["{$baseDir}/publishes/Modules" => app_path('Http/Terranet/Administrator/Modules')],
            'boilerplate'
        );
        $this->publishes(
            ["{$baseDir}/publishes/Dashboard" => app_path('Http/Terranet/Administrator/Dashboard')],
            'boilerplate'
        );
        $this->publishes(
            ["{$baseDir}/publishes/Providers" => app_path('Providers')],
            'boilerplate'
        );

        $this->configureAuth();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $dependencies = [];

        // Ensure the ServiceProvider has been published
        if (file_exists(app_path("Providers/AdminServiceProvider.php"))) {
            $dependencies[] = AdminServiceProvider::class;
        }

        $dependencies = array_merge($dependencies, [
            ArtisanServiceProvider::class,
            ContainersServiceProvider::class,
            EventServiceProvider::class,
            HtmlServiceProvider::class => [
                'Html' => HtmlFacade::class,
                'Form' => FormFacade::class,
            ],
            MenusServiceProvider::class => [
                'AdminNav' => MenuFacade::class,
            ],
        ]);

        array_walk($dependencies, function ($package, $provider) {
            if (\is_string($package) && is_numeric($provider)) {
                $provider = $package;
                $package = null;
            }

            if (!$this->app->getProvider($provider)) {
                $this->app->register($provider);

                if (\is_array($package)) {
                    foreach ($package as $alias => $facade) {
                        if (class_exists($alias)) {
                            continue;
                        }

                        class_alias($facade, $alias);
                    }
                }
            }
        });
    }

    protected function configureAuth()
    {
        if (!config()->has('auth.guards.admin')) {
            config()->set('auth.guards.admin', [
                'driver' => 'session',
                'provider' => 'admins',
            ]);
        }

        if (!config()->has('auth.providers.admins')) {
            config()->set('auth.providers.admins', [
                'driver' => 'eloquent',
                'model' => config('administrator.auth.model'),
            ]);
        }
    }
}
