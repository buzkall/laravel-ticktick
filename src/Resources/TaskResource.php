<?php

namespace Arzcode\TickTick\Resources;

class TaskResource extends Resource
{
    /** Task is open */
    public const STATUS_OPEN = 0;

    /** Task was abandoned */
    public const STATUS_ABANDONED = -1;

    /** Task was completed */
    public const STATUS_COMPLETED = 2;

    /** Priority values accepted by the API */
    public const PRIORITY_NONE = 0;

    public const PRIORITY_LOW = 1;
    public const PRIORITY_MEDIUM = 3;
    public const PRIORITY_HIGH = 5;

    /**
     * Get all tasks for a specific project
     *
     * GET /open/v1/project/{projectId}/data
     */
    public function all(string $projectId, array $params = []): array
    {
        $response = $this->client->get("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/data", $params);

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
     *
     * GET /open/v1/project/{projectId}/task/{taskId}
     */
    public function get(string $taskId, string $projectId): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/task/{$this->encode($taskId)}");
    }

    /**
     * Create a new task
     *
     * POST /open/v1/task
     */
    public function create(array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/task", $data);
    }

    /**
     * Update an existing task
     *
     * POST /open/v1/task/{taskId}
     */
    public function update(string $taskId, string $projectId, array $data): array
    {
        $data['id'] = $taskId;
        $data['projectId'] = $projectId;

        return $this->client->post("{$this->getOpenApiUrl()}/task/{$this->encode($taskId)}", $data);
    }

    /**
     * Delete a task
     *
     * DELETE /open/v1/project/{projectId}/task/{taskId}
     */
    public function delete(string $taskId, string $projectId): array
    {
        return $this->client->delete("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/task/{$this->encode($taskId)}");
    }

    /**
     * Complete a task
     *
     * POST /open/v1/project/{projectId}/task/{taskId}/complete
     */
    public function complete(string $taskId, string $projectId): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/task/{$this->encode($taskId)}/complete");
    }

    /**
     * Move tasks between projects
     *
     * POST /open/v1/task/move
     *
     * @param  array<int, array{fromProjectId: string, toProjectId: string, taskId: string}>  $moves
     */
    public function move(array $moves): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/task/move", $moves);
    }

    /**
     * Move a single task between projects
     */
    public function moveTask(string $taskId, string $fromProjectId, string $toProjectId): array
    {
        return $this->move([[
            'taskId'        => $taskId,
            'fromProjectId' => $fromProjectId,
            'toProjectId'   => $toProjectId,
        ]]);
    }

    /**
     * List completed tasks in a date range
     *
     * POST /open/v1/task/completed
     *
     * @param  array<int, string>  $projectIds
     * @param  string  $startDate  ISO 8601, e.g. 2026-03-01T00:00:00+0000
     * @param  string  $endDate  ISO 8601, e.g. 2026-03-09T23:59:59+0000
     */
    public function completed(array $projectIds, string $startDate, string $endDate): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/task/completed", [
            'projectIds' => $projectIds,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ]);
    }

    /**
     * Filter tasks server-side
     *
     * POST /open/v1/task/filter
     *
     * @param  array<int, string>|null  $projectIds
     * @param  array<int, int>|null  $priority  0 none, 1 low, 3 medium, 5 high
     * @param  array<int, string>|null  $tag
     * @param  array<int, int>|null  $status  0 open, -1 abandoned, 2 completed
     */
    public function filter(
        ?array $projectIds = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?array $priority = null,
        ?array $tag = null,
        ?array $status = null,
    ): array {
        return $this->client->post("{$this->getOpenApiUrl()}/task/filter", $this->filterNulls([
            'projectIds' => $projectIds,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
            'priority'   => $priority,
            'tag'        => $tag,
            'status'     => $status,
        ]));
    }

    /**
     * Search tasks server-side
     *
     * POST /open/v1/task/search
     *
     * @param  array<int, string>|null  $projectIds
     * @param  array<int, string>|null  $tags
     * @param  array<int, int>|null  $status  0 open, -1 abandoned, 2 completed
     * @param  string|null  $dueFrom  ISO 8601
     * @param  string|null  $dueTo  ISO 8601
     */
    public function search(
        ?string $keywords = null,
        ?array $projectIds = null,
        ?array $tags = null,
        ?array $status = null,
        ?string $dueFrom = null,
        ?string $dueTo = null,
    ): array {
        return $this->client->post("{$this->getOpenApiUrl()}/task/search", $this->filterNulls([
            'keywords'   => $keywords,
            'projectIds' => $projectIds,
            'tags'       => $tags,
            'status'     => $status,
            'dueFrom'    => $dueFrom,
            'dueTo'      => $dueTo,
        ]));
    }

    /**
     * List the comments of a task
     *
     * GET /open/v1/project/{projectId}/task/{taskId}/comments
     */
    public function comments(string $taskId, string $projectId): array
    {
        return $this->client->get("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/task/{$this->encode($taskId)}/comments");
    }

    /**
     * Add a comment to a task
     *
     * POST /open/v1/project/{projectId}/task/{taskId}/comment
     */
    public function addComment(string $taskId, string $projectId, array $data): array
    {
        return $this->client->post("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/task/{$this->encode($taskId)}/comment", $data);
    }

    /**
     * Delete a comment from a task
     *
     * DELETE /open/v1/project/{projectId}/task/{taskId}/comment/{commentId}
     */
    public function deleteComment(string $taskId, string $projectId, string $commentId): array
    {
        return $this->client->delete("{$this->getOpenApiUrl()}/project/{$this->encode($projectId)}/task/{$this->encode($taskId)}/comment/{$this->encode($commentId)}");
    }
}
