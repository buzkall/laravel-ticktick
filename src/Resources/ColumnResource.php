<?php

namespace Arzcode\TickTick\Resources;

class ColumnResource extends Resource
{
    /**
     * Get the kanban columns of a project
     *
     * GET /open/v1/project/{projectId}/column
     *
     * @return array<int, array<mixed>>
     */
    public function all(string $projectId): array
    {
        return $this->toList($this->client->get("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/column"));
    }

    /**
     * Create a kanban column
     *
     * POST /open/v1/project/{projectId}/column
     *
     * @param  array<string, mixed>  $data  name, sortOrder
     * @return array<mixed>
     */
    public function create(string $projectId, array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/column", $data);
    }

    /**
     * Update a kanban column
     *
     * POST /open/v1/project/{projectId}/column/{columnId}
     *
     * @param  array<string, mixed>  $data
     * @return array<mixed>
     */
    public function update(string $projectId, string $columnId, array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/column/{$this->encode($columnId)}", $data);
    }
}
