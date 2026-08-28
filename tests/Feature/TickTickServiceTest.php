<?php

use Buzkall\TickTick\Facades\TickTick as TickTickFacade;
use Buzkall\TickTick\Resources\ProjectResource;
use Buzkall\TickTick\Resources\TaskResource;
use Buzkall\TickTick\TickTick;
use Buzkall\TickTick\TickTickClient;

test('it exposes the client and the resources', function() {
    $ticktick = new TickTick(['access_token' => 'test_token']);

    expect($ticktick->client())->toBeInstanceOf(TickTickClient::class)
        ->and($ticktick->tasks())->toBeInstanceOf(TaskResource::class)
        ->and($ticktick->projects())->toBeInstanceOf(ProjectResource::class);
});

test('it can set the access and refresh tokens', function() {
    $ticktick = new TickTick([]);

    $ticktick->setAccessToken('new_token')->setRefreshToken('new_refresh');

    expect($ticktick->getAccessToken())->toBe('new_token')
        ->and($ticktick->getRefreshToken())->toBe('new_refresh');
});

test('it resolves from the service container', function() {
    expect(app(TickTick::class))->toBeInstanceOf(TickTick::class)
        ->and(app('ticktick'))->toBe(app(TickTick::class));
});

test('it can use the facade', function() {
    expect(TickTickFacade::getFacadeRoot())->toBeInstanceOf(TickTick::class)
        ->and(TickTickFacade::projects())->toBeInstanceOf(ProjectResource::class);
});

test('it builds the container instance from the published config', function() {
    config()->set('ticktick.scope', 'tasks:read');

    app()->forgetInstance(TickTick::class);

    $client = app(TickTick::class)->client();

    expect($client->getAccessToken())->toBe('test_access_token')
        ->and($client->getRefreshToken())->toBe('test_refresh_token')
        ->and($client->getScope())->toBe('tasks:read')
        ->and($client->getAuthorizationUrl())->toContain('client_id=test_client_id');
});

test('it publishes the config file', function() {
    expect(config('ticktick.open_api_url'))->toBe('https://api.ticktick.com/open/v1')
        ->and(config('ticktick.oauth_url'))->toBe('https://ticktick.com');
});
