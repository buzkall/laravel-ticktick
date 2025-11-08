<?php

namespace Buzkall\TickTick\Resources;

use Buzkall\TickTick\TickTickClient;

class TaskResource
{
    protected TickTickClient $client;

    public function __construct(TickTickClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get all tasks for a specific project
     */
    public function all(string $projectId, array $params = []): array
    {
        $response = $this->client->get("{$this->getOpenApiUrl()}/project/{$projectId}/data", $params);

        return $response['tasks'] ?? [];
    }

    /**
     * Get tasks filtered by due date (client-side filtering)
     *
     * @param  string  $projectId  The project ID
     * @param  string  $date  Date in Y-m-d format (e.g., '2025-01-15')
     * @param  string|null  $timezone  Timezone to use (e.g., 'Europe/Madrid'). Defaults to system timezone.
     * @param  array  $params  Additional query parameters (not used for filtering)
     * @return array Filtered tasks
     */
    public function byDueDate(string $projectId, string $date, ?string $timezone = null, array $params = []): array
    {
        $allTasks = $this->all($projectId, $params);
        $timezone = $timezone ?? date_default_timezone_get();

        return array_values(array_filter($allTasks, function($task) use ($date, $timezone) {
            if (empty($task['dueDate'])) {
                return false;
            }

            // Parse TickTick UTC date and convert to local timezone
            // TickTick format: 2021-05-06T21:30:00.000+0000
            $utcDate = new \DateTime($task['dueDate'], new \DateTimeZone('UTC'));
            $localDate = $utcDate->setTimezone(new \DateTimeZone($timezone));
            $taskDate = $localDate->format('Y-m-d');

            return $taskDate === $date;
        }));
    }

    /**
     * Get tasks due today (client-side filtering)
     *
     * @param  string  $projectId  The project ID
     * @param  string|null  $timezone  Timezone to use (e.g., 'Europe/Madrid'). Defaults to system timezone.
     * @param  array  $params  Additional query parameters (not used for filtering)
     * @return array Tasks due today
     */
    public function today(string $projectId, ?string $timezone = null, array $params = []): array
    {
        $timezone = $timezone ?? date_default_timezone_get();
        $today = (new \DateTime('now', new \DateTimeZone($timezone)))->format('Y-m-d');

        return $this->byDueDate($projectId, $today, $timezone, $params);
    }

    /**
     * Get a specific task by ID
     */
    public function get(string $taskId, string $projectId): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/project/{$projectId}/task/{$taskId}");
    }

    /**
     * Create a new task
     */
    public function create(array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/task", $data);
    }

    /**
     * Update an existing task
     */
    public function update(string $taskId, string $projectId, array $data): array
    {
        $data['id'] = $taskId;
        $data['projectId'] = $projectId;

        return $this->client->post("{$this->getOpenApiUrl()}/task/{$taskId}", $data);
    }

    /**
     * Delete a task
     */
    public function delete(string $taskId, string $projectId): array
    {
        return $this->client->delete("{$this->getOpenApiUrl()}/project/{$projectId}/task/{$taskId}");
    }

    /**
     * Complete a task
     */
    public function complete(string $taskId, string $projectId): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project/{$projectId}/task/{$taskId}/complete", []);
    }

    /**
     * Get the Open API base URL
     */
    protected function getOpenApiUrl(): string
    {
        return $this->client->getOpenApiUrl();
    }
}
