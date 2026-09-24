<?php

use Arzcode\TickTick\TickTickClient;

test('it generates a pkce verifier and its s256 challenge', function() {
    $pkce = TickTickClient::generatePkceChallenge();

    expect($pkce)->toHaveKeys(['code_verifier', 'code_challenge']);

    // base64url: no padding, no + or /
    expect($pkce['code_verifier'])->toMatch('/^[A-Za-z0-9_-]+$/')
        ->and($pkce['code_challenge'])->toMatch('/^[A-Za-z0-9_-]+$/');

    // The challenge must be the base64url encoded SHA-256 of the verifier.
    $expected = rtrim(strtr(base64_encode(hash('sha256', $pkce['code_verifier'], true)), '+/', '-_'), '=');

    expect($pkce['code_challenge'])->toBe($expected);
});

test('it generates a different verifier each time', function() {
    expect(TickTickClient::generatePkceChallenge()['code_verifier'])
        ->not->toBe(TickTickClient::generatePkceChallenge()['code_verifier']);
});

test('it puts the challenge on the authorization url', function() {
    $client = new TickTickClient([
        'client_id'    => 'cid',
        'redirect_uri' => 'https://example.com/cb',
        'oauth_url'    => 'https://ticktick.com',
    ]);

    $url = $client->getAuthorizationUrl(state: 'st', codeChallenge: 'the_challenge');
    parse_str(parse_url($url, PHP_URL_QUERY), $query);

    expect($query)->toMatchArray([
        'client_id'             => 'cid',
        'response_type'         => 'code',
        'code_challenge'        => 'the_challenge',
        'code_challenge_method' => 'S256',
    ]);
});

test('it omits the pkce parameters when no challenge is given', function() {
    $client = new TickTickClient(['client_id' => 'cid', 'redirect_uri' => 'https://example.com/cb']);

    parse_str(parse_url($client->getAuthorizationUrl(), PHP_URL_QUERY), $query);

    expect($query)->not->toHaveKey('code_challenge')
        ->and($query)->not->toHaveKey('code_challenge_method');
});

test('it exchanges a pkce code without sending a client secret', function() {
    $ticktick = fakeTickTick([
        jsonResponse(['access_token' => 'pkce_token']),
    ], $history);

    $token = $ticktick->getAccessTokenFromPkceCode('the_code', 'the_verifier');

    $request = recordedRequest($history);
    parse_str((string)$request->getBody(), $body);

    expect($request->getHeaderLine('Authorization'))->toBe('')
        ->and($body)->toBe([
            'grant_type'    => 'authorization_code',
            'code'          => 'the_code',
            'redirect_uri'  => 'https://example.com/callback',
            'scope'         => 'tasks:read tasks:write',
            'code_verifier' => 'the_verifier',
            'client_id'     => 'test_client_id',
        ])
        ->and($body)->not->toHaveKey('client_secret')
        ->and($token['access_token'])->toBe('pkce_token');
});

test('it still sends basic auth for the confidential client flow', function() {
    $ticktick = fakeTickTick([jsonResponse(['access_token' => 'tok'])], $history);

    $ticktick->getAccessTokenFromCode('the_code');

    $request = recordedRequest($history);
    parse_str((string)$request->getBody(), $body);

    expect($request->getHeaderLine('Authorization'))->toBe('Basic ' . base64_encode('test_client_id:test_client_secret'))
        ->and($body)->toHaveKey('client_secret');
});

test('a personal api token is usable as an access token', function() {
    // The API Token from Settings > Account > API Token is sent exactly like an
    // OAuth access token, so no OAuth round trip is needed.
    $ticktick = fakeTickTick([jsonResponse([['id' => 'p1']])], $history, ['access_token' => null]);

    $ticktick->setAccessToken('personal_api_token');
    $ticktick->projects()->all();

    expect(recordedRequest($history)->getHeaderLine('Authorization'))->toBe('Bearer personal_api_token');
});
