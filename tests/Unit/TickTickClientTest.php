<?php

use Buzkall\TickTick\Exceptions\TickTickException;
use Buzkall\TickTick\TickTickClient;

beforeEach(function() {
    $this->client = new TickTickClient([
        'access_token' => 'test_token',
        'base_url'     => 'https://api.ticktick.com',
        'open_api_url' => 'https://api.ticktick.com/open/v1',
        'oauth_url'    => 'https://ticktick.com',
    ]);
});

test('it can set and get access token', function() {
    $token = 'new_test_token';
    $this->client->setAccessToken($token);

    expect($this->client->getAccessToken())->toBe($token);
});

test('it generates authorization url', function() {
    $clientId = 'test_client_id';
    $redirectUri = 'https://example.com/callback';
    $scope = 'tasks:read tasks:write';
    $state = 'random_state';

    $url = $this->client->getAuthorizationUrl($clientId, $redirectUri, $scope, $state);

    expect($url)
        ->toContain('https://ticktick.com/oauth/authorize')
        ->toContain('client_id=' . $clientId)
        ->toContain('redirect_uri=' . urlencode($redirectUri))
        ->toContain('scope=' . urlencode($scope))
        ->toContain('state=' . $state)
        ->toContain('response_type=code');
});

test('it throws exception when access token is missing', function() {
    $client = new TickTickClient([]);

    $client->get('/test/endpoint');
})->throws(TickTickException::class, 'Access token is required');
