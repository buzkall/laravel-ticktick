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
