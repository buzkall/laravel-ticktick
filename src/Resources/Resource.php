<?php

namespace Arzcode\TickTick\Resources;

use Arzcode\TickTick\TickTickClient;

abstract class Resource
{
    protected TickTickClient $client;

    public function __construct(TickTickClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get the Open API base URL
     */
    protected function getOpenApiUrl(): string
    {
        return $this->client->getOpenApiUrl();
    }

    /**
     * Encode an id so it is always a single, valid URL path segment.
     */
    protected function encode(string $id): string
    {
        return rawurlencode($id);
    }

    /**
     * Drop null entries so optional filters are omitted rather than sent as null.
     */
    protected function filterNulls(array $data): array
    {
        return array_filter($data, static fn($value) => $value !== null);
    }
}
