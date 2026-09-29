<?php

namespace Arzcode\TickTick\Resources;

class CountdownResource extends Resource
{
    /**
     * Get all countdowns
     *
     * GET /open/v1/countdown
     *
     * @return array<int, array<mixed>>
     */
    public function all(): array
    {
        return $this->toList($this->client->get("{$this->getOpenApiUrl()}/countdown"));
    }
}
