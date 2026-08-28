<?php

namespace Buzkall\TickTick;

use Buzkall\TickTick\Resources\ColumnResource;
use Buzkall\TickTick\Resources\CountdownResource;
use Buzkall\TickTick\Resources\FocusResource;
use Buzkall\TickTick\Resources\HabitResource;
use Buzkall\TickTick\Resources\ProjectGroupResource;
use Buzkall\TickTick\Resources\ProjectResource;
use Buzkall\TickTick\Resources\TagResource;
use Buzkall\TickTick\Resources\TaskResource;

class TickTick
{
    protected TickTickClient $client;
    protected TaskResource $tasks;
    protected ProjectResource $projects;
    protected ProjectGroupResource $projectGroups;
    protected ColumnResource $columns;
    protected TagResource $tags;
    protected FocusResource $focus;
    protected HabitResource $habits;
    protected CountdownResource $countdowns;

    public function __construct(array $config = [])
    {
        $this->client = new TickTickClient($config);
        $this->tasks = new TaskResource($this->client);
        $this->projects = new ProjectResource($this->client);
        $this->projectGroups = new ProjectGroupResource($this->client);
        $this->columns = new ColumnResource($this->client);
        $this->tags = new TagResource($this->client);
        $this->focus = new FocusResource($this->client);
        $this->habits = new HabitResource($this->client);
        $this->countdowns = new CountdownResource($this->client);
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

    public function projectGroups(): ProjectGroupResource
    {
        return $this->projectGroups;
    }

    public function columns(): ColumnResource
    {
        return $this->columns;
    }

    public function tags(): TagResource
    {
        return $this->tags;
    }

    public function focus(): FocusResource
    {
        return $this->focus;
    }

    public function habits(): HabitResource
    {
        return $this->habits;
    }

    public function countdowns(): CountdownResource
    {
        return $this->countdowns;
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

    public function getAuthorizationUrl(?string $clientId = null, ?string $redirectUri = null, ?string $scope = null, string $state = '', ?string $codeChallenge = null): string
    {
        return $this->client->getAuthorizationUrl($clientId, $redirectUri, $scope, $state, $codeChallenge);
    }

    /**
     * @return array{code_verifier: string, code_challenge: string}
     */
    public function generatePkceChallenge(): array
    {
        return TickTickClient::generatePkceChallenge();
    }

    public function getAccessTokenFromCode(string $code, ?string $clientId = null, ?string $clientSecret = null, ?string $redirectUri = null, ?string $scope = null, ?string $codeVerifier = null): array
    {
        return $this->client->getAccessTokenFromCode($code, $clientId, $clientSecret, $redirectUri, $scope, $codeVerifier);
    }

    public function getAccessTokenFromPkceCode(string $code, string $codeVerifier, ?string $clientId = null, ?string $redirectUri = null, ?string $scope = null): array
    {
        return $this->client->getAccessTokenFromPkceCode($code, $codeVerifier, $clientId, $redirectUri, $scope);
    }

    public function refreshAccessToken(?string $refreshToken = null, ?string $clientId = null, ?string $clientSecret = null, ?string $scope = null): array
    {
        return $this->client->refreshAccessToken($refreshToken, $clientId, $clientSecret, $scope);
    }
}
