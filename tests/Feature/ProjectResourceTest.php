<?php

test('it lists all projects', function() {
    $ticktick = fakeTickTick([
        jsonResponse([['id' => '6226ff9877acee87727f6bca', 'name' => 'Inbox']]),
    ], $history);

    $projects = $ticktick->projects()->all();

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/project')
        ->and(recordedRequest($history)->getMethod())->toBe('GET')
        ->and($projects)->toHaveCount(1)
        ->and($projects[0]['name'])->toBe('Inbox');
});

test('it gets a single project', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'p1', 'name' => 'Work'])], $history);

    $project = $ticktick->projects()->get('p1');

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1')
        ->and($project['name'])->toBe('Work');
});

test('it gets the project data including tasks', function() {
    $ticktick = fakeTickTick([
        jsonResponse([
            'project' => ['id' => 'p1', 'name' => 'Work'],
            'tasks'   => [['id' => 't1', 'title' => 'Ship it']],
            'columns' => [],
        ]),
    ], $history);

    $data = $ticktick->projects()->getData('p1');

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/project/p1/data')
        ->and($data['tasks'][0]['title'])->toBe('Ship it');
});
