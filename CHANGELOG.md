# Changelog

All notable changes to `laravel-ticktick` will be documented in this file.

## Unreleased

### Fixed

- The OAuth token exchange now sends the `scope` parameter and HTTP Basic
  credentials, as required by the TickTick documentation. Without them the token
  endpoint answers with a `400`.
- Connection failures (DNS errors, timeouts, refused connections) no longer
  raise a fatal `Error: Call to undefined method ConnectException::hasResponse()`
  and are thrown as `TickTickException` instead.
- Responses that are not valid JSON no longer raise a `TypeError` and are thrown
  as `TickTickException` with the offending body.
- `tasks()->complete()` no longer sends a bogus `[]` JSON body.
- Documented signatures of `tasks()->today()` and `tasks()->byDueDate()` now
  match the code; the previously documented calls raised a `TypeError`.
- The install instructions and Packagist badges pointed at `buzkall/ticktick`,
  which does not exist. The package is `buzkall/laravel-ticktick`.
- The facade docblock was missing `projects()`.

### Changed

- Dropped PHP 8.2 and Laravel 11. The package now requires PHP 8.3+ and
  Laravel 12 or 13.
- Test on Pest 5 where the dependency tree allows it. Pest 5 requires PHP 8.4
  and pulls the Symfony 8 tree, which conflicts with the Symfony 7 constraint
  in testbench 10 (Laravel 12), so the constraint is `^4.0|^5.0`: the
  PHP 8.4 + Laravel 13 leg runs Pest 5, the rest run Pest 4.

### Added

- `refreshAccessToken()` to exchange a refresh token for a new access token,
  plus `TICKTICK_REFRESH_TOKEN` and `TICKTICK_SCOPE` configuration entries.
- `getAuthorizationUrl()` and `getAccessTokenFromCode()` fall back to the
  credentials in `config/ticktick.php` instead of requiring them as arguments.
- `TickTickException::getStatusCode()` and `getResponseBody()` to inspect API
  errors (for example to detect a `401` or a `429`).
- Test coverage of the HTTP layer through a mocked Guzzle handler, asserting the
  exact requests sent to TickTick.
- GitHub Actions workflows running the test suite and the code style check.

## 0.0.1 - 2025-11-08

- Initial release
- OAuth2 authentication support
- Task management operations (CRUD)
- Task completion tracking
- Laravel service provider and facade
