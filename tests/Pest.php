<?php

use Arzcode\TickTick\Tests\TestCase;
use Arzcode\TickTick\TickTick;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

uses(
    TestCase::class,
)->in('Feature', 'Unit');

/**
 * Build a TickTick instance backed by a mocked Guzzle handler, recording every
 * request that the package sends into $history.
 *
 * @param  array<int, ResponseInterface|Throwable>  $responses
 * @param  array<int, array<string, mixed>>  $history
 */
function fakeTickTick(array $responses, ?array &$history = null, array $config = []): TickTick
{
    $history = [];

    $stack = HandlerStack::create(new MockHandler($responses));
    $stack->push(Middleware::history($history));

    return new TickTick(array_merge([
        'access_token'  => 'test_access_token',
        'client_id'     => 'test_client_id',
        'client_secret' => 'test_client_secret',
        'redirect_uri'  => 'https://example.com/callback',
        'base_url'      => 'https://api.ticktick.com',
        'open_api_url'  => 'https://api.ticktick.com/open/v1',
        'oauth_url'     => 'https://ticktick.com',
        'handler'       => $stack,
    ], $config));
}

/**
 * Shorthand for a JSON response.
 */
function jsonResponse(array $body, int $status = 200): Response
{
    return new Response($status, ['Content-Type' => 'application/json'], json_encode($body));
}

/**
 * The request recorded by the history middleware at the given index.
 */
function recordedRequest(array $history, int $index = 0): RequestInterface
{
    expect($history)->toHaveCount(max($index + 1, count($history)));

    return $history[$index]['request'];
}
