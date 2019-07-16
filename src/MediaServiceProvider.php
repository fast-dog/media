<?php

namespace FastDog\Media;


use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class MediaServiceProvider extends LaravelServiceProvider
{
    const NAME = 'media';

    /**
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->handleConfigs();
        $this->handleRoutes();
        $this->handleMigrations();

        $this->loadViewsFrom(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR, 'elfinder');

        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR =>
                base_path('resources/views/'),
        ]);

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {

        $this->app->register(MediaEventServiceProvider::class);
        $this->app->register(ElfinderServiceProviderFD::class);

        $this->app->alias('Image', \Intervention\Image\Facades\Image::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [];
    }


    /**
     * Определение конфигурации по умолчанию
     */
    private function handleConfigs(): void
    {
        $configPath = __DIR__ . '/../config/media.php';
        $this->publishes([$configPath => config_path('media.php')]);

        $this->mergeConfigFrom($configPath, self::NAME);
    }

    /**
     * Миграции базы данных
     */
    private function handleMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations/');
    }


    /**
     * Определение маршрутов пакета
     */
    private function handleRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
    }
}