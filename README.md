# TickTick Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/buzkall/laravel-ticktick.svg?style=flat-square)](https://packagist.org/packages/buzkall/laravel-ticktick)
[![Total Downloads](https://img.shields.io/packagist/dt/buzkall/laravel-ticktick.svg?style=flat-square)](https://packagist.org/packages/buzkall/laravel-ticktick)

A Laravel package to connect to the TickTick API, authenticate, and interact with tasks. Built using the Spatie package skeleton structure.

## Features

- 🔐 OAuth2 authentication: authorization code, PKCE, or a personal API token
- ✅ Full Open API v1 coverage: tasks, projects, groups, columns, tags, habits, focus and countdowns
- 🔎 Server-side task search and filtering
- 🎯 Task completion tracking
- 🚀 Laravel service provider and facade
- ✨ Clean and intuitive API
- 🔄 Refresh token support
- 🧪 Test suite covering the HTTP layer

## Requirements

- PHP 8.3 or higher
- Laravel 12 or 13

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

Three options, simplest first.

#### Option A: Personal API token (no OAuth)

The quickest way to get going, and the only one that works in a headless
environment. In the TickTick web app click your avatar, then
**Settings → Account → API Token**, and create a token.

```env
TICKTICK_ACCESS_TOKEN=your_api_token
```

That token is sent exactly like an OAuth access token, so every method in this
package works with it and there is no browser round trip to arrange. This is the
same mechanism the official TickTick CLI uses for `ticktick auth token <token>`.

#### Option B: OAuth with PKCE (no client secret)

PKCE suits public clients that cannot keep a secret. Generate a verifier before
redirecting, keep it in the session, and send it back on the callback.

```php
Route::get('/ticktick/auth', function () {
    $pkce = TickTick::generatePkceChallenge();
    $state = Str::random(40);

    session([
        'ticktick_state'         => $state,
        'ticktick_code_verifier' => $pkce['code_verifier'],
    ]);

    return redirect(TickTick::getAuthorizationUrl(
        state: $state,
        codeChallenge: $pkce['code_challenge'],
    ));
});

Route::get('/ticktick/callback', function (Request $request) {
    abort_unless($request->get('state') === session('ticktick_state'), 403);

    $tokenData = TickTick::getAccessTokenFromPkceCode(
        $request->get('code'),
        session('ticktick_code_verifier'),
    );

    session(['ticktick_access_token' => $tokenData['access_token']]);

    return redirect('/dashboard');
});
```

#### Option C: OAuth authorization code (with client secret)

##### Step 1: Redirect user to TickTick authorization page

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

##### Step 2: Handle the callback

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

##### Step 3: Refresh the access token when it expires

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

### Searching and filtering tasks

TickTick can filter server-side, which avoids pulling a whole project down.

```php
// Full-text search, optionally narrowed
$tasks = TickTick::tasks()->search('quarterly report',
    projectIds: [$projectId],
    tags: ['work'],
    status: [TaskResource::STATUS_OPEN],
);

// Search by due date range only
$tasks = TickTick::tasks()->search(
    dueFrom: '2026-07-01T00:00:00+0000',
    dueTo: '2026-07-31T23:59:59+0000',
);

// Structured filter
$tasks = TickTick::tasks()->filter(
    projectIds: [$projectId],
    priority: [TaskResource::PRIORITY_MEDIUM, TaskResource::PRIORITY_HIGH],
    status: [TaskResource::STATUS_OPEN],
);

// Completed tasks in a range
$done = TickTick::tasks()->completed([$projectId],
    '2026-03-01T00:00:00+0000',
    '2026-03-09T23:59:59+0000',
);
```

Note that `today()` and `byDueDate()` still filter client-side, since the API
has no single-day due-date filter; `search()` with `dueFrom`/`dueTo` is the
server-side equivalent for a range.

### Moving tasks and comments

```php
TickTick::tasks()->moveTask($taskId, $fromProjectId, $toProjectId);

TickTick::tasks()->move([
    ['taskId' => $a, 'fromProjectId' => $p1, 'toProjectId' => $p2],
    ['taskId' => $b, 'fromProjectId' => $p1, 'toProjectId' => $p3],
]);

$comments = TickTick::tasks()->comments($taskId, $projectId);
TickTick::tasks()->addComment($taskId, $projectId, ['title' => 'Done']);
TickTick::tasks()->deleteComment($taskId, $projectId, $commentId);
```

### Managing projects, groups and columns

```php
$project = TickTick::projects()->create([
    'name'     => 'Work',
    'color'    => '#F18181',
    'viewMode' => 'kanban',   // list, kanban or timeline
    'kind'     => 'TASK',     // TASK or NOTE
]);

TickTick::projects()->update($projectId, ['name' => 'New name']);
TickTick::projects()->delete($projectId);

// Groups (sidebar folders)
TickTick::projectGroups()->all();
TickTick::projectGroups()->create(['name' => 'Work']);
TickTick::projectGroups()->update($groupId, ['name' => 'Personal']);
TickTick::projectGroups()->delete($groupId);

// Kanban columns
TickTick::columns()->all($projectId);
TickTick::columns()->create($projectId, ['name' => 'In progress']);
TickTick::columns()->update($projectId, $columnId, ['name' => 'Done']);
```

### Tags, habits, focus and countdowns

```php
TickTick::tags()->all();
TickTick::tags()->create(['name' => 'urgent', 'label' => 'urgent']);

// Habits
TickTick::habits()->all();
TickTick::habits()->create(['name' => 'Drink water', 'goal' => 8, 'unit' => 'cups']);
TickTick::habits()->checkin($habitId, ['stamp' => 20260407, 'value' => 1, 'goal' => 8]);
TickTick::habits()->checkins([$habitId], 20260401, 20260430);

// Focus records (the API caps the range at 30 days)
TickTick::focus()->all('2026-04-01T00:00:00+0000', '2026-04-30T23:59:59+0000');
TickTick::focus()->get($focusId, FocusResource::TYPE_TIMING);
TickTick::focus()->delete($focusId, FocusResource::TYPE_POMODORO);

TickTick::countdowns()->all();
```

### Field formats

| Field | Values |
| ----- | ------ |
| `priority` | `0` none, `1` low, `3` medium, `5` high |
| `status` | `0` open, `-1` abandoned, `2` completed |
| `viewMode` | `list`, `kanban`, `timeline` |
| `kind` | `TASK`, `NOTE`, `CHECKLIST` |
| `reminders[]` | `TRIGGER(;RELATED=START\|END)?:(-)?P…` e.g. `TRIGGER:-PT60M`, `TRIGGER;RELATED=END:-PT15M` |
| `repeatFlag` | A single `RRULE:` or `ERULE:` string, never both |
| Focus `type` | `0` pomodoro, `1` timing |
| Habit check-in `stamp` | `Ymd`, e.g. `20260407` |

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

- `getAuthorizationUrl($clientId = null, $redirectUri = null, $scope = null, $state = '', $codeChallenge = null)` - Generate authorization URL; pass a challenge for PKCE
- `generatePkceChallenge()` - Returns `['code_verifier' => …, 'code_challenge' => …]` (S256)
- `getAccessTokenFromPkceCode($code, $codeVerifier, $clientId = null, $redirectUri = null, $scope = null)` - Exchange a PKCE code, no client secret sent
- `getAccessTokenFromCode($code, $clientId = null, $clientSecret = null, $redirectUri = null, $scope = null)` - Exchange authorization code for access token
- `refreshAccessToken($refreshToken = null, $clientId = null, $clientSecret = null, $scope = null)` - Exchange a refresh token for a new access token
- `setAccessToken($token)` / `getAccessToken()` - Set or read the access token used for API requests
- `setRefreshToken($token)` / `getRefreshToken()` - Set or read the refresh token

### Project Methods

- `projects()->all($params = [])` - Get all projects
- `projects()->get($projectId)` - Get a specific project
- `projects()->getData($projectId)` - Get project data including all tasks and columns
- `projects()->create($data)` - Create a project
- `projects()->update($projectId, $data)` - Update a project
- `projects()->delete($projectId)` - Delete a project

### Project Group Methods

- `projectGroups()->all()` / `create($data)` / `update($groupId, $data)` / `delete($groupId)`

### Column Methods

- `columns()->all($projectId)` / `create($projectId, $data)` / `update($projectId, $columnId, $data)`

### Task Methods

- `tasks()->all($projectId, $params = [])` - Get all tasks for a specific project
- `tasks()->today($projectId, $timezone = null, $params = [])` - Get tasks due today (client-side filtering)
- `tasks()->byDueDate($projectId, $date, $timezone = null, $params = [])` - Get tasks by due date in Y-m-d format (client-side filtering)
- `tasks()->get($taskId, $projectId)` - Get a specific task
- `tasks()->create($data)` - Create a new task
- `tasks()->update($taskId, $projectId, $data)` - Update a task
- `tasks()->delete($taskId, $projectId)` - Delete a task
- `tasks()->complete($taskId, $projectId)` - Mark task as complete
- `tasks()->move($moves)` / `moveTask($taskId, $from, $to)` - Move tasks between projects
- `tasks()->completed($projectIds, $startDate, $endDate)` - Completed tasks in a range
- `tasks()->filter($projectIds, $startDate, $endDate, $priority, $tag, $status)` - Server-side filter
- `tasks()->search($keywords, $projectIds, $tags, $status, $dueFrom, $dueTo)` - Server-side search
- `tasks()->comments($taskId, $projectId)` / `addComment(...)` / `deleteComment(...)`

### Tag Methods

- `tags()->all()` / `create($data)`

### Habit Methods

- `habits()->all()` / `get($habitId)` / `create($data)` / `update($habitId, $data)`
- `habits()->checkin($habitId, $data)` / `checkins($habitIds, $from, $to)`

### Focus Methods

- `focus()->all($from, $to, $type)` / `get($focusId, $type)` / `create($data)` / `delete($focusId, $type)`

### Countdown Methods

- `countdowns()->all()`

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