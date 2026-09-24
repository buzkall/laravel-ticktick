<?php

namespace Arzcode\TickTick\Resources;

class TagResource extends Resource
{
    /**
     * Get all tags
     *
     * GET /open/v1/tag
     */
    public function all(): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/tag");
    }

    /**
     * Create a tag
     *
     * POST /open/v1/tag
     *
     * @param  array  $data  name, label, color, sortOrder
     */
    public function create(array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/tag", $data);
    }
}
