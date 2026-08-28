<?php

namespace Buzkall\TickTick\Resources;

class CountdownResource extends Resource
{
    /**
     * Get all countdowns
     *
     * GET /open/v1/countdown
     */
    public function all(): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/countdown");
    }
}
