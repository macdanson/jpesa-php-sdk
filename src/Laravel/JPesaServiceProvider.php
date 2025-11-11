<?php

namespace JPesa\SDK\Laravel;

use Illuminate\Support\ServiceProvider;
use JPesa\SDK\JPesaClient;

class JPesaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/jpesa.php', 'jpesa');

        $this->app->singleton(JPesaClient::class, function ($app) {
            $config = $app['config']->get('jpesa');
            return new JPesaClient(
                baseUrl: $config['base_url'] ?? null,
                apiKey: $config['key'] ?? null,
                timeout: (float) ($config['timeout'] ?? 30.0)
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/jpesa.php' => config_path('jpesa.php'),
        ], 'config');
    }
}
