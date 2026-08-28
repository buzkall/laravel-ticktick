<?php

namespace Buzkall\TickTick;

use Buzkall\TickTick\Resources\ProjectResource;
use Buzkall\TickTick\Resources\TaskResource;

class TickTick
{
    protected TickTickClient $client;
    protected TaskResource $tasks;
    protected ProjectResource $projects;

    public function __construct(array $config = [])
    {
        $this->client = new TickTickClient($config);
        $this->tasks = new TaskResource($this->client);
        $this->projects = new ProjectResource($this->client);
    }

    public function client(): TickTickClient
    {
        return $this->client;
    }

    public function tasks(): TaskResource
    {
        return $this->tasks;
    }

    public function projects(): ProjectResource
    {
        return $this->projects;
    }

    public function setAccessToken(string $token): self
    {
        $this->client->setAccessToken($token);

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->client->getAccessToken();
    }

    public function setRefreshToken(string $token): self
    {
        $this->client->setRefreshToken($token);

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->client->getRefreshToken();
    }

    public function getAuthorizationUrl(?string $clientId = null, ?string $redirectUri = null, ?string $scope = null, string $state = ''): string
    {
        return $this->client->getAuthorizationUrl($clientId, $redirectUri, $scope, $state);
    }

    public function getAccessTokenFromCode(string $code, ?string $clientId = null, ?string $clientSecret = null, ?string $redirectUri = null, ?string $scope = null): array
    {
        return $this->client->getAccessTokenFromCode($code, $clientId, $clientSecret, $redirectUri, $scope);
    }

    public function refreshAccessToken(?string $refreshToken = null, ?string $clientId = null, ?string $clientSecret = null, ?string $scope = null): array
    {
        return $this->client->refreshAccessToken($refreshToken, $clientId, $clientSecret, $scope);
    }
}
