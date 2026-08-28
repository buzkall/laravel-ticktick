<?php

use GuzzleHttp\Psr7\Response;

test('it lists the tasks of a project', function() {
    $ticktick = fakeTickTick([
        jsonResponse(['project' => ['id' => 'p1'], 'tasks' => [['id' => 't1', 'title' => 'Ship it']]]),
    ], $history);

    $tasks = $ticktick->tasks()->all('p1');

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/data')
        ->and($tasks)->toHaveCount(1)
        ->and($tasks[0]['title'])->toBe('Ship it');
});

test('it returns an empty array when the project has no tasks', function() {
    $ticktick = fakeTickTick([jsonResponse(['project' => ['id' => 'p1']])], $history);

    expect($ticktick->tasks()->all('p1'))->toBe([]);
});

test('it filters tasks by due date in the given timezone', function() {
    $ticktick = fakeTickTick([
        jsonResponse(['tasks' => [
            ['id' => 't1', 'title' => 'Late evening in Madrid', 'dueDate' => '2025-01-15T23:30:00.000+0000'],
            ['id' => 't2', 'title' => 'Another day', 'dueDate' => '2025-01-17T10:00:00.000+0000'],
            ['id' => 't3', 'title' => 'No due date'],
        ]]),
    ], $history);

    // 23:30 UTC on the 15th is 00:30 on the 16th in Madrid.
    $tasks = $ticktick->tasks()->byDueDate('p1', '2025-01-16', 'Europe/Madrid');

    expect($tasks)->toHaveCount(1)
        ->and($tasks[0]['id'])->toBe('t1');
});

test('it filters tasks due today', function() {
    $today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s.000O');

    $ticktick = fakeTickTick([
        jsonResponse(['tasks' => [
            ['id' => 't1', 'title' => 'Today', 'dueDate' => $today],
            ['id' => 't2', 'title' => 'Long ago', 'dueDate' => '2020-01-01T10:00:00.000+0000'],
        ]]),
    ], $history);

    $tasks = $ticktick->tasks()->today('p1', 'UTC');

    expect($tasks)->toHaveCount(1)
        ->and($tasks[0]['id'])->toBe('t1');
});

test('it gets a single task', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 't1', 'title' => 'Ship it'])], $history);

    $ticktick->tasks()->get('t1', 'p1');

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/task/t1');
});

test('it creates a task', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'new_task', 'title' => 'Ship it'])], $history);

    $ticktick->tasks()->create(['title' => 'Ship it', 'projectId' => 'p1']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/task')
        ->and($request->getMethod())->toBe('POST')
        ->and(json_decode((string)$request->getBody(), true))->toBe(['title' => 'Ship it', 'projectId' => 'p1']);
});

test('it updates a task adding the ids to the payload', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 't1', 'title' => 'Updated'])], $history);

    $ticktick->tasks()->update('t1', 'p1', ['title' => 'Updated']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/task/t1')
        ->and($request->getMethod())->toBe('POST')
        ->and(json_decode((string)$request->getBody(), true))->toBe([
            'title'     => 'Updated',
            'id'        => 't1',
            'projectId' => 'p1',
        ]);
});

test('it completes a task without sending a body', function() {
    $ticktick = fakeTickTick([new Response(200, [], '')], $history);

    $result = $ticktick->tasks()->complete('t1', 'p1');

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/task/t1/complete')
        ->and($request->getMethod())->toBe('POST')
        ->and((string)$request->getBody())->toBe('')
        ->and($result)->toBe([]);
});

test('it deletes a task', function() {
    $ticktick = fakeTickTick([new Response(200, [], '')], $history);

    $result = $ticktick->tasks()->delete('t1', 'p1');

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/task/t1')
        ->and($request->getMethod())->toBe('DELETE')
        ->and($result)->toBe([]);
});
