<?php

namespace Arzcode\TickTick;

use Arzcode\TickTick\Exceptions\TickTickException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class TickTickClient
{
    /**
     * Default scopes requested during the OAuth flow.
     *
     * TickTick requires the scope on both the authorization request and the
     * token exchange, otherwise the token endpoint answers with a 400.
     */
    public const DEFAULT_SCOPE = 'tasks:read tasks:write';

    protected Client $client;
    protected ?string $accessToken;
    protected ?string $refreshToken;
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $redirectUri;
    protected string $scope;
    protected string $baseUrl;
    protected string $openApiUrl;
    protected string $oauthUrl;

    public function __construct(array $config = [])
    {
        $this->accessToken = $config['access_token'] ?? null;
        $this->refreshToken = $config['refresh_token'] ?? null;
        $this->clientId = $config['client_id'] ?? null;
        $this->clientSecret = $config['client_secret'] ?? null;
        $this->redirectUri = $config['redirect_uri'] ?? null;
        $this->scope = $config['scope'] ?? self::DEFAULT_SCOPE;
        $this->baseUrl = $config['base_url'] ?? 'https://api.ticktick.com';
        $this->openApiUrl = $config['open_api_url'] ?? 'https://api.ticktick.com/open/v1';
        $this->oauthUrl = $config['oauth_url'] ?? 'https://ticktick.com';

        $clientConfig = [
            'timeout' => $config['timeout'] ?? 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ];

        // Allows tests (and advanced users) to plug in their own Guzzle handler.
        if (isset($config['handler'])) {
            $clientConfig['handler'] = $config['handler'];
        }

        $this->client = new Client($clientConfig);
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

    public function setRefreshToken(string $token): self
    {
        $this->refreshToken = $token;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getOpenApiUrl(): string
    {
        return rtrim($this->openApiUrl, '/');
    }

    /**
     * Build the URL the user has to visit to authorize the application.
     *
     * The client id, redirect uri and scope fall back to the values coming from
     * config/ticktick.php, so they only need to be passed explicitly when
     * overriding them.
     */
    public function getAuthorizationUrl(?string $clientId = null, ?string $redirectUri = null, ?string $scope = null, string $state = '', ?string $codeChallenge = null): string
    {
        $params = $this->filterNulls([
            'client_id'             => $this->requireCredential($clientId ?? $this->clientId, 'client_id'),
            'redirect_uri'          => $this->requireCredential($redirectUri ?? $this->redirectUri, 'redirect_uri'),
            'scope'                 => $scope ?? $this->scope,
            'state'                 => $state ?: bin2hex(random_bytes(16)),
            'response_type'         => 'code',
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => $codeChallenge === null ? null : 'S256',
        ]);

        return "{$this->oauthUrl}/oauth/authorize?" . http_build_query($params);
    }

    /**
     * Generate a PKCE verifier and its S256 challenge.
     *
     * Store the verifier (in the session, for instance) between the redirect
     * and the callback; the challenge goes on the authorization URL.
     *
     * @return array{code_verifier: string, code_challenge: string}
     */
    public static function generatePkceChallenge(): array
    {
        $verifier = self::base64UrlEncode(random_bytes(32));

        return [
            'code_verifier'  => $verifier,
            'code_challenge' => self::base64UrlEncode(hash('sha256', $verifier, true)),
        ];
    }

    protected static function base64UrlEncode(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    /**
     * Exchange the authorization code returned by TickTick for an access token.
     */
    public function getAccessTokenFromCode(string $code, ?string $clientId = null, ?string $clientSecret = null, ?string $redirectUri = null, ?string $scope = null, ?string $codeVerifier = null): array
    {
        return $this->requestToken($this->filterNulls([
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->requireCredential($redirectUri ?? $this->redirectUri, 'redirect_uri'),
            'scope'         => $scope ?? $this->scope,
            'code_verifier' => $codeVerifier,
        ]), $clientId, $clientSecret);
    }

    /**
     * Exchange an authorization code obtained through the PKCE flow.
     *
     * PKCE is for public clients, so no client secret is sent.
     */
    public function getAccessTokenFromPkceCode(string $code, string $codeVerifier, ?string $clientId = null, ?string $redirectUri = null, ?string $scope = null): array
    {
        return $this->requestToken([
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->requireCredential($redirectUri ?? $this->redirectUri, 'redirect_uri'),
            'scope'         => $scope ?? $this->scope,
            'code_verifier' => $codeVerifier,
        ], $clientId, null, false);
    }

    /**
     * Exchange a refresh token for a fresh access token.
     *
     * TickTick access tokens expire, so long lived integrations need to call
     * this before the stored token becomes invalid.
     */
    public function refreshAccessToken(?string $refreshToken = null, ?string $clientId = null, ?string $clientSecret = null, ?string $scope = null): array
    {
        $refreshToken = $refreshToken ?? $this->refreshToken;

        if (! $refreshToken) {
            throw new TickTickException('A refresh token is required to refresh the access token.');
        }

        return $this->requestToken([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
            'scope'         => $scope ?? $this->scope,
        ], $clientId, $clientSecret);
    }

    /**
     * Perform a request against the TickTick OAuth token endpoint.
     *
     * The credentials are sent both as HTTP Basic auth (as described in the
     * TickTick documentation) and as form parameters, which keeps the request
     * compatible with either expectation.
     */
    protected function requestToken(array $params, ?string $clientId = null, ?string $clientSecret = null, bool $confidential = true): array
    {
        $clientId = $this->requireCredential($clientId ?? $this->clientId, 'client_id');
        $options = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        ];

        $params['client_id'] = $clientId;

        if ($confidential) {
            // Confidential clients authenticate with their secret, sent both as
            // HTTP Basic auth and as form parameters.
            $clientSecret = $this->requireCredential($clientSecret ?? $this->clientSecret, 'client_secret');
            $options['auth'] = [$clientId, $clientSecret];
            $params['client_secret'] = $clientSecret;
        }

        try {
            $response = $this->client->post("{$this->oauthUrl}/oauth/token", $options + ['form_params' => $params]);

            $data = $this->decodeBody($response->getBody()->getContents());

            if (isset($data['access_token'])) {
                $this->setAccessToken($data['access_token']);
            }

            if (isset($data['refresh_token'])) {
                $this->setRefreshToken($data['refresh_token']);
            }

            return $data;
        } catch (ConnectException $e) {
            throw new TickTickException('Could not connect to the TickTick API: ' . $e->getMessage(), (int)$e->getCode(), $e);
        } catch (RequestException $e) {
            throw $this->exceptionFromRequestException('Failed to obtain access token', $e);
        } catch (GuzzleException $e) {
            throw new TickTickException('Failed to obtain access token: ' . $e->getMessage(), (int)$e->getCode(), $e);
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

            return $this->decodeBody($response->getBody()->getContents());
        } catch (ConnectException $e) {
            throw new TickTickException('Could not connect to the TickTick API: ' . $e->getMessage(), (int)$e->getCode(), $e);
        } catch (RequestException $e) {
            throw $this->exceptionFromRequestException('API request failed', $e);
        } catch (GuzzleException $e) {
            throw new TickTickException('API request failed: ' . $e->getMessage(), (int)$e->getCode(), $e);
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
        // Endpoints such as "complete" take no payload at all, so an empty
        // array must not be serialized into a bogus "[]" body.
        return $this->request('POST', $endpoint, empty($data) ? [] : ['json' => $data]);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, empty($data) ? [] : ['json' => $data]);
    }

    public function delete(string $endpoint, array $query = []): array
    {
        $options = [];
        if (! empty($query)) {
            $options['query'] = $query;
        }

        return $this->request('DELETE', $endpoint, $options);
    }

    /**
     * Decode a response body, tolerating the empty bodies TickTick returns for
     * endpoints such as delete and complete.
     */
    protected function decodeBody(string $content): array
    {
        if (trim($content) === '') {
            return [];
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TickTickException(
                'Failed to decode the TickTick API response as JSON (' . json_last_error_msg() . '). Body: ' . mb_substr($content, 0, 500)
            );
        }

        if ($decoded === null) {
            return [];
        }

        if (! is_array($decoded)) {
            throw new TickTickException('Unexpected TickTick API response, expected a JSON object or array. Body: ' . mb_substr($content, 0, 500));
        }

        return $decoded;
    }

    protected function exceptionFromRequestException(string $prefix, RequestException $e): TickTickException
    {
        $message = $e->getMessage();
        $statusCode = null;
        $responseBody = null;

        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $message .= ' Response: ' . $responseBody;
        }

        return new TickTickException($prefix . ': ' . $message, (int)$e->getCode(), $e, $statusCode, $responseBody);
    }

    /**
     * Drop null entries so optional parameters are omitted entirely.
     */
    protected function filterNulls(array $data): array
    {
        return array_filter($data, static fn($value) => $value !== null);
    }

    protected function requireCredential(?string $value, string $name): string
    {
        if ($value === null || $value === '') {
            throw new TickTickException(
                "Missing TickTick {$name}. Pass it explicitly or set it in config/ticktick.php (TICKTICK_" . strtoupper($name) . ')'
            );
        }

        return $value;
    }
}
