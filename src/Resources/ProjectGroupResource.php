<?php

namespace Buzkall\TickTick\Resources;

class ProjectGroupResource extends Resource
{
    /**
     * Get all project groups (folders)
     *
     * GET /open/v1/project/group
     */
    public function all(): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/project/group");
    }

    /**
     * Create a project group
     *
     * POST /open/v1/project/group
     *
     * @param  array  $data  name, sortOrder
     */
    public function create(array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project/group", $data);
    }

    /**
     * Update a project group
     *
     * POST /open/v1/project/group/{projectGroupId}
     */
    public function update(string $projectGroupId, array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project/group/{$projectGroupId}", $data);
    }

    /**
     * Delete a project group
     *
     * DELETE /open/v1/project/group/{projectGroupId}
     */
    public function delete(string $projectGroupId): array
    {
        return $this->client->delete("{$this->getOpenApiUrl()}/project/group/{$projectGroupId}");
    }
}
