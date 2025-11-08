<?php

namespace Buzkall\TickTick\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            \Buzkall\TickTick\TickTickServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'TickTick' => \Buzkall\TickTick\Facades\TickTick::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('ticktick.client_id', 'test_client_id');
        $app['config']->set('ticktick.client_secret', 'test_client_secret');
        $app['config']->set('ticktick.redirect_uri', 'https://example.com/callback');
        $app['config']->set('ticktick.access_token', 'test_access_token');
    }
}
