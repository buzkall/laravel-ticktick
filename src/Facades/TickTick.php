<?php

namespace Buzkall\TickTick\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Buzkall\TickTick\TickTickClient client()
 * @method static \Buzkall\TickTick\Resources\TaskResource tasks()
 * @method static \Buzkall\TickTick\Resources\ProjectResource projects()
 * @method static \Buzkall\TickTick\Resources\ProjectGroupResource projectGroups()
 * @method static \Buzkall\TickTick\Resources\ColumnResource columns()
 * @method static \Buzkall\TickTick\Resources\TagResource tags()
 * @method static \Buzkall\TickTick\Resources\FocusResource focus()
 * @method static \Buzkall\TickTick\Resources\HabitResource habits()
 * @method static \Buzkall\TickTick\Resources\CountdownResource countdowns()
 * @method static \Buzkall\TickTick\TickTick setAccessToken(string $token)
 * @method static string|null getAccessToken()
 * @method static \Buzkall\TickTick\TickTick setRefreshToken(string $token)
 * @method static string|null getRefreshToken()
 * @method static string getAuthorizationUrl(?string $clientId = null, ?string $redirectUri = null, ?string $scope = null, string $state = '', ?string $codeChallenge = null)
 * @method static array generatePkceChallenge()
 * @method static array getAccessTokenFromCode(string $code, ?string $clientId = null, ?string $clientSecret = null, ?string $redirectUri = null, ?string $scope = null, ?string $codeVerifier = null)
 * @method static array getAccessTokenFromPkceCode(string $code, string $codeVerifier, ?string $clientId = null, ?string $redirectUri = null, ?string $scope = null)
 * @method static array refreshAccessToken(?string $refreshToken = null, ?string $clientId = null, ?string $clientSecret = null, ?string $scope = null)
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
