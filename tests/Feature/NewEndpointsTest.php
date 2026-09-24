<?php

use Arzcode\TickTick\Exceptions\TickTickException;
use Arzcode\TickTick\Facades\TickTick as TickTickFacade;
use Arzcode\TickTick\Resources\ColumnResource;
use Arzcode\TickTick\Resources\CountdownResource;
use Arzcode\TickTick\Resources\FocusResource;
use Arzcode\TickTick\Resources\HabitResource;
use Arzcode\TickTick\Resources\ProjectGroupResource;
use Arzcode\TickTick\Resources\TagResource;
use Arzcode\TickTick\Resources\TaskResource;
use Arzcode\TickTick\TickTick;
use GuzzleHttp\Psr7\Response;

/**
 * Every Open API v1 endpoint added for full parity with the TickTick CLI, as
 * [resource accessor, resource method, arguments, HTTP method, request path].
 */
dataset('new endpoints', [
    'tasks move'            => ['tasks', 'move', [[['taskId' => 't1', 'fromProjectId' => 'p1', 'toProjectId' => 'p2']]], 'POST', '/open/v1/task/move'],
    'tasks moveTask'        => ['tasks', 'moveTask', ['t1', 'p1', 'p2'], 'POST', '/open/v1/task/move'],
    'tasks completed'       => ['tasks', 'completed', [['p1'], '2026-03-01T00:00:00+0000', '2026-03-09T23:59:59+0000'], 'POST', '/open/v1/task/completed'],
    'tasks filter'          => ['tasks', 'filter', [['p1']], 'POST', '/open/v1/task/filter'],
    'tasks search'          => ['tasks', 'search', ['groceries'], 'POST', '/open/v1/task/search'],
    'tasks comments'        => ['tasks', 'comments', ['t1', 'p1'], 'GET', '/open/v1/project/p1/task/t1/comments'],
    'tasks addComment'      => ['tasks', 'addComment', ['t1', 'p1', ['title' => 'Hi']], 'POST', '/open/v1/project/p1/task/t1/comment'],
    'tasks deleteComment'   => ['tasks', 'deleteComment', ['t1', 'p1', 'c1'], 'DELETE', '/open/v1/project/p1/task/t1/comment/c1'],
    'projects create'       => ['projects', 'create', [['name' => 'Work']], 'POST', '/open/v1/project'],
    'projects update'       => ['projects', 'update', ['p1', ['name' => 'Home']], 'POST', '/open/v1/project/p1'],
    'projects delete'       => ['projects', 'delete', ['p1'], 'DELETE', '/open/v1/project/p1'],
    'project groups all'    => ['projectGroups', 'all', [], 'GET', '/open/v1/project/group'],
    'project groups create' => ['projectGroups', 'create', [['name' => 'Work']], 'POST', '/open/v1/project/group'],
    'project groups update' => ['projectGroups', 'update', ['g1', ['name' => 'Home']], 'POST', '/open/v1/project/group/g1'],
    'project groups delete' => ['projectGroups', 'delete', ['g1'], 'DELETE', '/open/v1/project/group/g1'],
    'columns all'           => ['columns', 'all', ['p1'], 'GET', '/open/v1/project/p1/column'],
    'columns create'        => ['columns', 'create', ['p1', ['name' => 'Doing']], 'POST', '/open/v1/project/p1/column'],
    'columns update'        => ['columns', 'update', ['p1', 'col1', ['name' => 'Done']], 'POST', '/open/v1/project/p1/column/col1'],
    'tags all'              => ['tags', 'all', [], 'GET', '/open/v1/tag'],
    'tags create'           => ['tags', 'create', [['name' => 'urgent']], 'POST', '/open/v1/tag'],
    'habits all'            => ['habits', 'all', [], 'GET', '/open/v1/habit'],
    'habits get'            => ['habits', 'get', ['h1'], 'GET', '/open/v1/habit/h1'],
    'habits create'         => ['habits', 'create', [['name' => 'Read']], 'POST', '/open/v1/habit'],
    'habits update'         => ['habits', 'update', ['h1', ['name' => 'Read more']], 'POST', '/open/v1/habit/h1'],
    'habits checkin'        => ['habits', 'checkin', ['h1', ['stamp' => 20260407, 'value' => 1]], 'POST', '/open/v1/habit/h1/checkin'],
    'habits checkins'       => ['habits', 'checkins', [['h1'], 20260401, 20260430], 'GET', '/open/v1/habit/checkins'],
    'focus get'             => ['focus', 'get', ['f1'], 'GET', '/open/v1/focus/f1'],
    'focus all'             => ['focus', 'all', ['2026-04-01T00:00:00+0000', '2026-04-30T23:59:59+0000'], 'GET', '/open/v1/focus'],
    'focus create'          => ['focus', 'create', [['type' => 0, 'duration' => 1500]], 'POST', '/open/v1/focus'],
    'focus delete'          => ['focus', 'delete', ['f1'], 'DELETE', '/open/v1/focus/f1'],
    'countdowns all'        => ['countdowns', 'all', [], 'GET', '/open/v1/countdown'],
]);

test('it returns the decoded api response', function(string $resource, string $method, array $args, string $httpMethod, string $path) {
    $payload = [['id' => 'x1', 'name' => 'From TickTick']];
    $ticktick = fakeTickTick([jsonResponse($payload)], $history);

    $result = $ticktick->{$resource}()->{$method}(...$args);

    $request = recordedRequest($history);

    expect($result)->toBe($payload)
        ->and($request->getMethod())->toBe($httpMethod)
        ->and($request->getUri()->getHost())->toBe('api.ticktick.com')
        ->and($request->getUri()->getPath())->toBe($path)
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer test_access_token');
})->with('new endpoints');

test('it returns an empty array when the api answers with an empty body', function(string $resource, string $method, array $args) {
    $ticktick = fakeTickTick([new Response(200, [], '')], $history);

    expect($ticktick->{$resource}()->{$method}(...$args))->toBe([]);
})->with('new endpoints');

test('it wraps api errors in a ticktick exception', function(string $resource, string $method, array $args) {
    $ticktick = fakeTickTick([jsonResponse(['errorCode' => 'not_found'], 404)], $history);

    try {
        $ticktick->{$resource}()->{$method}(...$args);
        $this->fail('Expected a TickTickException.');
    } catch (TickTickException $e) {
        expect($e->getStatusCode())->toBe(404)
            ->and($e->getResponseBody())->toContain('not_found');
    }
})->with('new endpoints');

test('it refuses to call the api without an access token', function(string $resource, string $method, array $args) {
    $ticktick = fakeTickTick([jsonResponse([])], $history, ['access_token' => null]);

    expect(fn() => $ticktick->{$resource}()->{$method}(...$args))
        ->toThrow(TickTickException::class, 'Access token is required');

    expect($history)->toBeEmpty();
})->with('new endpoints');

/**
 * Every endpoint that puts an id in the URL, called with ids containing a slash
 * and a space, as [resource accessor, resource method, arguments, encoded path].
 */
dataset('endpoints with ids', [
    'projects get'          => ['projects', 'get', ['p/1 x'], '/open/v1/project/p%2F1%20x'],
    'projects getData'      => ['projects', 'getData', ['p/1 x'], '/open/v1/project/p%2F1%20x/data'],
    'projects update'       => ['projects', 'update', ['p/1 x', ['name' => 'Home']], '/open/v1/project/p%2F1%20x'],
    'projects delete'       => ['projects', 'delete', ['p/1 x'], '/open/v1/project/p%2F1%20x'],
    'project groups update' => ['projectGroups', 'update', ['g/1 x', ['name' => 'Home']], '/open/v1/project/group/g%2F1%20x'],
    'project groups delete' => ['projectGroups', 'delete', ['g/1 x'], '/open/v1/project/group/g%2F1%20x'],
    'columns all'           => ['columns', 'all', ['p/1 x'], '/open/v1/project/p%2F1%20x/column'],
    'columns create'        => ['columns', 'create', ['p/1 x', ['name' => 'Doing']], '/open/v1/project/p%2F1%20x/column'],
    'columns update'        => ['columns', 'update', ['p/1 x', 'c/1 x', ['name' => 'Done']], '/open/v1/project/p%2F1%20x/column/c%2F1%20x'],
    'tasks all'             => ['tasks', 'all', ['p/1 x'], '/open/v1/project/p%2F1%20x/data'],
    'tasks get'             => ['tasks', 'get', ['t/1 x', 'p/1 x'], '/open/v1/project/p%2F1%20x/task/t%2F1%20x'],
    'tasks update'          => ['tasks', 'update', ['t/1 x', 'p/1 x', ['title' => 'New']], '/open/v1/task/t%2F1%20x'],
    'tasks delete'          => ['tasks', 'delete', ['t/1 x', 'p/1 x'], '/open/v1/project/p%2F1%20x/task/t%2F1%20x'],
    'tasks complete'        => ['tasks', 'complete', ['t/1 x', 'p/1 x'], '/open/v1/project/p%2F1%20x/task/t%2F1%20x/complete'],
    'tasks comments'        => ['tasks', 'comments', ['t/1 x', 'p/1 x'], '/open/v1/project/p%2F1%20x/task/t%2F1%20x/comments'],
    'tasks addComment'      => ['tasks', 'addComment', ['t/1 x', 'p/1 x', ['title' => 'Hi']], '/open/v1/project/p%2F1%20x/task/t%2F1%20x/comment'],
    'tasks deleteComment'   => ['tasks', 'deleteComment', ['t/1 x', 'p/1 x', 'c/1 x'], '/open/v1/project/p%2F1%20x/task/t%2F1%20x/comment/c%2F1%20x'],
    'habits get'            => ['habits', 'get', ['h/1 x'], '/open/v1/habit/h%2F1%20x'],
    'habits update'         => ['habits', 'update', ['h/1 x', ['name' => 'Read']], '/open/v1/habit/h%2F1%20x'],
    'habits checkin'        => ['habits', 'checkin', ['h/1 x', ['stamp' => 20260407]], '/open/v1/habit/h%2F1%20x/checkin'],
    'focus get'             => ['focus', 'get', ['f/1 x'], '/open/v1/focus/f%2F1%20x'],
    'focus delete'          => ['focus', 'delete', ['f/1 x'], '/open/v1/focus/f%2F1%20x'],
]);

test('it encodes the ids in the url', function(string $resource, string $method, array $args, string $path) {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->{$resource}()->{$method}(...$args);

    expect(recordedRequest($history)->getUri()->getPath())->toBe($path);
})->with('endpoints with ids');

test('it sends the move helper payload as a single element list', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->tasks()->moveTask('t1', 'p1', 'p2');

    expect(json_decode((string)recordedRequest($history)->getBody(), true))->toBe([
        ['taskId' => 't1', 'fromProjectId' => 'p1', 'toProjectId' => 'p2'],
    ]);
});

test('it sends the priority and status constants as the api expects', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->tasks()->filter(
        priority: [TaskResource::PRIORITY_NONE, TaskResource::PRIORITY_LOW, TaskResource::PRIORITY_MEDIUM, TaskResource::PRIORITY_HIGH],
        status: [TaskResource::STATUS_OPEN, TaskResource::STATUS_ABANDONED, TaskResource::STATUS_COMPLETED],
    );

    expect(json_decode((string)recordedRequest($history)->getBody(), true))->toBe([
        'priority' => [0, 1, 3, 5],
        'status'   => [0, -1, 2],
    ]);
});

test('it sends a filter without a body when no criteria are given', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->tasks()->filter();

    expect((string)recordedRequest($history)->getBody())->toBe('');
});

test('it defaults focus records to the pomodoro type', function() {
    $ticktick = fakeTickTick([jsonResponse([]), jsonResponse([])], $history);

    $ticktick->focus()->get('f1');
    $ticktick->focus()->delete('f1');

    expect(FocusResource::TYPE_POMODORO)->toBe(0)
        ->and(FocusResource::TYPE_TIMING)->toBe(1)
        ->and($history[0]['request']->getUri()->getQuery())->toBe('type=0')
        ->and($history[1]['request']->getUri()->getQuery())->toBe('type=0');
});

test('it exposes the new resources through the facade', function() {
    expect(TickTickFacade::getFacadeRoot())->toBeInstanceOf(TickTick::class)
        ->and(TickTickFacade::projectGroups())->toBeInstanceOf(ProjectGroupResource::class)
        ->and(TickTickFacade::columns())->toBeInstanceOf(ColumnResource::class)
        ->and(TickTickFacade::tags())->toBeInstanceOf(TagResource::class)
        ->and(TickTickFacade::habits())->toBeInstanceOf(HabitResource::class)
        ->and(TickTickFacade::focus())->toBeInstanceOf(FocusResource::class)
        ->and(TickTickFacade::countdowns())->toBeInstanceOf(CountdownResource::class);
});
