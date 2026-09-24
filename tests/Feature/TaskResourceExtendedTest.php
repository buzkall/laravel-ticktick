<?php

use GuzzleHttp\Psr7\Response;

test('it moves tasks between projects', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->tasks()->moveTask('t1', 'p1', 'p2');

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/task/move')
        ->and($request->getMethod())->toBe('POST')
        ->and(json_decode((string)$request->getBody(), true))->toBe([
            ['taskId' => 't1', 'fromProjectId' => 'p1', 'toProjectId' => 'p2'],
        ]);
});

test('it moves several tasks in one call', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $moves = [
        ['taskId' => 't1', 'fromProjectId' => 'p1', 'toProjectId' => 'p2'],
        ['taskId' => 't2', 'fromProjectId' => 'p1', 'toProjectId' => 'p3'],
    ];
    $ticktick->tasks()->move($moves);

    expect(json_decode((string)recordedRequest($history)->getBody(), true))->toBe($moves);
});

test('it lists completed tasks in a date range', function() {
    $ticktick = fakeTickTick([jsonResponse([['id' => 't1']])], $history);

    $ticktick->tasks()->completed(['p1'], '2026-03-01T00:00:00+0000', '2026-03-09T23:59:59+0000');

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/task/completed')
        ->and($request->getMethod())->toBe('POST')
        ->and(json_decode((string)$request->getBody(), true))->toBe([
            'projectIds' => ['p1'],
            'startDate'  => '2026-03-01T00:00:00+0000',
            'endDate'    => '2026-03-09T23:59:59+0000',
        ]);
});

test('it filters tasks server side', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->tasks()->filter(projectIds: ['p1'], priority: [3, 5], status: [0]);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/task/filter')
        ->and(json_decode((string)$request->getBody(), true))->toBe([
            'projectIds' => ['p1'],
            'priority'   => [3, 5],
            'status'     => [0],
        ]);
});

test('it omits unset filters rather than sending nulls', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->tasks()->filter(projectIds: ['p1']);

    expect(json_decode((string)recordedRequest($history)->getBody(), true))
        ->toBe(['projectIds' => ['p1']]);
});

test('it searches tasks server side', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->tasks()->search('quarterly report', projectIds: ['p1'], tags: ['work'], status: [0]);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/task/search')
        ->and(json_decode((string)$request->getBody(), true))->toBe([
            'keywords'   => 'quarterly report',
            'projectIds' => ['p1'],
            'tags'       => ['work'],
            'status'     => [0],
        ]);
});

test('it searches by due date range without keywords', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->tasks()->search(dueFrom: '2026-07-01T00:00:00+0000', dueTo: '2026-07-31T23:59:59+0000');

    expect(json_decode((string)recordedRequest($history)->getBody(), true))->toBe([
        'dueFrom' => '2026-07-01T00:00:00+0000',
        'dueTo'   => '2026-07-31T23:59:59+0000',
    ]);
});

test('it lists task comments', function() {
    $ticktick = fakeTickTick([jsonResponse([['id' => 'c1', 'title' => 'Done']])], $history);

    $ticktick->tasks()->comments('t1', 'p1');

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/task/t1/comments')
        ->and($request->getMethod())->toBe('GET');
});

test('it adds a task comment', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'c1'])], $history);

    $ticktick->tasks()->addComment('t1', 'p1', ['title' => 'Done']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/task/t1/comment')
        ->and($request->getMethod())->toBe('POST')
        ->and(json_decode((string)$request->getBody(), true))->toBe(['title' => 'Done']);
});

test('it deletes a task comment', function() {
    $ticktick = fakeTickTick([new Response(200, [], '')], $history);

    $ticktick->tasks()->deleteComment('t1', 'p1', 'c1');

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/task/t1/comment/c1')
        ->and($request->getMethod())->toBe('DELETE');
});
