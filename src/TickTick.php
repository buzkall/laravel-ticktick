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

    public function getAuthorizationUrl(string $clientId, string $redirectUri, string $scope = 'tasks:read tasks:write', string $state = ''): string
    {
        return $this->client->getAuthorizationUrl($clientId, $redirectUri, $scope, $state);
    }

    public function getAccessTokenFromCode(string $code, string $clientId, string $clientSecret, string $redirectUri): array
    {
        return $this->client->getAccessTokenFromCode($code, $clientId, $clientSecret, $redirectUri);
    }
}
