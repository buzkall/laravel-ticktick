<?php

use GuzzleHttp\Psr7\Response;

test('it creates a project', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'p1', 'name' => 'Work'])], $history);

    $ticktick->projects()->create(['name' => 'Work', 'color' => '#F18181', 'viewMode' => 'kanban', 'kind' => 'TASK']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project')
        ->and($request->getMethod())->toBe('POST')
        ->and(json_decode((string)$request->getBody(), true))->toBe([
            'name' => 'Work', 'color' => '#F18181', 'viewMode' => 'kanban', 'kind' => 'TASK',
        ]);
});

test('it updates a project', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'p1'])], $history);

    $ticktick->projects()->update('p1', ['name' => 'New Name']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1')
        ->and($request->getMethod())->toBe('POST');
});

test('it deletes a project', function() {
    $ticktick = fakeTickTick([new Response(200, [], '')], $history);

    expect($ticktick->projects()->delete('p1'))->toBe([]);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1')
        ->and($request->getMethod())->toBe('DELETE');
});

test('it lists project groups', function() {
    $ticktick = fakeTickTick([jsonResponse([['id' => 'g1', 'name' => 'Work']])], $history);

    $ticktick->projectGroups()->all();

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/project/group');
});

test('it creates a project group', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'g1'])], $history);

    $ticktick->projectGroups()->create(['name' => 'Work']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/group')
        ->and($request->getMethod())->toBe('POST')
        ->and(json_decode((string)$request->getBody(), true))->toBe(['name' => 'Work']);
});

test('it updates a project group', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'g1'])], $history);

    $ticktick->projectGroups()->update('g1', ['name' => 'Personal']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/group/g1')
        ->and($request->getMethod())->toBe('POST');
});

test('it deletes a project group', function() {
    $ticktick = fakeTickTick([new Response(200, [], '')], $history);

    $ticktick->projectGroups()->delete('g1');

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/group/g1')
        ->and($request->getMethod())->toBe('DELETE');
});

test('it lists kanban columns', function() {
    $ticktick = fakeTickTick([jsonResponse([['id' => 'c1', 'name' => 'In progress']])], $history);

    $ticktick->columns()->all('p1');

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/column');
});

test('it creates a kanban column', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'c1'])], $history);

    $ticktick->columns()->create('p1', ['name' => 'In progress']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/column')
        ->and($request->getMethod())->toBe('POST')
        ->and(json_decode((string)$request->getBody(), true))->toBe(['name' => 'In progress']);
});

test('it updates a kanban column', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'c1'])], $history);

    $ticktick->columns()->update('p1', 'c1', ['name' => 'Done']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/column/c1')
        ->and($request->getMethod())->toBe('POST');
});

test('it lists tags', function() {
    $ticktick = fakeTickTick([jsonResponse([['name' => 'work']])], $history);

    $ticktick->tags()->all();

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/tag');
});

test('it creates a tag', function() {
    $ticktick = fakeTickTick([jsonResponse(['name' => 'urgent'])], $history);

    $ticktick->tags()->create(['name' => 'urgent', 'label' => 'urgent']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/tag')
        ->and($request->getMethod())->toBe('POST');
});

test('it lists countdowns', function() {
    $ticktick = fakeTickTick([jsonResponse([['id' => 'cd1']])], $history);

    $ticktick->countdowns()->all();

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/countdown');
});
