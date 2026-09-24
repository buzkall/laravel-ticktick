<?php

namespace Arzcode\TickTick\Resources;

class HabitResource extends Resource
{
    /**
     * Get all habits
     *
     * GET /open/v1/habit
     */
    public function all(): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/habit");
    }

    /**
     * Get a habit
     *
     * GET /open/v1/habit/{habitId}
     */
    public function get(string $habitId): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/habit/{$this->encode($habitId)}");
    }

    /**
     * Create a habit
     *
     * POST /open/v1/habit
     *
     * @param  array  $data  name, goal, unit, repeatRule (RRULE), colour and so on
     */
    public function create(array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/habit", $data);
    }

    /**
     * Update a habit
     *
     * POST /open/v1/habit/{habitId}
     */
    public function update(string $habitId, array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/habit/{$this->encode($habitId)}", $data);
    }

    /**
     * Create or update a habit check-in
     *
     * POST /open/v1/habit/{habitId}/checkin
     *
     * @param  array  $data  stamp (Ymd, e.g. 20260407), value, goal
     */
    public function checkin(string $habitId, array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/habit/{$this->encode($habitId)}/checkin", $data);
    }

    /**
     * Query check-ins for one or more habits in a date range
     *
     * GET /open/v1/habit/checkins?habitIds=&from=&to=
     *
     * @param  array<int, string>  $habitIds
     * @param  string|int  $from  Date stamp in Ymd form, e.g. 20260401
     * @param  string|int  $to  Date stamp in Ymd form, e.g. 20260430
     */
    public function checkins(array $habitIds, string|int $from, string|int $to): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/habit/checkins", [
            'habitIds' => implode(',', $habitIds),
            'from'     => (string)$from,
            'to'       => (string)$to,
        ]);
    }
}
