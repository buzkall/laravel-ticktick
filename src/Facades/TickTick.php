<?php

namespace Arzcode\TickTick\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Arzcode\TickTick\TickTickClient client()
 * @method static \Arzcode\TickTick\Resources\TaskResource tasks()
 * @method static \Arzcode\TickTick\TickTick setAccessToken(string $token)
 * @method static string getAuthorizationUrl(string $clientId, string $redirectUri, string $scope = 'tasks:read tasks:write', string $state = '')
 * @method static array getAccessTokenFromCode(string $code, string $clientId, string $clientSecret, string $redirectUri)
 *
 * @see \Arzcode\TickTick\TickTick
 */
class TickTick extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Arzcode\TickTick\TickTick::class;
    }
}
