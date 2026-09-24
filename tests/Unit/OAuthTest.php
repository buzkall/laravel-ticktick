<?php

use Arzcode\TickTick\Exceptions\TickTickException;
use Arzcode\TickTick\TickTick;
use Arzcode\TickTick\TickTickClient;

test('it generates an authorization url from explicit arguments', function() {
    $client = new TickTickClient(['oauth_url' => 'https://ticktick.com']);

    $url = $client->getAuthorizationUrl('test_client_id', 'https://example.com/callback', 'tasks:read tasks:write', 'random_state');

    expect($url)
        ->toContain('https://ticktick.com/oauth/authorize')
        ->toContain('client_id=test_client_id')
        ->toContain('redirect_uri=' . urlencode('https://example.com/callback'))
        ->toContain('scope=' . urlencode('tasks:read tasks:write'))
        ->toContain('state=random_state')
        ->toContain('response_type=code');
});

test('it falls back to the configured credentials when building the authorization url', function() {
    $client = new TickTickClient([
        'client_id'    => 'config_client_id',
        'redirect_uri' => 'https://example.com/configured',
        'scope'        => 'tasks:read',
        'oauth_url'    => 'https://ticktick.com',
    ]);

    expect($client->getAuthorizationUrl())
        ->toContain('client_id=config_client_id')
        ->toContain('redirect_uri=' . urlencode('https://example.com/configured'))
        ->toContain('scope=tasks%3Aread');
});

test('it generates a random state when none is given', function() {
    $client = new TickTickClient(['client_id' => 'id', 'redirect_uri' => 'https://example.com/cb']);

    parse_str(parse_url($client->getAuthorizationUrl(), PHP_URL_QUERY), $first);
    parse_str(parse_url($client->getAuthorizationUrl(), PHP_URL_QUERY), $second);

    expect($first['state'])->not->toBeEmpty()
        ->and($first['state'])->not->toBe($second['state']);
});

test('it fails with a helpful message when the client id is missing', function() {
    (new TickTickClient(['redirect_uri' => 'https://example.com/cb']))->getAuthorizationUrl();
})->throws(TickTickException::class, 'Missing TickTick client_id');

test('it exchanges an authorization code sending scope and basic auth', function() {
    $ticktick = fakeTickTick([
        jsonResponse(['access_token' => 'live_token', 'refresh_token' => 'refresh_me', 'expires_in' => 15552000]),
    ], $history);

    $token = $ticktick->getAccessTokenFromCode('the_code');

    $request = recordedRequest($history);
    parse_str((string)$request->getBody(), $body);

    expect((string)$request->getUri())->toBe('https://ticktick.com/oauth/token')
        ->and($request->getMethod())->toBe('POST')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/x-www-form-urlencoded')
        ->and($request->getHeaderLine('Authorization'))->toBe('Basic ' . base64_encode('test_client_id:test_client_secret'))
        ->and($body)->toMatchArray([
            'grant_type'    => 'authorization_code',
            'code'          => 'the_code',
            'redirect_uri'  => 'https://example.com/callback',
            'scope'         => 'tasks:read tasks:write',
            'client_id'     => 'test_client_id',
            'client_secret' => 'test_client_secret',
        ])
        ->and($token['access_token'])->toBe('live_token');
});

test('it stores the tokens returned by the code exchange', function() {
    $ticktick = fakeTickTick([
        jsonResponse(['access_token' => 'live_token', 'refresh_token' => 'refresh_me']),
    ], $history, ['access_token' => null]);

    $ticktick->getAccessTokenFromCode('the_code');

    expect($ticktick->getAccessToken())->toBe('live_token')
        ->and($ticktick->getRefreshToken())->toBe('refresh_me');
});

test('it refreshes an access token', function() {
    $ticktick = fakeTickTick([
        jsonResponse(['access_token' => 'refreshed_token', 'refresh_token' => 'next_refresh']),
    ], $history, ['refresh_token' => 'stored_refresh']);

    $token = $ticktick->refreshAccessToken();

    $request = recordedRequest($history);
    parse_str((string)$request->getBody(), $body);

    expect((string)$request->getUri())->toBe('https://ticktick.com/oauth/token')
        ->and($request->getHeaderLine('Authorization'))->toBe('Basic ' . base64_encode('test_client_id:test_client_secret'))
        ->and($body)->toMatchArray([
            'grant_type'    => 'refresh_token',
            'refresh_token' => 'stored_refresh',
            'scope'         => 'tasks:read tasks:write',
        ])
        ->and($token['access_token'])->toBe('refreshed_token')
        ->and($ticktick->getAccessToken())->toBe('refreshed_token')
        ->and($ticktick->getRefreshToken())->toBe('next_refresh');
});

test('it fails when refreshing without a refresh token', function() {
    (new TickTick(['client_id' => 'id', 'client_secret' => 'secret']))->refreshAccessToken();
})->throws(TickTickException::class, 'A refresh token is required');

test('it wraps token endpoint errors in a ticktick exception', function() {
    $ticktick = fakeTickTick([
        jsonResponse(['error' => 'invalid_grant'], 400),
    ], $history);

    expect(fn() => $ticktick->getAccessTokenFromCode('bad_code'))
        ->toThrow(TickTickException::class, 'Failed to obtain access token');
});
