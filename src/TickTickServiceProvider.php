<?php

namespace Buzkall\TickTick;

use Illuminate\Support\ServiceProvider;

class TickTickServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ticktick.php', 'ticktick');

        $this->app->singleton(TickTick::class, function($app) {
            $config = $app['config']['ticktick'];

            return new TickTick([
                'client_id'     => $config['client_id'] ?? null,
                'client_secret' => $config['client_secret'] ?? null,
                'redirect_uri'  => $config['redirect_uri'] ?? null,
                'access_token'  => $config['access_token'] ?? null,
                'base_url'      => $config['base_url'] ?? 'https://api.ticktick.com',
                'open_api_url'  => $config['open_api_url'] ?? 'https://api.ticktick.com/open/v1',
                'oauth_url'     => $config['oauth_url'] ?? 'https://ticktick.com',
                'timeout'       => $config['timeout'] ?? 30,
            ]);
        });

        $this->app->alias(TickTick::class, 'ticktick');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ticktick.php' => config_path('ticktick.php'),
            ], 'ticktick-config');
        }
    }

    public function provides(): array
    {
        return [TickTick::class, 'ticktick'];
    }
}
