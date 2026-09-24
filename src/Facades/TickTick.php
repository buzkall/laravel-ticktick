<?php

namespace Arzcode\TickTick\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Arzcode\TickTick\TickTickClient client()
 * @method static \Arzcode\TickTick\Resources\TaskResource tasks()
 * @method static \Arzcode\TickTick\Resources\ProjectResource projects()
 * @method static \Arzcode\TickTick\Resources\ProjectGroupResource projectGroups()
 * @method static \Arzcode\TickTick\Resources\ColumnResource columns()
 * @method static \Arzcode\TickTick\Resources\TagResource tags()
 * @method static \Arzcode\TickTick\Resources\FocusResource focus()
 * @method static \Arzcode\TickTick\Resources\HabitResource habits()
 * @method static \Arzcode\TickTick\Resources\CountdownResource countdowns()
 * @method static \Arzcode\TickTick\TickTick setAccessToken(string $token)
 * @method static string|null getAccessToken()
 * @method static \Arzcode\TickTick\TickTick setRefreshToken(string $token)
 * @method static string|null getRefreshToken()
 * @method static string getAuthorizationUrl(?string $clientId = null, ?string $redirectUri = null, ?string $scope = null, string $state = '', ?string $codeChallenge = null)
 * @method static array generatePkceChallenge()
 * @method static array getAccessTokenFromCode(string $code, ?string $clientId = null, ?string $clientSecret = null, ?string $redirectUri = null, ?string $scope = null, ?string $codeVerifier = null)
 * @method static array getAccessTokenFromPkceCode(string $code, string $codeVerifier, ?string $clientId = null, ?string $redirectUri = null, ?string $scope = null)
 * @method static array refreshAccessToken(?string $refreshToken = null, ?string $clientId = null, ?string $clientSecret = null, ?string $scope = null)
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
