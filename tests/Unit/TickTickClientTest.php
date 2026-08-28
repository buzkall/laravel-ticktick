<?php

use Buzkall\TickTick\Exceptions\TickTickException;
use Buzkall\TickTick\TickTickClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

test('it can set and get the access token', function() {
    $client = new TickTickClient([]);

    expect($client->setAccessToken('new_test_token'))->toBe($client)
        ->and($client->getAccessToken())->toBe('new_test_token');
});

test('it can set and get the refresh token', function() {
    $client = new TickTickClient([]);

    expect($client->setRefreshToken('new_refresh_token'))->toBe($client)
        ->and($client->getRefreshToken())->toBe('new_refresh_token');
});

test('it throws an exception when the access token is missing', function() {
    (new TickTickClient([]))->get('/test/endpoint');
})->throws(TickTickException::class, 'Access token is required');

test('it sends the bearer token on api requests', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->client()->get('https://api.ticktick.com/open/v1/project');

    expect(recordedRequest($history)->getHeaderLine('Authorization'))->toBe('Bearer test_access_token');
});

test('it forwards query parameters', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->client()->get('https://api.ticktick.com/open/v1/project', ['limit' => 10]);

    expect(recordedRequest($history)->getUri()->getQuery())->toBe('limit=10');
});

test('it returns an empty array for empty response bodies', function() {
    $ticktick = fakeTickTick([new Response(200, [], '')], $history);

    expect($ticktick->client()->get('https://api.ticktick.com/open/v1/anything'))->toBe([]);
});

test('it throws a ticktick exception instead of a type error on non json bodies', function() {
    $ticktick = fakeTickTick([new Response(200, ['Content-Type' => 'text/plain'], 'OK')], $history);

    expect(fn() => $ticktick->client()->get('https://api.ticktick.com/open/v1/anything'))
        ->toThrow(TickTickException::class, 'Failed to decode the TickTick API response as JSON');
});

test('it throws a ticktick exception instead of a fatal error on connection failures', function() {
    $ticktick = fakeTickTick([
        new ConnectException('cURL error 7: Failed to connect', new Request('GET', 'https://api.ticktick.com')),
    ], $history);

    expect(fn() => $ticktick->projects()->all())
        ->toThrow(TickTickException::class, 'Could not connect to the TickTick API');
});

test('it exposes the status code and body of failed responses', function() {
    $ticktick = fakeTickTick([jsonResponse(['errorCode' => 'unauthorized'], 401)], $history);

    try {
        $ticktick->projects()->all();
        $this->fail('Expected a TickTickException.');
    } catch (TickTickException $e) {
        expect($e->getStatusCode())->toBe(401)
            ->and($e->getResponseBody())->toContain('unauthorized')
            ->and($e->getMessage())->toContain('API request failed');
    }
});

test('it surfaces rate limit responses', function() {
    $ticktick = fakeTickTick([jsonResponse(['errorCode' => 'exceed_query_limit'], 429)], $history);

    try {
        $ticktick->projects()->all();
        $this->fail('Expected a TickTickException.');
    } catch (TickTickException $e) {
        expect($e->getStatusCode())->toBe(429);
    }
});

test('it does not send a body for payload free post requests', function() {
    $ticktick = fakeTickTick([new Response(200, [], '')], $history);

    $ticktick->client()->post('https://api.ticktick.com/open/v1/anything');

    expect((string)recordedRequest($history)->getBody())->toBe('');
});

test('it trims trailing slashes from the open api url', function() {
    expect((new TickTickClient(['open_api_url' => 'https://api.ticktick.com/open/v1/']))->getOpenApiUrl())
        ->toBe('https://api.ticktick.com/open/v1');
});
