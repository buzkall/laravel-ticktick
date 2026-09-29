<?php

namespace Arzcode\TickTick\Resources;

class ProjectGroupResource extends Resource
{
    /**
     * Get all project groups (folders)
     *
     * GET /open/v1/project/group
     *
     * @return array<int, array<mixed>>
     */
    public function all(): array
    {
        return $this->toList($this->client->get("{$this->getOpenApiUrl()}/project/group"));
    }

    /**
     * Create a project group
     *
     * POST /open/v1/project/group
     *
     * @param  array<string, mixed>  $data  name, sortOrder
     * @return array<mixed>
     */
    public function create(array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project/group", $data);
    }

    /**
     * Update a project group
     *
     * POST /open/v1/project/group/{projectGroupId}
     *
     * @param  array<string, mixed>  $data
     * @return array<mixed>
     */
    public function update(string $projectGroupId, array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project/group/{$this->encode($projectGroupId)}", $data);
    }

    /**
     * Delete a project group
     *
     * DELETE /open/v1/project/group/{projectGroupId}
     *
     * @return array<mixed>
     */
    public function delete(string $projectGroupId): array
    {
        return $this->client->delete("{$this->getOpenApiUrl()}/project/group/{$this->encode($projectGroupId)}");
    }
}
