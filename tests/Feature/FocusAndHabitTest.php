<?php

use Buzkall\TickTick\Resources\FocusResource;
use GuzzleHttp\Psr7\Response;

test('it gets a focus record with its type', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'f1'])], $history);

    $ticktick->focus()->get('f1', FocusResource::TYPE_TIMING);

    $request = recordedRequest($history);

    expect($request->getUri()->getPath())->toBe('/open/v1/focus/f1')
        ->and($request->getUri()->getQuery())->toBe('type=1');
});

test('it lists focus records in a range', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->focus()->all('2026-04-01T00:00:00+0000', '2026-04-30T23:59:59+0000');

    $query = [];
    parse_str(recordedRequest($history)->getUri()->getQuery(), $query);

    expect(recordedRequest($history)->getUri()->getPath())->toBe('/open/v1/focus')
        ->and($query)->toBe([
            'from' => '2026-04-01T00:00:00+0000',
            'to'   => '2026-04-30T23:59:59+0000',
            'type' => '0',
        ]);
});

test('it creates a focus record', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'f1'])], $history);

    $ticktick->focus()->create(['type' => 0, 'taskId' => 't1', 'duration' => 1500]);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/focus')
        ->and($request->getMethod())->toBe('POST');
});

test('it deletes a focus record with its type', function() {
    $ticktick = fakeTickTick([new Response(200, [], '')], $history);

    $ticktick->focus()->delete('f1', FocusResource::TYPE_TIMING);

    $request = recordedRequest($history);

    expect($request->getMethod())->toBe('DELETE')
        ->and($request->getUri()->getPath())->toBe('/open/v1/focus/f1')
        ->and($request->getUri()->getQuery())->toBe('type=1');
});

test('it lists habits', function() {
    $ticktick = fakeTickTick([jsonResponse([['id' => 'h1', 'name' => 'Drink water']])], $history);

    $ticktick->habits()->all();

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/habit');
});

test('it gets a habit', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'h1'])], $history);

    $ticktick->habits()->get('h1');

    expect((string)recordedRequest($history)->getUri())->toBe('https://api.ticktick.com/open/v1/habit/h1');
});

test('it creates a habit', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'h1'])], $history);

    $ticktick->habits()->create(['name' => 'Drink water', 'goal' => 8, 'unit' => 'cups']);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/habit')
        ->and($request->getMethod())->toBe('POST')
        ->and(json_decode((string)$request->getBody(), true))->toBe([
            'name' => 'Drink water', 'goal' => 8, 'unit' => 'cups',
        ]);
});

test('it updates a habit', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'h1'])], $history);

    $ticktick->habits()->update('h1', ['goal' => 10]);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/habit/h1')
        ->and($request->getMethod())->toBe('POST');
});

test('it checks in a habit', function() {
    $ticktick = fakeTickTick([jsonResponse(['id' => 'ci1'])], $history);

    $ticktick->habits()->checkin('h1', ['stamp' => 20260407, 'value' => 1, 'goal' => 8]);

    $request = recordedRequest($history);

    expect((string)$request->getUri())->toBe('https://api.ticktick.com/open/v1/habit/h1/checkin')
        ->and($request->getMethod())->toBe('POST')
        ->and(json_decode((string)$request->getBody(), true))->toBe(['stamp' => 20260407, 'value' => 1, 'goal' => 8]);
});

test('it queries habit checkins with comma separated ids', function() {
    $ticktick = fakeTickTick([jsonResponse([])], $history);

    $ticktick->habits()->checkins(['h1', 'h2'], 20260401, 20260430);

    $query = [];
    parse_str(recordedRequest($history)->getUri()->getQuery(), $query);

    expect(recordedRequest($history)->getUri()->getPath())->toBe('/open/v1/habit/checkins')
        ->and($query)->toBe([
            'habitIds' => 'h1,h2',
            'from'     => '20260401',
            'to'       => '20260430',
        ]);
});
