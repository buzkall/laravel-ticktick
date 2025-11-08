<?php

namespace Buzkall\TickTick;

use Buzkall\TickTick\Exceptions\TickTickException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TickTickClient
{
    protected Client $client;
    protected ?string $accessToken;
    protected string $baseUrl;
    protected string $openApiUrl;
    protected string $oauthUrl;

    public function __construct(array $config = [])
    {
        $this->accessToken = $config['access_token'] ?? null;
        $this->baseUrl = $config['base_url'] ?? 'https://api.ticktick.com';
        $this->openApiUrl = $config['open_api_url'] ?? 'https://api.ticktick.com/open/v1';
        $this->oauthUrl = $config['oauth_url'] ?? 'https://ticktick.com';

        $this->client = new Client([
            'timeout' => $config['timeout'] ?? 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ]);
    }

    public function setAccessToken(string $token): self
    {
        $this->accessToken = $token;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function getOpenApiUrl(): string
    {
        return rtrim($this->openApiUrl, '/');
    }

    public function getAuthorizationUrl(string $clientId, string $redirectUri, string $scope = 'tasks:read tasks:write', string $state = ''): string
    {
        $params = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'scope'         => $scope,
            'state'         => $state ?: bin2hex(random_bytes(16)),
            'response_type' => 'code',
        ]);

        return "{$this->oauthUrl}/oauth/authorize?{$params}";
    }

    public function getAccessTokenFromCode(string $code, string $clientId, string $clientSecret, string $redirectUri): array
    {
        try {
            $response = $this->client->post("{$this->oauthUrl}/oauth/token", [
                'form_params' => [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'code'          => $code,
                    'grant_type'    => 'authorization_code',
                    'redirect_uri'  => $redirectUri,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['access_token'])) {
                $this->setAccessToken($data['access_token']);
            }

            return $data;
        } catch (GuzzleException $e) {
            throw new TickTickException('Failed to obtain access token: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function request(string $method, string $endpoint, array $options = []): array
    {
        if (! $this->accessToken) {
            throw new TickTickException('Access token is required. Please authenticate first.');
        }

        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            ['Authorization' => "Bearer {$this->accessToken}"]
        );

        try {
            $response = $this->client->request($method, $endpoint, $options);
            $content = $response->getBody()->getContents();

            return $content ? json_decode($content, true) : [];
        } catch (GuzzleException $e) {
            $message = $e->getMessage();
            if ($e->hasResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $message .= ' Response: ' . $errorBody;
            }

            throw new TickTickException('API request failed: ' . $message, $e->getCode(), $e);
        }
    }

    public function get(string $endpoint, array $query = []): array
    {
        $options = [];
        if (! empty($query)) {
            $options['query'] = $query;
        }

        return $this->request('GET', $endpoint, $options);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }
}
