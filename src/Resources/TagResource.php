<?php

namespace Arzcode\TickTick\Resources;

class TagResource extends Resource
{
    /**
     * Get all tags
     *
     * GET /open/v1/tag
     *
     * @return array<int, array<mixed>>
     */
    public function all(): array
    {
        return $this->toList($this->client->get("{$this->getOpenApiUrl()}/tag"));
    }

    /**
     * Create a tag
     *
     * POST /open/v1/tag
     *
     * @param  array<string, mixed>  $data  name, label, color, sortOrder
     * @return array<mixed>
     */
    public function create(array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/tag", $data);
    }
}
