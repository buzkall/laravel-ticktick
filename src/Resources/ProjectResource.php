<?php

namespace Arzcode\TickTick\Resources;

class ProjectResource extends Resource
{
    /**
     * Get all projects
     *
     * GET /open/v1/project
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<mixed>>
     */
    public function all(array $params = []): array
    {
        return $this->toList($this->client->get("{$this->getOpenApiUrl()}/project", $params));
    }

    /**
     * Get a specific project by ID
     *
     * GET /open/v1/project/{projectId}
     *
     * @return array<mixed>
     */
    public function get(string $projectId): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}");
    }

    /**
     * Get project data including tasks and columns
     *
     * GET /open/v1/project/{projectId}/data
     *
     * @return array<mixed>
     */
    public function getData(string $projectId): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/data");
    }

    /**
     * Create a project
     *
     * POST /open/v1/project
     *
     * @param  array<string, mixed>  $data  name, color, sortOrder, viewMode (list|kanban|timeline), kind (TASK|NOTE)
     * @return array<mixed>
     */
    public function create(array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project", $data);
    }

    /**
     * Update a project
     *
     * POST /open/v1/project/{projectId}
     *
     * @param  array<string, mixed>  $data
     * @return array<mixed>
     */
    public function update(string $projectId, array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}", $data);
    }

    /**
     * Delete a project
     *
     * DELETE /open/v1/project/{projectId}
     *
     * @return array<mixed>
     */
    public function delete(string $projectId): array
    {
        return $this->client->delete("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}");
    }
}
