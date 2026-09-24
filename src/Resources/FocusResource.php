<?php

namespace Arzcode\TickTick\Resources;

class FocusResource extends Resource
{
    /** Pomodoro focus record */
    public const TYPE_POMODORO = 0;

    /** Stopwatch / timing focus record */
    public const TYPE_TIMING = 1;

    /**
     * Get a focus record
     *
     * GET /open/v1/focus/{focusId}?type=
     *
     * @param  int  $type  self::TYPE_POMODORO or self::TYPE_TIMING
     */
    public function get(string $focusId, int $type = self::TYPE_POMODORO): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/focus/{$this->encode($focusId)}", ['type' => $type]);
    }

    /**
     * List focus records in a time range
     *
     * The API caps the range at 30 days.
     *
     * GET /open/v1/focus?from=&to=&type=
     *
     * @param  string  $from  ISO 8601, e.g. 2026-04-01T00:00:00+0000
     * @param  string  $to  ISO 8601, e.g. 2026-04-30T23:59:59+0000
     */
    public function all(string $from, string $to, int $type = self::TYPE_POMODORO): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/focus", [
            'from' => $from,
            'to'   => $to,
            'type' => $type,
        ]);
    }

    /**
     * Create a focus record
     *
     * POST /open/v1/focus
     *
     * @param  array  $data  type, taskId, startTime, endTime, duration (seconds)
     */
    public function create(array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/focus", $data);
    }

    /**
     * Delete a focus record
     *
     * DELETE /open/v1/focus/{focusId}?type=
     */
    public function delete(string $focusId, int $type = self::TYPE_POMODORO): array
    {
        return $this->client->delete("{$this->getOpenApiUrl()}/focus/{$this->encode($focusId)}", ['type' => $type]);
    }
}
