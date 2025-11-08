<?php

use Buzkall\TickTick\Facades\TickTick as TickTickFacade;
use Buzkall\TickTick\TickTick;

test('it can instantiate ticktick class', function() {
    $ticktick = new TickTick([
        'access_token' => 'test_token',
    ]);

    expect($ticktick)->toBeInstanceOf(TickTick::class);
});

test('it provides access to client', function() {
    $ticktick = new TickTick([
        'access_token' => 'test_token',
    ]);

    expect($ticktick->client())->toBeInstanceOf(\Buzkall\TickTick\TickTickClient::class);
});

test('it provides access to task resource', function() {
    $ticktick = new TickTick([
        'access_token' => 'test_token',
    ]);

    expect($ticktick->tasks())->toBeInstanceOf(\Buzkall\TickTick\Resources\TaskResource::class);
});

test('it provides access to project resource', function() {
    $ticktick = new TickTick([
        'access_token' => 'test_token',
    ]);

    expect($ticktick->projects())->toBeInstanceOf(\Buzkall\TickTick\Resources\ProjectResource::class);
});

test('it can set access token', function() {
    $ticktick = new TickTick([]);
    $token = 'new_token';

    $ticktick->setAccessToken($token);

    expect($ticktick->client()->getAccessToken())->toBe($token);
});

test('it resolves from service container', function() {
    $ticktick = app(TickTick::class);

    expect($ticktick)->toBeInstanceOf(TickTick::class);
});

test('it can use facade', function() {
    expect(TickTickFacade::getFacadeRoot())->toBeInstanceOf(TickTick::class);
});
