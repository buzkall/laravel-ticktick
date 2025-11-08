<?php

namespace Buzkall\TickTick\Resources;

use Buzkall\TickTick\TickTickClient;

class ProjectResource
{
    protected TickTickClient $client;

    public function __construct(TickTickClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get all projects
     */
    public function all(array $params = []): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/project", $params);
    }

    /**
     * Get a specific project by ID
     */
    public function get(string $projectId): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/project/{$projectId}");
    }

    /**
     * Get project data including tasks
     */
    public function getData(string $projectId): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/project/{$projectId}/data");
    }

    /**
     * Get the Open API base URL
     */
    protected function getOpenApiUrl(): string
    {
        return $this->client->getOpenApiUrl();
    }
}
