# TickTick Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/buzkall/laravel-ticktick.svg?style=flat-square)](https://packagist.org/packages/buzkall/laravel-ticktick)
[![Total Downloads](https://img.shields.io/packagist/dt/buzkall/laravel-ticktick.svg?style=flat-square)](https://packagist.org/packages/buzkall/laravel-ticktick)

A Laravel package to connect to the TickTick API, authenticate, and interact with tasks. Built using the Spatie package skeleton structure.

## Features

- 🔐 OAuth2 authentication flow
- ✅ Complete task management (create, read, update, delete)
- 🎯 Task completion tracking
- 🚀 Laravel service provider and facade
- ✨ Clean and intuitive API
- 🔄 Refresh token support
- 🧪 Test suite covering the HTTP layer

## Installation

You can install the package via Composer:

```bash
composer require buzkall/laravel-ticktick
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ticktick-config
```

## Configuration

Add your TickTick API credentials to your `.env` file:

```env
TICKTICK_CLIENT_ID=your_client_id
TICKTICK_CLIENT_SECRET=your_client_secret
TICKTICK_REDIRECT_URI=https://yourapp.com/ticktick/callback

# Optional
TICKTICK_SCOPE="tasks:read tasks:write"
TICKTICK_ACCESS_TOKEN=your_access_token
TICKTICK_REFRESH_TOKEN=your_refresh_token
```

To obtain API credentials:
1. Visit [TickTick Developer Portal](https://developer.ticktick.com/)
2. Create a new application
3. Copy your Client ID and Client Secret

## Usage

The API documentation is here: https://developer.ticktick.com/docs#/openapi

### Authentication

#### Step 1: Redirect user to TickTick authorization page

The client id, redirect uri and scope default to the values in
`config/ticktick.php`, so you only need to pass them to override the defaults.

```php
use Buzkall\TickTick\Facades\TickTick;

Route::get('/ticktick/auth', function () {
    $state = Str::random(40);
    session(['ticktick_state' => $state]);

    return redirect(TickTick::getAuthorizationUrl(state: $state));
});
```

If a random state is not provided, one is generated for you. Store it in the
session so you can verify it in the callback.

#### Step 2: Handle the callback

```php
Route::get('/ticktick/callback', function (Request $request) {
    abort_unless($request->get('state') === session('ticktick_state'), 403);

    $tokenData = TickTick::getAccessTokenFromCode($request->get('code'));

    // Persist both tokens: the access token expires, the refresh token
    // is what lets you get a new one without sending the user through
    // the authorization flow again.
    session([
        'ticktick_access_token'  => $tokenData['access_token'],
        'ticktick_refresh_token' => $tokenData['refresh_token'] ?? null,
    ]);

    return redirect('/dashboard');
});
```

#### Step 3: Refresh the access token when it expires

```php
$tokenData = TickTick::refreshAccessToken(session('ticktick_refresh_token'));

session([
    'ticktick_access_token'  => $tokenData['access_token'],
    'ticktick_refresh_token' => $tokenData['refresh_token'] ?? session('ticktick_refresh_token'),
]);
```

The refresh token also defaults to `config('ticktick.refresh_token')`, so
`TickTick::refreshAccessToken()` works without arguments when it is configured.

### Working with Projects

#### Get all projects

```php
use Buzkall\TickTick\Facades\TickTick;

// Set access token (if not already set in config)
TickTick::setAccessToken(session('ticktick_access_token'));

// Get all projects
$projects = TickTick::projects()->all();

// Each project has 'id' and 'name' properties
foreach ($projects as $project) {
    echo $project['name'] . ' (ID: ' . $project['id'] . ')';
}
```

#### Get a specific project

```php
$project = TickTick::projects()->get($projectId);
```

#### Get project data (including tasks)

```php
// This returns complete project data including all tasks
$data = TickTick::projects()->getData($projectId);
$tasks = $data['tasks'];
```

### Working with Tasks

#### Get all tasks for a project

```php
// Get all tasks for a specific project
$tasks = TickTick::tasks()->all($projectId);
```

#### Filter tasks by date

```php
// Get tasks due today (at any time)
$todayTasks = TickTick::tasks()->today($projectId);

// Get tasks due on a specific date
$tasks = TickTick::tasks()->byDueDate($projectId, '2025-01-15');

// TickTick stores due dates in UTC. Pass a timezone to decide which
// calendar day a task belongs to; it defaults to the app timezone.
$tasks = TickTick::tasks()->today($projectId, 'Europe/Madrid');
$tasks = TickTick::tasks()->byDueDate($projectId, '2025-01-15', 'Europe/Madrid');

// Note: TickTick API doesn't support server-side filtering by date.
// These methods fetch all tasks and filter client-side.
```

#### Create a new task

```php
$task = TickTick::tasks()->create([
    'title' => 'New Task',
    'content' => 'Task description',
    'projectId' => $projectId, // Required
    'priority' => 1, // 0: None, 1: Low, 3: Medium, 5: High
    'dueDate' => '2025-12-31T23:59:59+0000',
]);
```

#### Get a specific task

```php
$task = TickTick::tasks()->get($taskId, $projectId);
```

#### Update a task

```php
$task = TickTick::tasks()->update($taskId, $projectId, [
    'title' => 'Updated Task Title',
    'status' => 0, // 0: Normal, 1: Completed
]);
```

#### Delete a task

```php
TickTick::tasks()->delete($taskId, $projectId);
```

#### Complete a task

```php
TickTick::tasks()->complete($taskId, $projectId);
```

### Using without Facade

```php
use Buzkall\TickTick\TickTick;

$ticktick = new TickTick([
    'access_token' => 'your_access_token',
    'base_url' => 'https://api.ticktick.com',
    'open_api_url' => 'https://api.ticktick.com/open/v1',
    'oauth_url' => 'https://ticktick.com',
    'timeout' => 30,
]);

$projects = $ticktick->projects()->all();
```

### Using Dependency Injection

```php
use Buzkall\TickTick\TickTick;

class TaskController extends Controller
{
    public function __construct(private TickTick $ticktick)
    {
    }

    public function index()
    {
        $projects = $this->ticktick->projects()->all();
        return view('tasks.index', compact('projects'));
    }
}
```

## API Reference

### Authentication Methods

All arguments except `$code` and `$state` fall back to `config/ticktick.php`.

- `getAuthorizationUrl($clientId = null, $redirectUri = null, $scope = null, $state = '')` - Generate authorization URL
- `getAccessTokenFromCode($code, $clientId = null, $clientSecret = null, $redirectUri = null, $scope = null)` - Exchange authorization code for access token
- `refreshAccessToken($refreshToken = null, $clientId = null, $clientSecret = null, $scope = null)` - Exchange a refresh token for a new access token
- `setAccessToken($token)` / `getAccessToken()` - Set or read the access token used for API requests
- `setRefreshToken($token)` / `getRefreshToken()` - Set or read the refresh token

### Project Methods

- `projects()->all($params = [])` - Get all projects
- `projects()->get($projectId)` - Get a specific project
- `projects()->getData($projectId)` - Get project data including all tasks

### Task Methods

- `tasks()->all($projectId, $params = [])` - Get all tasks for a specific project
- `tasks()->today($projectId, $timezone = null, $params = [])` - Get tasks due today (client-side filtering)
- `tasks()->byDueDate($projectId, $date, $timezone = null, $params = [])` - Get tasks by due date in Y-m-d format (client-side filtering)
- `tasks()->get($taskId, $projectId)` - Get a specific task
- `tasks()->create($data)` - Create a new task
- `tasks()->update($taskId, $projectId, $data)` - Update a task
- `tasks()->delete($taskId, $projectId)` - Delete a task
- `tasks()->complete($taskId, $projectId)` - Mark task as complete

## Error handling

Every failure is thrown as a `Buzkall\TickTick\Exceptions\TickTickException`,
including connection failures and unparseable responses. When TickTick answered
with an HTTP error, the status code and raw body are available on the exception:

```php
use Buzkall\TickTick\Exceptions\TickTickException;

try {
    $projects = TickTick::projects()->all();
} catch (TickTickException $e) {
    if ($e->getStatusCode() === 401) {
        // The access token expired, refresh it.
    }

    report($e->getResponseBody());
}
```

## Testing

Run the tests with:

```bash
composer test
```

The suite mocks the Guzzle handler, so it asserts the exact requests the package
sends to TickTick without hitting the network.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security-related issues, please email the maintainer instead of using the issue tracker.

## Credits

- [buzkall](https://github.com/buzkall)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.