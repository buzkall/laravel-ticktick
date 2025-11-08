<?php

namespace Buzkall\TickTick\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Buzkall\TickTick\TickTickClient client()
 * @method static \Buzkall\TickTick\Resources\TaskResource tasks()
 * @method static \Buzkall\TickTick\TickTick setAccessToken(string $token)
 * @method static string getAuthorizationUrl(string $clientId, string $redirectUri, string $scope = 'tasks:read tasks:write', string $state = '')
 * @method static array getAccessTokenFromCode(string $code, string $clientId, string $clientSecret, string $redirectUri)
 *
 * @see \Buzkall\TickTick\TickTick
 */
class TickTick extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Buzkall\TickTick\TickTick::class;
    }
}
