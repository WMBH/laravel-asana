# Asana API Coverage + Housekeeping Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the 76 missing free/Starter-tier Asana endpoints (incl. `instantiateTask`), fix 4 mis-pathed requests, remove the never-functional retry config, and upgrade dependencies so `composer audit` passes locally and in CI.

**Architecture:** Every endpoint follows the existing chain Facade → `Asana` → Resource → Saloon Request → `AsanaConnector`, with responses hydrated into Spatie Data DTOs. New domains get a new Resource + accessor; endpoints belonging to existing domains become methods on the existing Resource. Spec: `docs/superpowers/specs/2026-09-11-api-coverage-upgrade-design.md`. Per-endpoint status: `docs/asana-api-coverage.md`.

**Tech Stack:** PHP 8.3+, Laravel 11–13, Saloon v4, Spatie Laravel Data v4, Pest 4 + Lawman, PHPStan 5, Pint (laravel preset).

**Branch:** `feat/api-coverage-2026-09` off `main`. One commit per task. Run `composer format` before every commit (Pint auto-commits on CI otherwise).

---

## File structure

Created (new domains):
- `src/Resources/{TaskTemplate,ProjectTemplate,Job,StatusUpdate,ProjectBrief,Membership,Event,CustomType,UserTaskList,AccessRequest,Reaction}Resource.php` — one public method per endpoint, no logic beyond send + hydrate (EventResource additionally handles the 412 sync handshake).
- `src/Requests/{TaskTemplates,ProjectTemplates,Jobs,StatusUpdates,ProjectBriefs,Memberships,Events,CustomTypes,UserTaskLists,AccessRequests,Reactions}/*.php` — one Saloon request per endpoint.
- `src/Data/{Job,TaskTemplate,ProjectTemplate,StatusUpdate,ProjectBrief,Membership,ProjectMembership,TeamMembership,WorkspaceMembership,Event,CustomType,UserTaskList,AccessRequest,Reaction,CustomFieldSetting,EnumOption}Data.php` — DTOs.
- `src/Data/Shared/EventsResponse.php` — `{data, sync, hasMore}` envelope for the events endpoints.
- `src/Requests/Attachments/GetAttachmentsForObjectRequest.php` — replaces the legacy per-task attachments path.
- Tests mirror `src/`: `tests/Unit/Resources/*ResourceTest.php`, `tests/Unit/Data/*DataTest.php`, `tests/Unit/Data/Shared/EventsResponseTest.php`, `tests/Unit/AsanaServiceProviderTest.php`.

Modified:
- `src/Asana.php` — one lazy accessor per new Resource.
- `src/Resources/{Task,Project,CustomField,Team,Workspace,User,Tag,Attachment,Goal}Resource.php` — new methods / path fixes.
- `src/Requests/Goals/{GetSubgoals,AddSubgoal}Request.php`, `src/Requests/Teams/GetTeamsForWorkspaceRequest.php` — path fixes.
- `src/AsanaConnector.php`, `src/AsanaServiceProvider.php`, `config/asana.php`, `tests/TestCase.php` — retry removal.
- `composer.json`, `.github/workflows/phpstan.yml` — dependency bumps + audit step.
- `tests/ArchTest.php`, `tests/Unit/AsanaTest.php`, `README.md`, `docs/asana-api-coverage.md`, `CLAUDE.md`.

Deleted:
- `src/Requests/Attachments/GetAttachmentsForTaskRequest.php`.

Task order: 1 (deps) → 2 (retry) → 3 (path fixes) → 4 (JobData) → 5–21 (domains; independent of each other, any order) → 22 (docs, tracker, final gates, PR).

---

### Task 1: Branch, dependency bumps, audit, CI audit step

**Files:**
- Modify: `composer.json:22-24,30`
- Modify: `.github/workflows/phpstan.yml:27-30`

- [ ] **Step 1: Create the branch**

Run: `git checkout -b feat/api-coverage-2026-09`
Expected: `Switched to a new branch 'feat/api-coverage-2026-09'`

- [ ] **Step 2: Record the failing baseline**

Run: `composer audit --format=summary`
Expected: `Found 21 security vulnerability advisories affecting 3 packages.` (guzzle, psr7, commonmark) and exit code 1.

- [ ] **Step 3: Raise the minimum constraints in `composer.json`**

Replace these three lines (keep everything else byte-identical):

```json
        "guzzlehttp/guzzle": "^7.15.2 || ^8.0",
```
(was `"guzzlehttp/guzzle": "^7.6",`)

```json
        "saloonphp/saloon": "^4.0.1",
```
(was `"saloonphp/saloon": "^4.0",`)

```json
        "jonpurvis/lawman": "^4.2 || ^5.0",
```
(was `"jonpurvis/lawman": "^4.2",`)

Pest stays `^4.0`: Pest 5 requires PHP ^8.4 and PHPUnit ^13, which the PHP 8.3 / Laravel 11 (testbench 9) legs of the CI matrix cannot satisfy.

- [ ] **Step 4: Update the lock and vendor**

Run: `composer update --with-all-dependencies --no-interaction`
Expected: ends with `Generating optimized autoload files` then the `testbench package:discover` output. `composer show guzzlehttp/guzzle | grep versions` prints `7.15.2` or later (Laravel 13 still pins `^7.8.2`, so Guzzle 8 is not selected locally). `composer show saloonphp/saloon | grep versions` prints `v4.0.1`. `composer show jonpurvis/lawman | grep versions` prints `v5.0.0` (it allows Pest 4).

- [ ] **Step 5: Verify audit passes**

Run: `composer audit`
Expected: `No security vulnerability advisories found.` and exit code 0. If `league/commonmark` still shows, run `composer update league/commonmark guzzlehttp/psr7 --with-all-dependencies` and re-run.

- [ ] **Step 6: Verify the suite and static analysis still pass on the new versions**

Run: `composer test`
Expected: `Tests:    185 passed (521 assertions)`.

Run: `composer analyse`
Expected: `[OK] No errors`.

- [ ] **Step 7: Add the CI audit step**

In `.github/workflows/phpstan.yml`, after the `Install composer dependencies` step and before `Run PHPStan`, add:

```yaml
      - name: Audit dependencies
        run: composer audit
```

The file's `steps:` block then reads:

```yaml
    steps:
      - uses: actions/checkout@v6

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: none

      - name: Install composer dependencies
        run: composer update --prefer-stable --prefer-dist --no-interaction

      - name: Audit dependencies
        run: composer audit

      - name: Run PHPStan
        run: ./vendor/bin/phpstan --error-format=github
```

- [ ] **Step 8: Commit**

```bash
git add composer.json .github/workflows/phpstan.yml
git commit -m "chore(deps): require patched guzzle/saloon, allow lawman 5, audit in CI

Raises guzzlehttp/guzzle to ^7.15.2 || ^8.0 and saloonphp/saloon to ^4.0.1
(21 advisories in guzzle, psr7 and commonmark cleared by composer update).
Adds a composer audit step to the PHPStan workflow.

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 2: Remove the never-functional retry configuration

`AsanaConnector` accepted `retryAttempts`/`retrySleep` but never assigned Saloon's `$tries`/`$retryInterval`, and Saloon's retry loop would not catch `AsanaException` anyway (it extends `Exception`, not Saloon's `RequestException`). Owner decision: remove rather than fix.

**Files:**
- Modify: `src/AsanaConnector.php:20-25`
- Modify: `src/AsanaServiceProvider.php:22-29`
- Modify: `config/asana.php`
- Modify: `tests/TestCase.php:19-25`
- Modify: `README.md:24-33`
- Create: `tests/Unit/AsanaServiceProviderTest.php`

- [ ] **Step 1: Write the service-provider test (guards the code path this task edits)**

Create `tests/Unit/AsanaServiceProviderTest.php`:

```php
<?php

use WMBH\Asana\Asana;
use WMBH\Asana\AsanaConnector;

test('container resolves AsanaConnector as a singleton configured from asana.timeout', function () {
    config()->set('asana.timeout', 45);

    $connector = app(AsanaConnector::class);

    $reflection = new ReflectionMethod($connector, 'defaultConfig');
    $defaultConfig = $reflection->invoke($connector);

    expect($connector)->toBeInstanceOf(AsanaConnector::class)
        ->and($defaultConfig['timeout'])->toBe(45)
        ->and(app(AsanaConnector::class))->toBe($connector);
});

test('container resolves Asana as a singleton wrapping the bound connector', function () {
    $asana = app(Asana::class);

    expect($asana)->toBeInstanceOf(Asana::class)
        ->and($asana->getConnector())->toBe(app(AsanaConnector::class))
        ->and(app(Asana::class))->toBe($asana);
});
```

- [ ] **Step 2: Run it (passes already; it pins current behaviour before the removal)**

Run: `vendor/bin/pest tests/Unit/AsanaServiceProviderTest.php`
Expected: `Tests:    2 passed`.

- [ ] **Step 3: Remove the constructor arguments**

`src/AsanaConnector.php` constructor becomes:

```php
    public function __construct(
        protected readonly string $token,
        protected readonly int $timeout = 30,
    ) {}
```

- [ ] **Step 4: Stop passing them from the provider**

`src/AsanaServiceProvider.php::packageRegistered()` becomes:

```php
    public function packageRegistered(): void
    {
        $this->app->singleton(AsanaConnector::class, function () {
            return new AsanaConnector(
                token: config('asana.token', ''),
                timeout: config('asana.timeout', 30),
            );
        });

        $this->app->singleton(Asana::class, function () {
            return new Asana(
                app(AsanaConnector::class),
            );
        });
    }
```

- [ ] **Step 5: Drop the config block**

`config/asana.php` becomes exactly:

```php
<?php

// config for WMBH/Asana
return [
    'token' => env('ASANA_TOKEN'),

    'timeout' => env('ASANA_TIMEOUT', 30),
];
```

- [ ] **Step 6: Drop the test-environment overrides**

`tests/TestCase.php::defineEnvironment()` becomes:

```php
    protected function defineEnvironment($app): void
    {
        $app['config']->set('asana.token', 'test-token');
        $app['config']->set('asana.timeout', 30);
    }
```

- [ ] **Step 7: Update the README config block**

In `README.md`, the "Config contents" block (lines 24–33) becomes:

```php
return [
    'token' => env('ASANA_TOKEN'),
    'timeout' => env('ASANA_TIMEOUT', 30),
];
```

- [ ] **Step 8: Verify nothing references retry config anymore**

Run: `grep -rn "retry" src config tests README.md | grep -v "RateLimit\|retryAfter\|Retry-After\|getRetryAfter\|retry after"`
Expected: no output.

- [ ] **Step 9: Run the suite and static analysis**

Run: `composer test`
Expected: `Tests:    187 passed`.

Run: `composer analyse`
Expected: `[OK] No errors`.

- [ ] **Step 10: Format and commit**

```bash
composer format
git add src/AsanaConnector.php src/AsanaServiceProvider.php config/asana.php tests/TestCase.php tests/Unit/AsanaServiceProviderTest.php README.md
git commit -m "refactor: remove never-applied retry configuration

asana.retry.attempts / asana.retry.sleep and the matching AsanaConnector
constructor arguments were stored but never wired to Saloon, so no request
was ever retried. Removed instead of fixed (fixing requires AsanaException
to extend Saloon's RequestException, a breaking change).

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 3: Fix the four mis-pathed requests

**Files:**
- Modify: `src/Requests/Goals/GetSubgoalsRequest.php`
- Modify: `src/Requests/Goals/AddSubgoalRequest.php:21-29`
- Modify: `src/Resources/GoalResource.php:57-62`
- Modify: `src/Requests/Teams/GetTeamsForWorkspaceRequest.php:19-22`
- Create: `src/Requests/Attachments/GetAttachmentsForObjectRequest.php`
- Delete: `src/Requests/Attachments/GetAttachmentsForTaskRequest.php`
- Modify: `src/Resources/AttachmentResource.php`
- Test: `tests/Unit/Resources/GoalResourceTest.php:98-121`, `tests/Unit/Resources/TeamResourceTest.php:37-54`, `tests/Unit/Resources/AttachmentResourceTest.php:40-57`, `tests/ArchTest.php`
- Modify: `README.md` (Goals table rows 805–806, Attachments table row 618)

- [ ] **Step 1: Rewrite the two goal tests to assert the real endpoints**

In `tests/Unit/Resources/GoalResourceTest.php`, add these imports after the existing `use` lines:

```php
use Saloon\Http\Request;
use WMBH\Asana\Data\Shared\CompactResource;
```

Replace the tests `getSubgoals returns PaginatedResponse` and `addSubgoal returns true on success` with:

```php
test('getSubgoals maps goal relationships to subgoal CompactResources', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                [
                    'gid' => '900',
                    'resource_type' => 'goal_relationship',
                    'resource_subtype' => 'subgoal',
                    'supporting_resource' => ['gid' => '10', 'name' => 'Subgoal', 'resource_type' => 'goal'],
                ],
            ],
            'next_page' => ['offset' => 'tok', 'uri' => '/goal_relationships?offset=tok'],
        ], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->getSubgoals('1100');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(CompactResource::class)
        ->and($result->data[0]->gid)->toBe('10')
        ->and($result->data[0]->name)->toBe('Subgoal')
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/goal_relationships'
        && $request->query()->all() === ['supported_goal' => '1100', 'resource_subtype' => 'subgoal']);
});

test('getSubgoals returns an empty page when there are no relationships', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->getSubgoals('1100');

    expect($result->data)->toBe([])
        ->and($result->hasNextPage())->toBeFalse();
});

test('addSubgoal posts a supporting relationship', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => '900', 'resource_type' => 'goal_relationship']], 200),
    ]);

    $resource = createGoalResource($mockClient);

    expect($resource->addSubgoal('1100', 'sub1'))->toBeTrue();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/goals/1100/addSupportingRelationship'
        && $request->body()->all() === ['data' => ['supporting_resource' => 'sub1']]);
});
```

- [ ] **Step 2: Run them to see the endpoint assertions fail**

Run: `vendor/bin/pest tests/Unit/Resources/GoalResourceTest.php --filter "Subgoal|subgoal"`
Expected: FAIL — `getSubgoals maps…` fails with `Failed asserting that null is an instance of class "WMBH\Asana\Data\Shared\CompactResource"` (or a GoalData type error) and `addSubgoal posts…` fails with `An expected request was not sent.`

- [ ] **Step 3: Point the subgoal requests at the real endpoints**

`src/Requests/Goals/GetSubgoalsRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Goals;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetSubgoalsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $goalGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/goal_relationships';
    }

    protected function defaultQuery(): array
    {
        return [
            'supported_goal' => $this->goalGid,
            'resource_subtype' => 'subgoal',
        ];
    }
}
```

`src/Requests/Goals/AddSubgoalRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Goals;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AddSubgoalRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $goalGid,
        protected readonly string $subgoalGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/goals/{$this->goalGid}/addSupportingRelationship";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['supporting_resource' => $this->subgoalGid]];
    }
}
```

- [ ] **Step 4: Map relationships to subgoals in the resource**

In `src/Resources/GoalResource.php`, replace `getSubgoals()` with:

```php
    public function getSubgoals(string $goalGid): PaginatedResponse
    {
        $response = $this->connector->send(new GetSubgoalsRequest($goalGid));
        $json = $response->json();

        return new PaginatedResponse(
            data: array_map(
                fn (array $relationship) => CompactResource::from($relationship['supporting_resource']),
                $json['data'] ?? [],
            ),
            nextPageToken: $json['next_page']['offset'] ?? null,
            nextPageUri: $json['next_page']['uri'] ?? null,
        );
    }
```

(`CompactResource` is already imported in this file.)

- [ ] **Step 5: Run the goal tests**

Run: `vendor/bin/pest tests/Unit/Resources/GoalResourceTest.php`
Expected: all pass (`getSubgoals maps…`, `getSubgoals returns an empty page…`, `addSubgoal posts…` included).

- [ ] **Step 6: Assert the documented teams path**

In `tests/Unit/Resources/TeamResourceTest.php`, add `use Saloon\Http\Request;` to the imports and append to the end of the `getForWorkspace returns PaginatedResponse` test (after the `expect(...)` chain):

```php
    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/teams');
```

Run: `vendor/bin/pest tests/Unit/Resources/TeamResourceTest.php --filter "getForWorkspace"`
Expected: FAIL with `An expected request was not sent.`

- [ ] **Step 7: Fix the teams path**

In `src/Requests/Teams/GetTeamsForWorkspaceRequest.php`, `resolveEndpoint()` becomes:

```php
    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/teams";
    }
```

Run: `vendor/bin/pest tests/Unit/Resources/TeamResourceTest.php`
Expected: all pass.

- [ ] **Step 8: Write the attachment tests for the documented `/attachments?parent=` path**

In `tests/Unit/Resources/AttachmentResourceTest.php`, add `use Saloon\Http\Request;` to the imports, then replace the `getForTask returns PaginatedResponse` test with:

```php
test('getForTask lists attachments via /attachments?parent=', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'file1.pdf', 'resource_type' => 'attachment'],
                ['gid' => '2', 'name' => 'file2.pdf', 'resource_type' => 'attachment'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createAttachmentResource($mockClient);
    $result = $resource->getForTask('task1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(AttachmentData::class);

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/attachments'
        && $request->query()->all() === ['parent' => 'task1']);
});

test('getForObject passes parent, opt_fields, offset and limit', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'brief.pdf', 'resource_type' => 'attachment']],
            'next_page' => ['offset' => 'next', 'uri' => '/attachments?offset=next'],
        ], 200),
    ]);

    $resource = createAttachmentResource($mockClient);
    $result = $resource->getForObject('brief1', ['name', 'download_url'], 'abc', 50);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(AttachmentData::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('next');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/attachments'
        && $request->query()->all() === [
            'parent' => 'brief1',
            'opt_fields' => 'name,download_url',
            'offset' => 'abc',
            'limit' => 50,
        ]);
});
```

Run: `vendor/bin/pest tests/Unit/Resources/AttachmentResourceTest.php`
Expected: FAIL — `getForObject …` errors with `Call to undefined method WMBH\Asana\Resources\AttachmentResource::getForObject()`; `getForTask …` fails with `An expected request was not sent.`

- [ ] **Step 9: Create the object request, delete the task request, wire the resource**

Create `src/Requests/Attachments/GetAttachmentsForObjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Attachments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAttachmentsForObjectRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $parentGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/attachments';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'parent' => $this->parentGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

Run: `git rm src/Requests/Attachments/GetAttachmentsForTaskRequest.php`

`src/Resources/AttachmentResource.php` becomes:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\AttachmentData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\Attachments\CreateAttachmentRequest;
use WMBH\Asana\Requests\Attachments\DeleteAttachmentRequest;
use WMBH\Asana\Requests\Attachments\GetAttachmentRequest;
use WMBH\Asana\Requests\Attachments\GetAttachmentsForObjectRequest;

class AttachmentResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): AttachmentData
    {
        $response = $this->connector->send(new GetAttachmentRequest($gid, $optFields));

        return AttachmentData::from($response->json('data'));
    }

    public function getForObject(string $parentGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetAttachmentsForObjectRequest($parentGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), AttachmentData::class);
    }

    public function getForTask(string $taskGid, array $optFields = []): PaginatedResponse
    {
        return $this->getForObject($taskGid, $optFields);
    }

    public function create(string $parentGid, array $data): AttachmentData
    {
        $response = $this->connector->send(new CreateAttachmentRequest($parentGid, $data));

        return AttachmentData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $this->connector->send(new DeleteAttachmentRequest($gid));

        return true;
    }
}
```

Run: `vendor/bin/pest tests/Unit/Resources/AttachmentResourceTest.php`
Expected: all pass.

- [ ] **Step 10: Arch-test the new request**

Append to the `// ── Requests (Lawman)` block of `tests/ArchTest.php`:

```php
arch('GetAttachmentsForObjectRequest sends GET')
    ->expect('WMBH\Asana\Requests\Attachments\GetAttachmentsForObjectRequest')
    ->toSendGetRequest();
```

- [ ] **Step 11: Update the README rows**

Goals table: replace the two rows

```markdown
| `getSubgoals` | `string $goalGid` | `PaginatedResponse` | List subgoals (items are `CompactResource`) |
| `addSubgoal` | `string $goalGid`, `string $subgoalGid` | `bool` | Add a subgoal (creates a supporting relationship) |
```

Attachments table: after the `getForTask` row add

```markdown
| `getForObject` | `string $parentGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List attachments on a task, project or project brief |
```

- [ ] **Step 12: Full suite, analysis, format, commit**

Run: `composer test` — Expected: all pass (`190 passed`).
Run: `composer analyse` — Expected: `[OK] No errors`.

```bash
composer format
git add -A src/Requests/Goals src/Requests/Teams src/Requests/Attachments src/Resources/GoalResource.php src/Resources/AttachmentResource.php tests/Unit/Resources/GoalResourceTest.php tests/Unit/Resources/TeamResourceTest.php tests/Unit/Resources/AttachmentResourceTest.php tests/ArchTest.php README.md
git commit -m "fix: point subgoal, team and attachment requests at documented endpoints

- getSubgoals/addSubgoal used /goals/{gid}/subgoals and /addSubgoal, which
  do not exist; now use goal relationships (resource_subtype=subgoal /
  addSupportingRelationship). getSubgoals items are CompactResource.
- getTeamsForWorkspace used the undocumented /organizations/ path.
- Attachments listing now uses GET /attachments?parent= (new getForObject;
  getForTask delegates to it). GetAttachmentsForTaskRequest removed.

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 4: `JobData` (shared by templates, task duplicate, save-as-template)

**Files:**
- Create: `src/Data/JobData.php`
- Test: `tests/Unit/Data/JobDataTest.php`

- [ ] **Step 1: Write the failing DTO test**

Create `tests/Unit/Data/JobDataTest.php`:

```php
<?php

use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\Shared\CompactResource;

test('JobData can be created from array', function () {
    $data = JobData::from([
        'gid' => '777',
        'resource_type' => 'job',
        'resource_subtype' => 'instantiate_task',
        'status' => 'in_progress',
    ]);

    expect($data->gid)->toBe('777')
        ->and($data->resource_type)->toBe('job')
        ->and($data->resource_subtype)->toBe('instantiate_task')
        ->and($data->status)->toBe('in_progress');
});

test('JobData handles null optional fields', function () {
    $data = JobData::from(['gid' => '777']);

    expect($data->status)->toBeNull()
        ->and($data->new_task)->toBeNull()
        ->and($data->new_project)->toBeNull()
        ->and($data->new_portfolio)->toBeNull()
        ->and($data->new_project_template)->toBeNull()
        ->and($data->new_graph_export)->toBeNull()
        ->and($data->new_resource_export)->toBeNull();
});

test('JobData casts new_task and new_project to CompactResource', function () {
    $data = JobData::from([
        'gid' => '777',
        'status' => 'succeeded',
        'new_task' => ['gid' => '1', 'name' => 'From template', 'resource_type' => 'task'],
        'new_project' => ['gid' => '2', 'name' => 'New project', 'resource_type' => 'project'],
    ]);

    expect($data->new_task)->toBeInstanceOf(CompactResource::class)
        ->and($data->new_task->gid)->toBe('1')
        ->and($data->new_project)->toBeInstanceOf(CompactResource::class)
        ->and($data->new_project->name)->toBe('New project');
});

test('JobData keeps export payloads as arrays', function () {
    $data = JobData::from([
        'gid' => '777',
        'new_graph_export' => ['gid' => '9', 'download_url' => 'https://example.test/x.csv'],
    ]);

    expect($data->new_graph_export)->toBe(['gid' => '9', 'download_url' => 'https://example.test/x.csv']);
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `vendor/bin/pest tests/Unit/Data/JobDataTest.php`
Expected: FAIL with `Class "WMBH\Asana\Data\JobData" not found`.

- [ ] **Step 3: Create the DTO**

Create `src/Data/JobData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class JobData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?string $status = null,
        public readonly ?CompactResource $new_project = null,
        public readonly ?CompactResource $new_task = null,
        public readonly ?CompactResource $new_portfolio = null,
        public readonly ?CompactResource $new_project_template = null,
        public readonly ?array $new_graph_export = null,
        public readonly ?array $new_resource_export = null,
    ) {}
}
```

- [ ] **Step 4: Run it to verify it passes**

Run: `vendor/bin/pest tests/Unit/Data/JobDataTest.php`
Expected: `Tests:    4 passed`.

- [ ] **Step 5: Format and commit**

```bash
composer format
git add src/Data/JobData.php tests/Unit/Data/JobDataTest.php
git commit -m "feat(data): add JobData for asynchronous job responses

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 5: Task templates

**Files:**
- Create: `src/Requests/TaskTemplates/GetTaskTemplatesRequest.php`
- Create: `src/Requests/TaskTemplates/GetTaskTemplateRequest.php`
- Create: `src/Requests/TaskTemplates/DeleteTaskTemplateRequest.php`
- Create: `src/Requests/TaskTemplates/InstantiateTaskRequest.php`
- Create: `src/Resources/TaskTemplateResource.php`
- Create: `src/Data/TaskTemplateData.php`
- Modify: `src/Asana.php` (new property + accessor)
- Modify: `tests/Unit/AsanaTest.php` (accessor assertion)
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (TOC + new "Task Templates" section after "Task Search (Query Builder)")
- Test: `tests/Unit/Resources/TaskTemplateResourceTest.php`
- Test: `tests/Unit/Data/TaskTemplateDataTest.php`

- [ ] **Step 1: Write the failing DTO test**

`tests/Unit/Data/TaskTemplateDataTest.php`:

```php
<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\TaskTemplateData;

test('TaskTemplateData can be created from array', function () {
    $data = TaskTemplateData::from([
        'gid' => '1',
        'resource_type' => 'task_template',
        'name' => 'Bug report',
        'created_at' => '2026-01-01T00:00:00.000Z',
    ]);

    expect($data->gid)->toBe('1')
        ->and($data->resource_type)->toBe('task_template')
        ->and($data->name)->toBe('Bug report')
        ->and($data->created_at)->toBe('2026-01-01T00:00:00.000Z');
});

test('TaskTemplateData handles null optional fields', function () {
    $data = TaskTemplateData::from(['gid' => '1']);

    expect($data->gid)->toBe('1')
        ->and($data->name)->toBeNull()
        ->and($data->project)->toBeNull()
        ->and($data->template)->toBeNull()
        ->and($data->created_by)->toBeNull()
        ->and($data->created_at)->toBeNull();
});

test('TaskTemplateData casts nested project and created_by to CompactResource', function () {
    $data = TaskTemplateData::from([
        'gid' => '1',
        'project' => ['gid' => '10', 'name' => 'Engineering', 'resource_type' => 'project'],
        'created_by' => ['gid' => '20', 'name' => 'Jane', 'resource_type' => 'user'],
        'template' => ['name' => 'Bug report', 'notes' => 'Steps to reproduce'],
    ]);

    expect($data->project)->toBeInstanceOf(CompactResource::class)
        ->and($data->project->gid)->toBe('10')
        ->and($data->created_by)->toBeInstanceOf(CompactResource::class)
        ->and($data->created_by->name)->toBe('Jane')
        ->and($data->template)->toBe(['name' => 'Bug report', 'notes' => 'Steps to reproduce']);
});
```

- [ ] **Step 2: Write the failing Resource test**

`tests/Unit/Resources/TaskTemplateResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TaskTemplateData;
use WMBH\Asana\Requests\TaskTemplates\InstantiateTaskRequest;
use WMBH\Asana\Resources\TaskTemplateResource;

function createTaskTemplateResource(MockClient $mockClient): TaskTemplateResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new TaskTemplateResource($connector);
}

test('list returns PaginatedResponse of TaskTemplateData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Bug report', 'resource_type' => 'task_template'],
                ['gid' => '2', 'name' => 'Feature request', 'resource_type' => 'task_template'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTaskTemplateResource($mockClient);
    $result = $resource->list('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(TaskTemplateData::class)
        ->and($result->data[0]->gid)->toBe('1')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/task_templates'
        && $request->query()->all() === ['project' => 'proj1']);
});

test('list forwards opt_fields, offset and limit', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => ['offset' => 'tok', 'uri' => '/task_templates?offset=tok']], 200),
    ]);

    $resource = createTaskTemplateResource($mockClient);
    $result = $resource->list('proj1', ['name', 'template'], 'abc', 50);

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === [
        'project' => 'proj1',
        'opt_fields' => 'name,template',
        'offset' => 'abc',
        'limit' => 50,
    ]);
});

test('get returns TaskTemplateData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1',
            'name' => 'Bug report',
            'resource_type' => 'task_template',
        ]], 200),
    ]);

    $resource = createTaskTemplateResource($mockClient);
    $result = $resource->get('1');

    expect($result)->toBeInstanceOf(TaskTemplateData::class)
        ->and($result->gid)->toBe('1')
        ->and($result->name)->toBe('Bug report');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/task_templates/1');
});

test('delete returns true on 200', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTaskTemplateResource($mockClient);

    expect($resource->delete('1'))->toBeTrue();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/task_templates/1');
});

test('instantiate returns JobData and sends name', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'job1',
            'resource_type' => 'job',
            'resource_subtype' => 'instantiate_task',
            'status' => 'in_progress',
            'new_task' => ['gid' => 't1', 'name' => 'Bug: login', 'resource_type' => 'task'],
        ]], 201),
    ]);

    $resource = createTaskTemplateResource($mockClient);
    $result = $resource->instantiate('1', 'Bug: login');

    expect($result)->toBeInstanceOf(JobData::class)
        ->and($result->gid)->toBe('job1')
        ->and($result->status)->toBe('in_progress')
        ->and($result->new_task->gid)->toBe('t1');

    $mockClient->assertSent(fn (Request $request) => $request instanceof InstantiateTaskRequest
        && $request->resolveEndpoint() === '/task_templates/1/instantiateTask'
        && $request->body()->all() === ['data' => ['name' => 'Bug: login']]);
});

test('instantiate without name sends an empty data object', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => 'job1', 'resource_type' => 'job', 'status' => 'not_started']], 201),
    ]);

    $resource = createTaskTemplateResource($mockClient);
    $resource->instantiate('1');

    $mockClient->assertSent(fn (Request $request) => $request->body()->all()['data'] instanceof stdClass);
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/TaskTemplateDataTest.php tests/Unit/Resources/TaskTemplateResourceTest.php`
Expected: FAIL with `Error: Class "WMBH\Asana\Data\TaskTemplateData" not found` and `Error: Class "WMBH\Asana\Resources\TaskTemplateResource" not found`.

- [ ] **Step 4: Create the DTO**

`src/Data/TaskTemplateData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class TaskTemplateData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?CompactResource $project = null,
        public readonly ?array $template = null,
        public readonly ?CompactResource $created_by = null,
        public readonly ?string $created_at = null,
    ) {}
}
```

- [ ] **Step 5: Create the four Request classes**

`src/Requests/TaskTemplates/GetTaskTemplatesRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\TaskTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTaskTemplatesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $projectGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/task_templates';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'project' => $this->projectGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

`src/Requests/TaskTemplates/GetTaskTemplateRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\TaskTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTaskTemplateRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/task_templates/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

`src/Requests/TaskTemplates/DeleteTaskTemplateRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\TaskTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteTaskTemplateRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/task_templates/{$this->gid}";
    }
}
```

`src/Requests/TaskTemplates/InstantiateTaskRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\TaskTemplates;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
use stdClass;

class InstantiateTaskRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly ?string $name = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/task_templates/{$this->gid}/instantiateTask";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        // Asana requires "data" to be a JSON object; an empty PHP array would encode as [].
        return ['data' => $this->name === null ? new stdClass : ['name' => $this->name]];
    }
}
```

- [ ] **Step 6: Create the Resource**

`src/Resources/TaskTemplateResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TaskTemplateData;
use WMBH\Asana\Requests\TaskTemplates\DeleteTaskTemplateRequest;
use WMBH\Asana\Requests\TaskTemplates\GetTaskTemplateRequest;
use WMBH\Asana\Requests\TaskTemplates\GetTaskTemplatesRequest;
use WMBH\Asana\Requests\TaskTemplates\InstantiateTaskRequest;

class TaskTemplateResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function list(string $projectGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTaskTemplatesRequest($projectGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TaskTemplateData::class);
    }

    public function get(string $gid, array $optFields = []): TaskTemplateData
    {
        $response = $this->connector->send(new GetTaskTemplateRequest($gid, $optFields));

        return TaskTemplateData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteTaskTemplateRequest($gid));

        return $response->status() === 200;
    }

    public function instantiate(string $gid, ?string $name = null, array $optFields = []): JobData
    {
        $response = $this->connector->send(new InstantiateTaskRequest($gid, $name, $optFields));

        return JobData::from($response->json('data'));
    }
}
```

- [ ] **Step 7: Wire the accessor into `Asana`**

In `src/Asana.php` add the import, the property, and the accessor (place next to the existing `taskResource` ones):

```php
use WMBH\Asana\Resources\TaskTemplateResource;
```

```php
    private ?TaskTemplateResource $taskTemplateResource = null;
```

```php
    public function taskTemplates(): TaskTemplateResource
    {
        return $this->taskTemplateResource ??= new TaskTemplateResource($this->connector);
    }
```

In `tests/Unit/AsanaTest.php` add the import and extend the `'Asana class returns resource instances'` chain:

```php
use WMBH\Asana\Resources\TaskTemplateResource;
```

```php
        ->and($asana->taskTemplates())->toBeInstanceOf(TaskTemplateResource::class)
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/TaskTemplateDataTest.php tests/Unit/Resources/TaskTemplateResourceTest.php tests/Unit/AsanaTest.php`
Expected: PASS (3 + 6 + 5 tests).

- [ ] **Step 9: Add ArchTest entries**

Append under the `// ── Requests (Lawman)` block in `tests/ArchTest.php`:

```php
arch('GetTaskTemplateRequest sends GET')
    ->expect('WMBH\Asana\Requests\TaskTemplates\GetTaskTemplateRequest')
    ->toSendGetRequest();

arch('InstantiateTaskRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\TaskTemplates\InstantiateTaskRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('DeleteTaskTemplateRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\TaskTemplates\DeleteTaskTemplateRequest')
    ->toSendDeleteRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS.

- [ ] **Step 10: Document in README**

In the Table of Contents, after `- [Task Search (Query Builder)](#task-search-query-builder)` add:

```markdown
- [Task Templates](#task-templates)
```

Insert a new section immediately before `### Projects`:

```markdown
### Task Templates

Access via `Asana::taskTemplates()` — returns `TaskTemplateResource`. Instantiating a template is asynchronous: Asana returns a job; poll it with [`Asana::jobs()->get()`](#jobs) until `status` is `succeeded`, then read `new_task`.

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `list` | `string $projectGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List task templates in a project |
| `get` | `string $gid`, `array $optFields = []` | `TaskTemplateData` | Get a task template |
| `delete` | `string $gid` | `bool` | Delete a task template |
| `instantiate` | `string $gid`, `?string $name = null`, `array $optFields = []` | `JobData` | Create a task from the template (async) |

```php
// List templates in a project
$templates = Asana::taskTemplates()->list('project_gid');

// Instantiate a template, optionally overriding the task name
$job = Asana::taskTemplates()->instantiate('template_gid', 'Bug: login broken');

// Poll the job until it finishes
do {
    sleep(1);
    $job = Asana::jobs()->get($job->gid);
} while (in_array($job->status, ['not_started', 'in_progress'], true));

$taskGid = $job->new_task?->gid;
```

#### TaskTemplateData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"task_template"` |
| `name` | `?string` | Template name |
| `project` | `?CompactResource` | Project the template belongs to |
| `template` | `?array` | The task fields the template applies (name, notes, assignee, …) |
| `created_by` | `?CompactResource` | User who created the template |
| `created_at` | `?string` | Creation timestamp |

---
```

- [ ] **Step 11: Format and commit**

```bash
composer format
composer test
git add src/Requests/TaskTemplates src/Resources/TaskTemplateResource.php src/Data/TaskTemplateData.php src/Asana.php tests/Unit/AsanaTest.php tests/ArchTest.php tests/Unit/Resources/TaskTemplateResourceTest.php tests/Unit/Data/TaskTemplateDataTest.php README.md
git commit -m "feat(task-templates): add task template resource with instantiateTask

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 6: Project templates

**Files:**
- Create: `src/Requests/ProjectTemplates/GetProjectTemplatesRequest.php`
- Create: `src/Requests/ProjectTemplates/GetProjectTemplatesForTeamRequest.php`
- Create: `src/Requests/ProjectTemplates/GetProjectTemplateRequest.php`
- Create: `src/Requests/ProjectTemplates/DeleteProjectTemplateRequest.php`
- Create: `src/Requests/ProjectTemplates/InstantiateProjectRequest.php`
- Create: `src/Requests/Projects/SaveProjectAsTemplateRequest.php`
- Create: `src/Resources/ProjectTemplateResource.php`
- Create: `src/Data/ProjectTemplateData.php`
- Modify: `src/Resources/ProjectResource.php` (add `saveAsTemplate`)
- Modify: `src/Asana.php` (new property + accessor)
- Modify: `tests/Unit/AsanaTest.php`
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (TOC, new "Project Templates" section after "Projects", `saveAsTemplate` row in Projects table)
- Test: `tests/Unit/Resources/ProjectTemplateResourceTest.php`
- Test: `tests/Unit/Data/ProjectTemplateDataTest.php`
- Test: `tests/Unit/Resources/ProjectResourceTest.php` (append)

- [ ] **Step 1: Write the failing DTO test**

`tests/Unit/Data/ProjectTemplateDataTest.php`:

```php
<?php

use WMBH\Asana\Data\ProjectTemplateData;
use WMBH\Asana\Data\Shared\CompactResource;

test('ProjectTemplateData can be created from array', function () {
    $data = ProjectTemplateData::from([
        'gid' => '1',
        'resource_type' => 'project_template',
        'name' => 'Sprint',
        'description' => 'Two-week sprint',
        'html_description' => '<body>Two-week sprint</body>',
        'public' => true,
        'color' => 'light-green',
    ]);

    expect($data->gid)->toBe('1')
        ->and($data->resource_type)->toBe('project_template')
        ->and($data->name)->toBe('Sprint')
        ->and($data->description)->toBe('Two-week sprint')
        ->and($data->html_description)->toBe('<body>Two-week sprint</body>')
        ->and($data->public)->toBeTrue()
        ->and($data->color)->toBe('light-green');
});

test('ProjectTemplateData handles null optional fields', function () {
    $data = ProjectTemplateData::from(['gid' => '1']);

    expect($data->gid)->toBe('1')
        ->and($data->name)->toBeNull()
        ->and($data->public)->toBeNull()
        ->and($data->owner)->toBeNull()
        ->and($data->team)->toBeNull()
        ->and($data->requested_dates)->toBeNull()
        ->and($data->requested_roles)->toBeNull();
});

test('ProjectTemplateData casts nested owner and team to CompactResource', function () {
    $data = ProjectTemplateData::from([
        'gid' => '1',
        'owner' => ['gid' => '20', 'name' => 'Jane', 'resource_type' => 'user'],
        'team' => ['gid' => '30', 'name' => 'Engineering', 'resource_type' => 'team'],
        'requested_dates' => [['gid' => '40', 'name' => 'Start date']],
        'requested_roles' => [['gid' => '50', 'name' => 'Lead']],
    ]);

    expect($data->owner)->toBeInstanceOf(CompactResource::class)
        ->and($data->owner->gid)->toBe('20')
        ->and($data->team)->toBeInstanceOf(CompactResource::class)
        ->and($data->team->name)->toBe('Engineering')
        ->and($data->requested_dates)->toHaveCount(1)
        ->and($data->requested_roles[0]['name'])->toBe('Lead');
});
```

- [ ] **Step 2: Write the failing Resource tests**

`tests/Unit/Resources/ProjectTemplateResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\ProjectTemplateData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\ProjectTemplates\InstantiateProjectRequest;
use WMBH\Asana\Resources\ProjectTemplateResource;

function createProjectTemplateResource(MockClient $mockClient): ProjectTemplateResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new ProjectTemplateResource($connector);
}

test('list returns PaginatedResponse of ProjectTemplateData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Sprint', 'resource_type' => 'project_template'],
                ['gid' => '2', 'name' => 'Launch', 'resource_type' => 'project_template'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createProjectTemplateResource($mockClient);
    $result = $resource->list('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(ProjectTemplateData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/project_templates'
        && $request->query()->all() === ['workspace' => 'ws1']);
});

test('list forwards team, opt_fields, offset and limit', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createProjectTemplateResource($mockClient);
    $resource->list(null, 'team1', ['name'], 'abc', 10);

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === [
        'team' => 'team1',
        'opt_fields' => 'name',
        'offset' => 'abc',
        'limit' => 10,
    ]);
});

test('getForTeam returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'Sprint', 'resource_type' => 'project_template']],
            'next_page' => ['offset' => 'tok', 'uri' => '/teams/team1/project_templates?offset=tok'],
        ], 200),
    ]);

    $resource = createProjectTemplateResource($mockClient);
    $result = $resource->getForTeam('team1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(ProjectTemplateData::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/teams/team1/project_templates');
});

test('get returns ProjectTemplateData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1',
            'name' => 'Sprint',
            'resource_type' => 'project_template',
        ]], 200),
    ]);

    $resource = createProjectTemplateResource($mockClient);
    $result = $resource->get('1', ['name']);

    expect($result)->toBeInstanceOf(ProjectTemplateData::class)
        ->and($result->gid)->toBe('1');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/project_templates/1'
        && $request->query()->all() === ['opt_fields' => 'name']);
});

test('delete returns true on 200', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createProjectTemplateResource($mockClient);

    expect($resource->delete('1'))->toBeTrue();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/project_templates/1');
});

test('instantiate returns JobData and sends data envelope', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'job1',
            'resource_type' => 'job',
            'resource_subtype' => 'instantiate_project',
            'status' => 'in_progress',
            'new_project' => ['gid' => 'p1', 'name' => 'Sprint 42', 'resource_type' => 'project'],
        ]], 201),
    ]);

    $resource = createProjectTemplateResource($mockClient);
    $result = $resource->instantiate('1', ['name' => 'Sprint 42', 'team' => 'team1', 'public' => false]);

    expect($result)->toBeInstanceOf(JobData::class)
        ->and($result->gid)->toBe('job1')
        ->and($result->new_project->gid)->toBe('p1');

    $mockClient->assertSent(fn (Request $request) => $request instanceof InstantiateProjectRequest
        && $request->resolveEndpoint() === '/project_templates/1/instantiateProject'
        && $request->body()->all() === ['data' => ['name' => 'Sprint 42', 'team' => 'team1', 'public' => false]]);
});
```

Append to `tests/Unit/Resources/ProjectResourceTest.php` (add `use Saloon\Http\Request;`, `use WMBH\Asana\Data\JobData;` and `use WMBH\Asana\Requests\Projects\SaveProjectAsTemplateRequest;` to the imports at the top of that file):

```php
test('saveAsTemplate returns JobData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'job1',
            'resource_type' => 'job',
            'resource_subtype' => 'save_as_template',
            'status' => 'not_started',
            'new_project_template' => ['gid' => 'pt1', 'name' => 'Sprint', 'resource_type' => 'project_template'],
        ]], 201),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->saveAsTemplate('p1', ['name' => 'Sprint', 'team' => 'team1', 'public' => true]);

    expect($result)->toBeInstanceOf(JobData::class)
        ->and($result->new_project_template->gid)->toBe('pt1');

    $mockClient->assertSent(fn (Request $request) => $request instanceof SaveProjectAsTemplateRequest
        && $request->resolveEndpoint() === '/projects/p1/saveAsTemplate'
        && $request->body()->all() === ['data' => ['name' => 'Sprint', 'team' => 'team1', 'public' => true]]);
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/ProjectTemplateDataTest.php tests/Unit/Resources/ProjectTemplateResourceTest.php tests/Unit/Resources/ProjectResourceTest.php`
Expected: FAIL with `Error: Class "WMBH\Asana\Data\ProjectTemplateData" not found`, `Error: Class "WMBH\Asana\Resources\ProjectTemplateResource" not found`, and `Error: Call to undefined method WMBH\Asana\Resources\ProjectResource::saveAsTemplate()`.

- [ ] **Step 4: Create the DTO**

`src/Data/ProjectTemplateData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class ProjectTemplateData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $html_description = null,
        public readonly ?bool $public = null,
        public readonly ?CompactResource $owner = null,
        public readonly ?CompactResource $team = null,
        public readonly ?array $requested_dates = null,
        public readonly ?array $requested_roles = null,
        public readonly ?string $color = null,
    ) {}
}
```

- [ ] **Step 5: Create the Request classes**

`src/Requests/ProjectTemplates/GetProjectTemplatesRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\ProjectTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectTemplatesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly ?string $workspaceGid = null,
        protected readonly ?string $teamGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/project_templates';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'team' => $this->teamGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

`src/Requests/ProjectTemplates/GetProjectTemplatesForTeamRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\ProjectTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectTemplatesForTeamRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $teamGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/teams/{$this->teamGid}/project_templates";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

`src/Requests/ProjectTemplates/GetProjectTemplateRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\ProjectTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectTemplateRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/project_templates/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

`src/Requests/ProjectTemplates/DeleteProjectTemplateRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\ProjectTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteProjectTemplateRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/project_templates/{$this->gid}";
    }
}
```

`src/Requests/ProjectTemplates/InstantiateProjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\ProjectTemplates;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class InstantiateProjectRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/project_templates/{$this->gid}/instantiateProject";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

`src/Requests/Projects/SaveProjectAsTemplateRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SaveProjectAsTemplateRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}/saveAsTemplate";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

- [ ] **Step 6: Create the Resource and extend `ProjectResource`**

`src/Resources/ProjectTemplateResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\ProjectTemplateData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\ProjectTemplates\DeleteProjectTemplateRequest;
use WMBH\Asana\Requests\ProjectTemplates\GetProjectTemplateRequest;
use WMBH\Asana\Requests\ProjectTemplates\GetProjectTemplatesForTeamRequest;
use WMBH\Asana\Requests\ProjectTemplates\GetProjectTemplatesRequest;
use WMBH\Asana\Requests\ProjectTemplates\InstantiateProjectRequest;

class ProjectTemplateResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function list(?string $workspaceGid = null, ?string $teamGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetProjectTemplatesRequest($workspaceGid, $teamGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), ProjectTemplateData::class);
    }

    public function getForTeam(string $teamGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetProjectTemplatesForTeamRequest($teamGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), ProjectTemplateData::class);
    }

    public function get(string $gid, array $optFields = []): ProjectTemplateData
    {
        $response = $this->connector->send(new GetProjectTemplateRequest($gid, $optFields));

        return ProjectTemplateData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteProjectTemplateRequest($gid));

        return $response->status() === 200;
    }

    public function instantiate(string $gid, array $data, array $optFields = []): JobData
    {
        $response = $this->connector->send(new InstantiateProjectRequest($gid, $data, $optFields));

        return JobData::from($response->json('data'));
    }
}
```

In `src/Resources/ProjectResource.php` add the imports:

```php
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Requests\Projects\SaveProjectAsTemplateRequest;
```

and the method (after `getTaskCounts`):

```php
    public function saveAsTemplate(string $gid, array $data, array $optFields = []): JobData
    {
        $response = $this->connector->send(new SaveProjectAsTemplateRequest($gid, $data, $optFields));

        return JobData::from($response->json('data'));
    }
```

- [ ] **Step 7: Wire the accessor into `Asana`**

In `src/Asana.php`:

```php
use WMBH\Asana\Resources\ProjectTemplateResource;
```

```php
    private ?ProjectTemplateResource $projectTemplateResource = null;
```

```php
    public function projectTemplates(): ProjectTemplateResource
    {
        return $this->projectTemplateResource ??= new ProjectTemplateResource($this->connector);
    }
```

In `tests/Unit/AsanaTest.php`:

```php
use WMBH\Asana\Resources\ProjectTemplateResource;
```

```php
        ->and($asana->projectTemplates())->toBeInstanceOf(ProjectTemplateResource::class)
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/ProjectTemplateDataTest.php tests/Unit/Resources/ProjectTemplateResourceTest.php tests/Unit/Resources/ProjectResourceTest.php tests/Unit/AsanaTest.php`
Expected: PASS.

- [ ] **Step 9: Add ArchTest entries**

Append to `tests/ArchTest.php`:

```php
arch('GetProjectTemplateRequest sends GET')
    ->expect('WMBH\Asana\Requests\ProjectTemplates\GetProjectTemplateRequest')
    ->toSendGetRequest();

arch('InstantiateProjectRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\ProjectTemplates\InstantiateProjectRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('SaveProjectAsTemplateRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Projects\SaveProjectAsTemplateRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('DeleteProjectTemplateRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\ProjectTemplates\DeleteProjectTemplateRequest')
    ->toSendDeleteRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS.

- [ ] **Step 10: Document in README**

TOC: after `- [Projects](#projects)` add:

```markdown
- [Project Templates](#project-templates)
```

Projects table (`### Projects`): add the row after `getTaskCounts`:

```markdown
| `saveAsTemplate` | `string $gid`, `array $data`, `array $optFields = []` | `JobData` | Save the project as a project template (async) |
```

Insert a new section immediately before `### Sections`:

```markdown
### Project Templates

Access via `Asana::projectTemplates()` — returns `ProjectTemplateResource`. Instantiating a template is asynchronous: poll the returned job with [`Asana::jobs()->get()`](#jobs) and read `new_project` once `status` is `succeeded`.

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `list` | `?string $workspaceGid = null`, `?string $teamGid = null`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List project templates (filter by workspace or team) |
| `getForTeam` | `string $teamGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List project templates in a team |
| `get` | `string $gid`, `array $optFields = []` | `ProjectTemplateData` | Get a project template |
| `delete` | `string $gid` | `bool` | Delete a project template |
| `instantiate` | `string $gid`, `array $data`, `array $optFields = []` | `JobData` | Create a project from the template (async) |

```php
// List templates in a workspace
$templates = Asana::projectTemplates()->list('workspace_gid');

// Create a project from a template
$job = Asana::projectTemplates()->instantiate('template_gid', [
    'name' => 'Sprint 42',
    'team' => 'team_gid',
    'public' => false,
    'requested_dates' => [['gid' => 'requested_date_gid', 'value' => '2026-10-01']],
]);

// Save an existing project as a template
$job = Asana::projects()->saveAsTemplate('project_gid', [
    'name' => 'Sprint template',
    'team' => 'team_gid',
    'public' => true,
]);
```

#### ProjectTemplateData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"project_template"` |
| `name` | `?string` | Template name |
| `description` | `?string` | Description |
| `html_description` | `?string` | Description with HTML formatting |
| `public` | `?bool` | Whether the template is public to the team |
| `owner` | `?CompactResource` | Owner |
| `team` | `?CompactResource` | Team |
| `requested_dates` | `?array` | Dates the template asks for on instantiation |
| `requested_roles` | `?array` | Roles the template asks for on instantiation |
| `color` | `?string` | Color |

---
```

- [ ] **Step 11: Format and commit**

```bash
composer format
composer test
git add src/Requests/ProjectTemplates src/Requests/Projects/SaveProjectAsTemplateRequest.php src/Resources/ProjectTemplateResource.php src/Resources/ProjectResource.php src/Data/ProjectTemplateData.php src/Asana.php tests/Unit/AsanaTest.php tests/ArchTest.php tests/Unit/Resources/ProjectTemplateResourceTest.php tests/Unit/Resources/ProjectResourceTest.php tests/Unit/Data/ProjectTemplateDataTest.php README.md
git commit -m "feat(project-templates): add project template resource and saveAsTemplate

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 7: Jobs

**Files:**
- Create: `src/Requests/Jobs/GetJobRequest.php`
- Create: `src/Resources/JobResource.php`
- Modify: `src/Asana.php` (new property + accessor)
- Modify: `tests/Unit/AsanaTest.php`
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (TOC + new "Jobs" section before "Batch Requests")
- Test: `tests/Unit/Resources/JobResourceTest.php`

`JobData` (`src/Data/JobData.php`) already exists from Task 4 with: `gid`, `resource_type`, `resource_subtype`, `status`, `new_project`, `new_task`, `new_portfolio`, `new_project_template` (`?CompactResource`), `new_graph_export`, `new_resource_export` (`?array`).

- [ ] **Step 1: Write the failing Resource test**

`tests/Unit/Resources/JobResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Resources\JobResource;

function createJobResource(MockClient $mockClient): JobResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new JobResource($connector);
}

test('get returns JobData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'job1',
            'resource_type' => 'job',
            'resource_subtype' => 'duplicate_task',
            'status' => 'succeeded',
            'new_task' => ['gid' => 't2', 'name' => 'Copy of task', 'resource_type' => 'task'],
        ]], 200),
    ]);

    $resource = createJobResource($mockClient);
    $result = $resource->get('job1');

    expect($result)->toBeInstanceOf(JobData::class)
        ->and($result->gid)->toBe('job1')
        ->and($result->status)->toBe('succeeded')
        ->and($result->new_task->gid)->toBe('t2');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/jobs/job1'
        && $request->query()->all() === []);
});

test('get forwards opt_fields', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => 'job1', 'resource_type' => 'job', 'status' => 'in_progress']], 200),
    ]);

    $resource = createJobResource($mockClient);
    $resource->get('job1', ['status', 'new_task']);

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === ['opt_fields' => 'status,new_task']);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/pest tests/Unit/Resources/JobResourceTest.php`
Expected: FAIL with `Error: Class "WMBH\Asana\Resources\JobResource" not found`.

- [ ] **Step 3: Create the Request and Resource**

`src/Requests/Jobs/GetJobRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Jobs;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetJobRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/jobs/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

`src/Resources/JobResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Requests\Jobs\GetJobRequest;

class JobResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): JobData
    {
        $response = $this->connector->send(new GetJobRequest($gid, $optFields));

        return JobData::from($response->json('data'));
    }
}
```

- [ ] **Step 4: Wire the accessor into `Asana`**

In `src/Asana.php`:

```php
use WMBH\Asana\Resources\JobResource;
```

```php
    private ?JobResource $jobResource = null;
```

```php
    public function jobs(): JobResource
    {
        return $this->jobResource ??= new JobResource($this->connector);
    }
```

In `tests/Unit/AsanaTest.php`:

```php
use WMBH\Asana\Resources\JobResource;
```

```php
        ->and($asana->jobs())->toBeInstanceOf(JobResource::class)
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Resources/JobResourceTest.php tests/Unit/AsanaTest.php`
Expected: PASS.

- [ ] **Step 6: Add ArchTest entry**

Append to `tests/ArchTest.php`:

```php
arch('GetJobRequest sends GET')
    ->expect('WMBH\Asana\Requests\Jobs\GetJobRequest')
    ->toSendGetRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS.

- [ ] **Step 7: Document in README**

TOC: before `- [Batch Requests](#batch-requests)` add:

```markdown
- [Jobs](#jobs)
```

Insert a new section immediately before `### Batch Requests`:

```markdown
### Jobs

Access via `Asana::jobs()` — returns `JobResource`. Asynchronous operations (`tasks()->duplicate()`, `projects()->duplicate()`, `projects()->saveAsTemplate()`, `taskTemplates()->instantiate()`, `projectTemplates()->instantiate()`) return a `JobData`; poll it here.

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `get` | `string $gid`, `array $optFields = []` | `JobData` | Get a job's status and result |

```php
$job = Asana::tasks()->duplicate('task_gid', ['name' => 'Copy', 'include' => 'notes,assignee']);

do {
    sleep(1);
    $job = Asana::jobs()->get($job->gid);
} while (in_array($job->status, ['not_started', 'in_progress'], true));

if ($job->status === 'succeeded') {
    $newTaskGid = $job->new_task->gid;
}
```

#### JobData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"job"` |
| `resource_subtype` | `?string` | `"duplicate_task"`, `"duplicate_project"`, `"instantiate_task"`, `"instantiate_project"`, `"save_as_template"`, … |
| `status` | `?string` | `"not_started"`, `"in_progress"`, `"succeeded"`, or `"failed"` |
| `new_task` | `?CompactResource` | Resulting task, if any |
| `new_project` | `?CompactResource` | Resulting project, if any |
| `new_portfolio` | `?CompactResource` | Resulting portfolio, if any |
| `new_project_template` | `?CompactResource` | Resulting project template, if any |
| `new_graph_export` | `?array` | Resulting graph export (`download_url`, `completed_at`), if any |
| `new_resource_export` | `?array` | Resulting resource export, if any |

---
```

- [ ] **Step 8: Format and commit**

```bash
composer format
composer test
git add src/Requests/Jobs src/Resources/JobResource.php src/Asana.php tests/Unit/AsanaTest.php tests/ArchTest.php tests/Unit/Resources/JobResourceTest.php README.md
git commit -m "feat(jobs): add job resource for polling async operations

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 8: Tasks + tags

**Files:**
- Create: `src/Requests/Tasks/GetTasksRequest.php`
- Create: `src/Requests/Tasks/DuplicateTaskRequest.php`
- Create: `src/Requests/Tasks/GetTasksForTagRequest.php`
- Create: `src/Requests/Tasks/GetTasksForUserTaskListRequest.php`
- Create: `src/Requests/Tasks/CreateSubtaskRequest.php`
- Create: `src/Requests/Tasks/RemoveDependenciesRequest.php`
- Create: `src/Requests/Tasks/RemoveDependentsRequest.php`
- Create: `src/Requests/Tasks/RemoveFollowersRequest.php`
- Create: `src/Requests/Tasks/GetTaskByCustomIdRequest.php`
- Create: `src/Requests/Tags/GetTagsRequest.php`
- Modify: `src/Resources/TaskResource.php` (9 methods)
- Modify: `src/Resources/TagResource.php` (`list`)
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (rows in Tasks and Tags tables + examples)
- Test: `tests/Unit/Resources/TaskResourceTest.php` (append)
- Test: `tests/Unit/Resources/TagResourceTest.php` (append)

- [ ] **Step 1: Write the failing Task tests**

Add these imports to the top of `tests/Unit/Resources/TaskResourceTest.php` (keep existing ones):

```php
use Saloon\Http\Request;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Requests\Tasks\CreateSubtaskRequest;
use WMBH\Asana\Requests\Tasks\DuplicateTaskRequest;
use WMBH\Asana\Requests\Tasks\RemoveDependenciesRequest;
use WMBH\Asana\Requests\Tasks\RemoveDependentsRequest;
use WMBH\Asana\Requests\Tasks\RemoveFollowersRequest;
```

Append to the end of the file:

```php
test('list returns PaginatedResponse and forwards params', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Task 1', 'resource_type' => 'task'],
                ['gid' => '2', 'name' => 'Task 2', 'resource_type' => 'task'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->list(['assignee' => 'me', 'workspace' => 'ws1', 'completed_since' => 'now'], ['name'], 'abc', 20);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(TaskData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/tasks'
        && $request->query()->all() === [
            'assignee' => 'me',
            'workspace' => 'ws1',
            'completed_since' => 'now',
            'opt_fields' => 'name',
            'offset' => 'abc',
            'limit' => 20,
        ]);
});

test('duplicate returns JobData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'job1',
            'resource_type' => 'job',
            'resource_subtype' => 'duplicate_task',
            'status' => 'in_progress',
            'new_task' => ['gid' => 't2', 'name' => 'Copy', 'resource_type' => 'task'],
        ]], 201),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->duplicate('t1', ['name' => 'Copy', 'include' => 'notes,assignee']);

    expect($result)->toBeInstanceOf(JobData::class)
        ->and($result->gid)->toBe('job1')
        ->and($result->new_task->gid)->toBe('t2');

    $mockClient->assertSent(fn (Request $request) => $request instanceof DuplicateTaskRequest
        && $request->resolveEndpoint() === '/tasks/t1/duplicate'
        && $request->body()->all() === ['data' => ['name' => 'Copy', 'include' => 'notes,assignee']]);
});

test('getForTag returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'Tagged', 'resource_type' => 'task']],
            'next_page' => ['offset' => 'tok', 'uri' => '/tags/tag1/tasks?offset=tok'],
        ], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->getForTag('tag1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(TaskData::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/tags/tag1/tasks');
});

test('getForUserTaskList returns PaginatedResponse and forwards completed_since', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'My task', 'resource_type' => 'task']],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->getForUserTaskList('utl1', ['name'], null, 50, 'now');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1);

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/user_task_lists/utl1/tasks'
        && $request->query()->all() === ['opt_fields' => 'name', 'limit' => 50, 'completed_since' => 'now']);
});

test('createSubtask returns TaskData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'sub1',
            'name' => 'Subtask',
            'resource_type' => 'task',
            'parent' => ['gid' => 't1', 'name' => 'Parent', 'resource_type' => 'task'],
        ]], 201),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->createSubtask('t1', ['name' => 'Subtask']);

    expect($result)->toBeInstanceOf(TaskData::class)
        ->and($result->gid)->toBe('sub1')
        ->and($result->parent->gid)->toBe('t1');

    $mockClient->assertSent(fn (Request $request) => $request instanceof CreateSubtaskRequest
        && $request->resolveEndpoint() === '/tasks/t1/subtasks'
        && $request->body()->all() === ['data' => ['name' => 'Subtask']]);
});

test('removeDependencies sends dependency gids', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $resource->removeDependencies('t1', ['d1', 'd2']);

    $mockClient->assertSent(fn (Request $request) => $request instanceof RemoveDependenciesRequest
        && $request->resolveEndpoint() === '/tasks/t1/removeDependencies'
        && $request->body()->all() === ['data' => ['dependencies' => ['d1', 'd2']]]);
});

test('removeDependents sends dependent gids', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $resource->removeDependents('t1', ['d3']);

    $mockClient->assertSent(fn (Request $request) => $request instanceof RemoveDependentsRequest
        && $request->resolveEndpoint() === '/tasks/t1/removeDependents'
        && $request->body()->all() === ['data' => ['dependents' => ['d3']]]);
});

test('removeFollowers sends followers', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => 't1', 'resource_type' => 'task']], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $resource->removeFollowers('t1', ['u1', 'u2']);

    $mockClient->assertSent(fn (Request $request) => $request instanceof RemoveFollowersRequest
        && $request->resolveEndpoint() === '/tasks/t1/removeFollowers'
        && $request->body()->all() === ['data' => ['followers' => ['u1', 'u2']]]);
});

test('getByCustomId returns TaskData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 't9',
            'name' => 'Custom ID task',
            'resource_type' => 'task',
        ]], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->getByCustomId('ws1', 'ENG-42');

    expect($result)->toBeInstanceOf(TaskData::class)
        ->and($result->gid)->toBe('t9');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/tasks/custom_id/ENG-42');
});
```

- [ ] **Step 2: Write the failing Tag test**

Add `use Saloon\Http\Request;` to the imports of `tests/Unit/Resources/TagResourceTest.php` and append:

```php
test('list returns PaginatedResponse and forwards workspace', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Priority', 'resource_type' => 'tag'],
                ['gid' => '2', 'name' => 'Urgent', 'resource_type' => 'tag'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTagResource($mockClient);
    $result = $resource->list('ws1', ['name'], 'abc', 10);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(TagData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/tags'
        && $request->query()->all() === ['workspace' => 'ws1', 'opt_fields' => 'name', 'offset' => 'abc', 'limit' => 10]);
});

test('list without workspace sends no query', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createTagResource($mockClient);
    $resource->list();

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === []);
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Resources/TaskResourceTest.php tests/Unit/Resources/TagResourceTest.php`
Expected: FAIL with `Error: Call to undefined method WMBH\Asana\Resources\TaskResource::list()` (and the other 8 new methods) and `Error: Call to undefined method WMBH\Asana\Resources\TagResource::list()`.

- [ ] **Step 4: Create the Task request classes**

`src/Requests/Tasks/GetTasksRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTasksRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly array $params = [],
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/tasks';
    }

    protected function defaultQuery(): array
    {
        return array_filter(array_merge($this->params, [
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]));
    }
}
```

`src/Requests/Tasks/DuplicateTaskRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class DuplicateTaskRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->gid}/duplicate";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

`src/Requests/Tasks/GetTasksForTagRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTasksForTagRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $tagGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tags/{$this->tagGid}/tasks";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

`src/Requests/Tasks/GetTasksForUserTaskListRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTasksForUserTaskListRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $userTaskListGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
        protected readonly ?string $completedSince = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/user_task_lists/{$this->userTaskListGid}/tasks";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'completed_since' => $this->completedSince,
        ]);
    }
}
```

`src/Requests/Tasks/CreateSubtaskRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateSubtaskRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/subtasks";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

`src/Requests/Tasks/RemoveDependenciesRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveDependenciesRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly array $dependencyGids,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/removeDependencies";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['dependencies' => $this->dependencyGids]];
    }
}
```

`src/Requests/Tasks/RemoveDependentsRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveDependentsRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly array $dependentGids,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/removeDependents";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['dependents' => $this->dependentGids]];
    }
}
```

`src/Requests/Tasks/RemoveFollowersRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveFollowersRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly array $followers,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/removeFollowers";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['followers' => $this->followers]];
    }
}
```

`src/Requests/Tasks/GetTaskByCustomIdRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTaskByCustomIdRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly string $customId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/tasks/custom_id/{$this->customId}";
    }
}
```

- [ ] **Step 5: Create the Tag request class**

`src/Requests/Tags/GetTagsRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Tags;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTagsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly ?string $workspaceGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/tags';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

- [ ] **Step 6: Extend `TaskResource`**

Add these imports to `src/Resources/TaskResource.php` (keep the existing ones, alphabetical order as Pint expects):

```php
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Requests\Tasks\CreateSubtaskRequest;
use WMBH\Asana\Requests\Tasks\DuplicateTaskRequest;
use WMBH\Asana\Requests\Tasks\GetTaskByCustomIdRequest;
use WMBH\Asana\Requests\Tasks\GetTasksForTagRequest;
use WMBH\Asana\Requests\Tasks\GetTasksForUserTaskListRequest;
use WMBH\Asana\Requests\Tasks\GetTasksRequest;
use WMBH\Asana\Requests\Tasks\RemoveDependenciesRequest;
use WMBH\Asana\Requests\Tasks\RemoveDependentsRequest;
use WMBH\Asana\Requests\Tasks\RemoveFollowersRequest;
```

Add these methods to the class (after `addDependents`):

```php
    public function list(array $params = [], array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTasksRequest($params, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TaskData::class);
    }

    public function duplicate(string $gid, array $data, array $optFields = []): JobData
    {
        $response = $this->connector->send(new DuplicateTaskRequest($gid, $data, $optFields));

        return JobData::from($response->json('data'));
    }

    public function getForTag(string $tagGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTasksForTagRequest($tagGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TaskData::class);
    }

    public function getForUserTaskList(string $userTaskListGid, array $optFields = [], ?string $offset = null, ?int $limit = null, ?string $completedSince = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTasksForUserTaskListRequest($userTaskListGid, $optFields, $offset, $limit, $completedSince));

        return PaginatedResponse::fromResponse($response->json(), TaskData::class);
    }

    public function createSubtask(string $taskGid, array $data, array $optFields = []): TaskData
    {
        $response = $this->connector->send(new CreateSubtaskRequest($taskGid, $data, $optFields));

        return TaskData::from($response->json('data'));
    }

    public function removeDependencies(string $taskGid, array $dependencyGids): void
    {
        $this->connector->send(new RemoveDependenciesRequest($taskGid, $dependencyGids));
    }

    public function removeDependents(string $taskGid, array $dependentGids): void
    {
        $this->connector->send(new RemoveDependentsRequest($taskGid, $dependentGids));
    }

    public function removeFollowers(string $taskGid, array $followers): void
    {
        $this->connector->send(new RemoveFollowersRequest($taskGid, $followers));
    }

    public function getByCustomId(string $workspaceGid, string $customId): TaskData
    {
        $response = $this->connector->send(new GetTaskByCustomIdRequest($workspaceGid, $customId));

        return TaskData::from($response->json('data'));
    }
```

- [ ] **Step 7: Extend `TagResource`**

Add the import to `src/Resources/TagResource.php`:

```php
use WMBH\Asana\Requests\Tags\GetTagsRequest;
```

Add the method (after `get`):

```php
    public function list(?string $workspaceGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTagsRequest($workspaceGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TagData::class);
    }
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Resources/TaskResourceTest.php tests/Unit/Resources/TagResourceTest.php`
Expected: PASS.

- [ ] **Step 9: Add ArchTest entries**

Append to `tests/ArchTest.php`:

```php
arch('GetTasksRequest sends GET')
    ->expect('WMBH\Asana\Requests\Tasks\GetTasksRequest')
    ->toSendGetRequest();

arch('GetTaskByCustomIdRequest sends GET')
    ->expect('WMBH\Asana\Requests\Tasks\GetTaskByCustomIdRequest')
    ->toSendGetRequest();

arch('DuplicateTaskRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Tasks\DuplicateTaskRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('CreateSubtaskRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Tasks\CreateSubtaskRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('RemoveFollowersRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Tasks\RemoveFollowersRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('GetTagsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Tags\GetTagsRequest')
    ->toSendGetRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS.

- [ ] **Step 10: Document in README**

In the `### Tasks` table, add these rows after `addDependents`:

```markdown
| `list` | `array $params = []`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List tasks by `assignee`, `project`, `section`, `workspace`, `completed_since`, `modified_since`, `custom_type` |
| `duplicate` | `string $gid`, `array $data`, `array $optFields = []` | `JobData` | Duplicate a task (async, see [Jobs](#jobs)) |
| `getForTag` | `string $tagGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List tasks with a tag |
| `getForUserTaskList` | `string $userTaskListGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null`, `?string $completedSince = null` | `PaginatedResponse` | List tasks in a user's My Tasks list |
| `createSubtask` | `string $taskGid`, `array $data`, `array $optFields = []` | `TaskData` | Create a subtask under a task |
| `removeDependencies` | `string $taskGid`, `array $dependencyGids` | `void` | Remove dependencies from a task |
| `removeDependents` | `string $taskGid`, `array $dependentGids` | `void` | Remove dependents from a task |
| `removeFollowers` | `string $taskGid`, `array $followers` | `void` | Remove followers from a task |
| `getByCustomId` | `string $workspaceGid`, `string $customId` | `TaskData` | Get a task by its custom ID (e.g. `ENG-42`) |
```

In the Tasks code example, add after the `// Dependencies` block:

```php
// List tasks across a workspace for the current user
$page = Asana::tasks()->list(['assignee' => 'me', 'workspace' => 'workspace_gid', 'completed_since' => 'now']);

// Subtasks, duplication and custom IDs
$subtask = Asana::tasks()->createSubtask('task_gid', ['name' => 'Write tests']);
$job = Asana::tasks()->duplicate('task_gid', ['name' => 'Copy of task', 'include' => 'notes,assignee,subtasks']);
$task = Asana::tasks()->getByCustomId('workspace_gid', 'ENG-42');

// Removing relationships
Asana::tasks()->removeFollowers('task_gid', ['user_gid_1']);
Asana::tasks()->removeDependencies('task_gid', ['blocker_task_1']);
Asana::tasks()->removeDependents('task_gid', ['blocked_task_1']);
```

In the `### Tags` table, add the row after `get`:

```markdown
| `list` | `?string $workspaceGid = null`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List tags, optionally filtered by workspace |
```

In the Tags code example, add at the top:

```php
// List tags, paginated
$page = Asana::tags()->list('workspace_gid', limit: 50);
```

- [ ] **Step 11: Format and commit**

```bash
composer format
composer test
git add src/Requests/Tasks src/Requests/Tags/GetTagsRequest.php src/Resources/TaskResource.php src/Resources/TagResource.php tests/ArchTest.php tests/Unit/Resources/TaskResourceTest.php tests/Unit/Resources/TagResourceTest.php README.md
git commit -m "feat(tasks): add list, duplicate, subtasks, custom id, remove* and tags list

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---


---

### Task 9: Status updates

**Files:**
- Create: `src/Requests/StatusUpdates/GetStatusUpdateRequest.php`
- Create: `src/Requests/StatusUpdates/GetStatusUpdatesForObjectRequest.php`
- Create: `src/Requests/StatusUpdates/CreateStatusUpdateRequest.php`
- Create: `src/Requests/StatusUpdates/DeleteStatusUpdateRequest.php`
- Create: `src/Data/StatusUpdateData.php`
- Create: `src/Resources/StatusUpdateResource.php`
- Modify: `src/Asana.php` (property, import, accessor)
- Modify: `tests/Unit/AsanaTest.php` (import + assertion line)
- Modify: `tests/ArchTest.php` (3 entries)
- Modify: `README.md` (TOC line + new section before `### Batch Requests`)
- Test: `tests/Unit/Resources/StatusUpdateResourceTest.php`
- Test: `tests/Unit/Data/StatusUpdateDataTest.php`

- [ ] **Step 1: Write the failing tests**

`tests/Unit/Data/StatusUpdateDataTest.php`:

```php
<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\StatusUpdateData;

test('StatusUpdateData can be created from array', function () {
    $data = StatusUpdateData::from([
        'gid' => '700',
        'resource_type' => 'status_update',
        'resource_subtype' => 'project_status_update',
        'title' => 'On track',
        'text' => 'All good',
        'status_type' => 'on_track',
    ]);

    expect($data->gid)->toBe('700')
        ->and($data->resource_subtype)->toBe('project_status_update')
        ->and($data->title)->toBe('On track')
        ->and($data->text)->toBe('All good')
        ->and($data->status_type)->toBe('on_track');
});

test('StatusUpdateData handles null optional fields', function () {
    $data = StatusUpdateData::from(['gid' => '700']);

    expect($data->gid)->toBe('700')
        ->and($data->title)->toBeNull()
        ->and($data->text)->toBeNull()
        ->and($data->html_text)->toBeNull()
        ->and($data->status_type)->toBeNull()
        ->and($data->author)->toBeNull()
        ->and($data->created_by)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->hearted)->toBeNull()
        ->and($data->hearts)->toBeNull()
        ->and($data->num_likes)->toBeNull()
        ->and($data->reaction_summary)->toBeNull();
});

test('StatusUpdateData casts nested author to CompactResource', function () {
    $data = StatusUpdateData::from([
        'gid' => '700',
        'author' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
    ]);

    expect($data->author)->toBeInstanceOf(CompactResource::class)
        ->and($data->author->gid)->toBe('111')
        ->and($data->author->name)->toBe('Jane');
});

test('StatusUpdateData casts nested parent to CompactResource', function () {
    $data = StatusUpdateData::from([
        'gid' => '700',
        'parent' => ['gid' => '900', 'name' => 'Project X', 'resource_type' => 'project'],
    ]);

    expect($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->parent->gid)->toBe('900');
});
```

`tests/Unit/Resources/StatusUpdateResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\StatusUpdateData;
use WMBH\Asana\Resources\StatusUpdateResource;

function createStatusUpdateResource(MockClient $mockClient): StatusUpdateResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new StatusUpdateResource($connector);
}

test('get returns StatusUpdateData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '700',
            'resource_type' => 'status_update',
            'title' => 'On track',
            'status_type' => 'on_track',
        ]], 200),
    ]);

    $resource = createStatusUpdateResource($mockClient);
    $result = $resource->get('700');

    expect($result)->toBeInstanceOf(StatusUpdateData::class)
        ->and($result->gid)->toBe('700')
        ->and($result->title)->toBe('On track')
        ->and($result->status_type)->toBe('on_track');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/status_updates/700';
    });
});

test('getForObject returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'title' => 'Update 1', 'resource_type' => 'status_update'],
                ['gid' => '2', 'title' => 'Update 2', 'resource_type' => 'status_update'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createStatusUpdateResource($mockClient);
    $result = $resource->getForObject('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(StatusUpdateData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(function ($request) {
        $query = $request->query()->all();

        return $request->resolveEndpoint() === '/status_updates'
            && $query['parent'] === 'proj1'
            && ! isset($query['created_since']);
    });
});

test('getForObject passes pagination and created_since', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'status_update']],
            'next_page' => ['offset' => 'abc', 'uri' => '/status_updates?offset=abc'],
        ], 200),
    ]);

    $resource = createStatusUpdateResource($mockClient);
    $result = $resource->getForObject('proj1', ['title'], 'off1', 10, '2025-01-01T00:00:00Z');

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('abc');

    $mockClient->assertSent(function ($request) {
        $query = $request->query()->all();

        return $query['opt_fields'] === 'title'
            && $query['offset'] === 'off1'
            && $query['limit'] === 10
            && $query['created_since'] === '2025-01-01T00:00:00Z';
    });
});

test('create returns StatusUpdateData and sends parent in body', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '701',
            'resource_type' => 'status_update',
            'text' => 'Shipping Friday',
            'status_type' => 'on_track',
        ]], 201),
    ]);

    $resource = createStatusUpdateResource($mockClient);
    $result = $resource->create('proj1', ['text' => 'Shipping Friday', 'status_type' => 'on_track']);

    expect($result)->toBeInstanceOf(StatusUpdateData::class)
        ->and($result->gid)->toBe('701')
        ->and($result->text)->toBe('Shipping Friday');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/status_updates'
            && $request->body()->all() === ['data' => [
                'parent' => 'proj1',
                'text' => 'Shipping Friday',
                'status_type' => 'on_track',
            ]];
    });
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createStatusUpdateResource($mockClient);

    expect($resource->delete('700'))->toBeTrue();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/status_updates/700';
    });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/StatusUpdateDataTest.php tests/Unit/Resources/StatusUpdateResourceTest.php`
Expected: FAIL with `Error: Class "WMBH\Asana\Data\StatusUpdateData" not found` and `Error: Class "WMBH\Asana\Resources\StatusUpdateResource" not found`

- [ ] **Step 3: Implement DTO, requests, resource, accessor**

`src/Data/StatusUpdateData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class StatusUpdateData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?string $title = null,
        public readonly ?string $text = null,
        public readonly ?string $html_text = null,
        public readonly ?string $status_type = null,
        public readonly ?CompactResource $author = null,
        public readonly ?string $created_at = null,
        public readonly ?CompactResource $created_by = null,
        public readonly ?string $modified_at = null,
        public readonly ?bool $hearted = null,
        public readonly ?array $hearts = null,
        public readonly ?bool $liked = null,
        public readonly ?array $likes = null,
        public readonly ?array $reaction_summary = null,
        public readonly ?int $num_hearts = null,
        public readonly ?int $num_likes = null,
        public readonly ?CompactResource $parent = null,
    ) {}
}
```

`src/Requests/StatusUpdates/GetStatusUpdateRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\StatusUpdates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetStatusUpdateRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/status_updates/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

`src/Requests/StatusUpdates/GetStatusUpdatesForObjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\StatusUpdates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetStatusUpdatesForObjectRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $parentGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
        protected readonly ?string $createdSince = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/status_updates';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'parent' => $this->parentGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'created_since' => $this->createdSince,
        ]);
    }
}
```

`src/Requests/StatusUpdates/CreateStatusUpdateRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\StatusUpdates;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateStatusUpdateRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $parentGid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/status_updates';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => array_merge(['parent' => $this->parentGid], $this->data)];
    }
}
```

`src/Requests/StatusUpdates/DeleteStatusUpdateRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\StatusUpdates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteStatusUpdateRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/status_updates/{$this->gid}";
    }
}
```

`src/Resources/StatusUpdateResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\StatusUpdateData;
use WMBH\Asana\Requests\StatusUpdates\CreateStatusUpdateRequest;
use WMBH\Asana\Requests\StatusUpdates\DeleteStatusUpdateRequest;
use WMBH\Asana\Requests\StatusUpdates\GetStatusUpdateRequest;
use WMBH\Asana\Requests\StatusUpdates\GetStatusUpdatesForObjectRequest;

class StatusUpdateResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): StatusUpdateData
    {
        $response = $this->connector->send(new GetStatusUpdateRequest($gid, $optFields));

        return StatusUpdateData::from($response->json('data'));
    }

    public function getForObject(string $parentGid, array $optFields = [], ?string $offset = null, ?int $limit = null, ?string $createdSince = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetStatusUpdatesForObjectRequest($parentGid, $optFields, $offset, $limit, $createdSince));

        return PaginatedResponse::fromResponse($response->json(), StatusUpdateData::class);
    }

    public function create(string $parentGid, array $data, array $optFields = []): StatusUpdateData
    {
        $response = $this->connector->send(new CreateStatusUpdateRequest($parentGid, $data, $optFields));

        return StatusUpdateData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteStatusUpdateRequest($gid));

        return $response->status() === 200;
    }
}
```

`src/Asana.php` — add the import (keep the `use` block alphabetical):

```php
use WMBH\Asana\Resources\StatusUpdateResource;
```

add the property after `private ?BatchResource $batchResource = null;`:

```php
    private ?StatusUpdateResource $statusUpdateResource = null;
```

add the accessor after the `batch()` method (before `testConnection()`):

```php
    public function statusUpdates(): StatusUpdateResource
    {
        return $this->statusUpdateResource ??= new StatusUpdateResource($this->connector);
    }
```

`tests/Unit/AsanaTest.php` — add the import (alphabetical in the `use` block):

```php
use WMBH\Asana\Resources\StatusUpdateResource;
```

and inside the `'Asana class returns resource instances'` test, insert this line immediately after `->and($asana->webhooks())->toBeInstanceOf(WebhookResource::class)`:

```php
        ->and($asana->statusUpdates())->toBeInstanceOf(StatusUpdateResource::class)
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/StatusUpdateDataTest.php tests/Unit/Resources/StatusUpdateResourceTest.php tests/Unit/AsanaTest.php`
Expected: PASS (4 + 5 + 5 tests)

- [ ] **Step 5: Add ArchTest entries**

Append to the `// ── Requests (Lawman)` section of `tests/ArchTest.php` (after the `DeleteWebhookRequest sends DELETE` block):

```php
arch('GetStatusUpdateRequest sends GET')
    ->expect('WMBH\Asana\Requests\StatusUpdates\GetStatusUpdateRequest')
    ->toSendGetRequest();

arch('CreateStatusUpdateRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\StatusUpdates\CreateStatusUpdateRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('DeleteStatusUpdateRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\StatusUpdates\DeleteStatusUpdateRequest')
    ->toSendDeleteRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS

- [ ] **Step 6: README**

In the Table of Contents, after the line `- [Webhooks](#webhooks)` add:

```markdown
- [Status Updates](#status-updates)
```

Insert the following immediately before the `### Batch Requests` heading:

```markdown
### Status Updates

Access via `Asana::statusUpdates()` — returns `StatusUpdateResource`. Status updates work on projects, portfolios and goals (Asana's replacement for the deprecated project statuses).

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `get` | `string $gid`, `array $optFields = []` | `StatusUpdateData` | Get a status update |
| `getForObject` | `string $parentGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null`, `?string $createdSince = null` | `PaginatedResponse` | List status updates on a project/portfolio/goal |
| `create` | `string $parentGid`, `array $data`, `array $optFields = []` | `StatusUpdateData` | Post a status update |
| `delete` | `string $gid` | `bool` | Delete a status update |

```php
// Post a project status
$update = Asana::statusUpdates()->create('project_gid', [
    'text' => 'Shipping on Friday',
    'status_type' => 'on_track', // on_track, at_risk, off_track, on_hold, complete, achieved, partial, missed, dropped
]);

// Latest updates since a date
$updates = Asana::statusUpdates()->getForObject('project_gid', createdSince: '2025-01-01T00:00:00Z');
```

#### StatusUpdateData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"status_update"` |
| `resource_subtype` | `?string` | `"project_status_update"`, `"portfolio_status_update"` or `"goal_status_update"` |
| `title` | `?string` | Title |
| `text` | `?string` | Plain-text body |
| `html_text` | `?string` | HTML body |
| `status_type` | `?string` | `"on_track"`, `"at_risk"`, `"off_track"`, `"on_hold"`, `"complete"`, ... |
| `author` | `?CompactResource` | Author |
| `created_at` | `?string` | Creation timestamp |
| `created_by` | `?CompactResource` | Creator |
| `modified_at` | `?string` | Last modified timestamp |
| `hearted` | `?bool` | Whether the current user hearted it |
| `hearts` | `?array` | Users who hearted it |
| `liked` | `?bool` | Whether the current user liked it |
| `likes` | `?array` | Users who liked it |
| `reaction_summary` | `?array` | Reaction counts |
| `num_hearts` | `?int` | Number of hearts |
| `num_likes` | `?int` | Number of likes |
| `parent` | `?CompactResource` | Project, portfolio or goal the update belongs to |

---

```

- [ ] **Step 7: Format and commit**

Run: `composer format && composer test`
Expected: Pint reports no changes (or fixes only files from this task); tests PASS.

```bash
git add src/Requests/StatusUpdates src/Data/StatusUpdateData.php src/Resources/StatusUpdateResource.php src/Asana.php tests/Unit/AsanaTest.php tests/ArchTest.php tests/Unit/Resources/StatusUpdateResourceTest.php tests/Unit/Data/StatusUpdateDataTest.php README.md
git commit -m "feat(status-updates): add StatusUpdateResource with get, getForObject, create, delete

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 10: Project briefs

**Files:**
- Create: `src/Requests/ProjectBriefs/GetProjectBriefRequest.php`
- Create: `src/Requests/ProjectBriefs/CreateProjectBriefRequest.php`
- Create: `src/Requests/ProjectBriefs/UpdateProjectBriefRequest.php`
- Create: `src/Requests/ProjectBriefs/DeleteProjectBriefRequest.php`
- Create: `src/Data/ProjectBriefData.php`
- Create: `src/Resources/ProjectBriefResource.php`
- Modify: `src/Asana.php`
- Modify: `tests/Unit/AsanaTest.php`
- Modify: `tests/ArchTest.php`
- Modify: `README.md`
- Test: `tests/Unit/Resources/ProjectBriefResourceTest.php`
- Test: `tests/Unit/Data/ProjectBriefDataTest.php`

- [ ] **Step 1: Write the failing tests**

`tests/Unit/Data/ProjectBriefDataTest.php`:

```php
<?php

use WMBH\Asana\Data\ProjectBriefData;
use WMBH\Asana\Data\Shared\CompactResource;

test('ProjectBriefData can be created from array', function () {
    $data = ProjectBriefData::from([
        'gid' => '800',
        'resource_type' => 'project_brief',
        'title' => 'Launch plan',
        'text' => 'We ship in Q3',
        'permalink_url' => 'https://app.asana.com/0/800',
    ]);

    expect($data->gid)->toBe('800')
        ->and($data->resource_type)->toBe('project_brief')
        ->and($data->title)->toBe('Launch plan')
        ->and($data->text)->toBe('We ship in Q3')
        ->and($data->permalink_url)->toBe('https://app.asana.com/0/800');
});

test('ProjectBriefData handles null optional fields', function () {
    $data = ProjectBriefData::from(['gid' => '800']);

    expect($data->gid)->toBe('800')
        ->and($data->title)->toBeNull()
        ->and($data->html_text)->toBeNull()
        ->and($data->text)->toBeNull()
        ->and($data->permalink_url)->toBeNull()
        ->and($data->project)->toBeNull();
});

test('ProjectBriefData casts nested project to CompactResource', function () {
    $data = ProjectBriefData::from([
        'gid' => '800',
        'project' => ['gid' => '900', 'name' => 'Project X', 'resource_type' => 'project'],
    ]);

    expect($data->project)->toBeInstanceOf(CompactResource::class)
        ->and($data->project->gid)->toBe('900')
        ->and($data->project->name)->toBe('Project X');
});
```

`tests/Unit/Resources/ProjectBriefResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\ProjectBriefData;
use WMBH\Asana\Resources\ProjectBriefResource;

function createProjectBriefResource(MockClient $mockClient): ProjectBriefResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new ProjectBriefResource($connector);
}

test('get returns ProjectBriefData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '800',
            'resource_type' => 'project_brief',
            'title' => 'Launch plan',
        ]], 200),
    ]);

    $resource = createProjectBriefResource($mockClient);
    $result = $resource->get('800', ['title']);

    expect($result)->toBeInstanceOf(ProjectBriefData::class)
        ->and($result->gid)->toBe('800')
        ->and($result->title)->toBe('Launch plan');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/project_briefs/800'
            && $request->query()->all() === ['opt_fields' => 'title'];
    });
});

test('create returns ProjectBriefData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '801',
            'resource_type' => 'project_brief',
            'title' => 'New brief',
            'text' => 'Body',
        ]], 201),
    ]);

    $resource = createProjectBriefResource($mockClient);
    $result = $resource->create('proj1', ['title' => 'New brief', 'text' => 'Body']);

    expect($result)->toBeInstanceOf(ProjectBriefData::class)
        ->and($result->gid)->toBe('801')
        ->and($result->title)->toBe('New brief');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/projects/proj1/project_briefs'
            && $request->body()->all() === ['data' => ['title' => 'New brief', 'text' => 'Body']];
    });
});

test('update returns ProjectBriefData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '800',
            'resource_type' => 'project_brief',
            'title' => 'Renamed',
        ]], 200),
    ]);

    $resource = createProjectBriefResource($mockClient);
    $result = $resource->update('800', ['title' => 'Renamed']);

    expect($result)->toBeInstanceOf(ProjectBriefData::class)
        ->and($result->title)->toBe('Renamed');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/project_briefs/800'
            && $request->body()->all() === ['data' => ['title' => 'Renamed']];
    });
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createProjectBriefResource($mockClient);

    expect($resource->delete('800'))->toBeTrue();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/project_briefs/800';
    });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/ProjectBriefDataTest.php tests/Unit/Resources/ProjectBriefResourceTest.php`
Expected: FAIL with `Error: Class "WMBH\Asana\Data\ProjectBriefData" not found` and `Error: Class "WMBH\Asana\Resources\ProjectBriefResource" not found`

- [ ] **Step 3: Implement DTO, requests, resource, accessor**

`src/Data/ProjectBriefData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class ProjectBriefData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $title = null,
        public readonly ?string $html_text = null,
        public readonly ?string $text = null,
        public readonly ?string $permalink_url = null,
        public readonly ?CompactResource $project = null,
    ) {}
}
```

`src/Requests/ProjectBriefs/GetProjectBriefRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\ProjectBriefs;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectBriefRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/project_briefs/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

`src/Requests/ProjectBriefs/CreateProjectBriefRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\ProjectBriefs;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateProjectBriefRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $projectGid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->projectGid}/project_briefs";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

`src/Requests/ProjectBriefs/UpdateProjectBriefRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\ProjectBriefs;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateProjectBriefRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/project_briefs/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

`src/Requests/ProjectBriefs/DeleteProjectBriefRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\ProjectBriefs;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteProjectBriefRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/project_briefs/{$this->gid}";
    }
}
```

`src/Resources/ProjectBriefResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\ProjectBriefData;
use WMBH\Asana\Requests\ProjectBriefs\CreateProjectBriefRequest;
use WMBH\Asana\Requests\ProjectBriefs\DeleteProjectBriefRequest;
use WMBH\Asana\Requests\ProjectBriefs\GetProjectBriefRequest;
use WMBH\Asana\Requests\ProjectBriefs\UpdateProjectBriefRequest;

class ProjectBriefResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): ProjectBriefData
    {
        $response = $this->connector->send(new GetProjectBriefRequest($gid, $optFields));

        return ProjectBriefData::from($response->json('data'));
    }

    public function create(string $projectGid, array $data, array $optFields = []): ProjectBriefData
    {
        $response = $this->connector->send(new CreateProjectBriefRequest($projectGid, $data, $optFields));

        return ProjectBriefData::from($response->json('data'));
    }

    public function update(string $gid, array $data, array $optFields = []): ProjectBriefData
    {
        $response = $this->connector->send(new UpdateProjectBriefRequest($gid, $data, $optFields));

        return ProjectBriefData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteProjectBriefRequest($gid));

        return $response->status() === 200;
    }
}
```

`src/Asana.php` — add the import (alphabetical):

```php
use WMBH\Asana\Resources\ProjectBriefResource;
```

add the property after `private ?BatchResource $batchResource = null;`:

```php
    private ?ProjectBriefResource $projectBriefResource = null;
```

add the accessor after the `batch()` method:

```php
    public function projectBriefs(): ProjectBriefResource
    {
        return $this->projectBriefResource ??= new ProjectBriefResource($this->connector);
    }
```

`tests/Unit/AsanaTest.php` — add the import:

```php
use WMBH\Asana\Resources\ProjectBriefResource;
```

and insert immediately after `->and($asana->webhooks())->toBeInstanceOf(WebhookResource::class)`:

```php
        ->and($asana->projectBriefs())->toBeInstanceOf(ProjectBriefResource::class)
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/ProjectBriefDataTest.php tests/Unit/Resources/ProjectBriefResourceTest.php tests/Unit/AsanaTest.php`
Expected: PASS

- [ ] **Step 5: Add ArchTest entries**

Append to the `// ── Requests (Lawman)` section of `tests/ArchTest.php`:

```php
arch('GetProjectBriefRequest sends GET')
    ->expect('WMBH\Asana\Requests\ProjectBriefs\GetProjectBriefRequest')
    ->toSendGetRequest();

arch('CreateProjectBriefRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\ProjectBriefs\CreateProjectBriefRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('UpdateProjectBriefRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\ProjectBriefs\UpdateProjectBriefRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('DeleteProjectBriefRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\ProjectBriefs\DeleteProjectBriefRequest')
    ->toSendDeleteRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS

- [ ] **Step 6: README**

In the Table of Contents, after `- [Status Updates](#status-updates)` add:

```markdown
- [Project Briefs](#project-briefs)
```

Insert immediately before the `### Batch Requests` heading:

```markdown
### Project Briefs

Access via `Asana::projectBriefs()` — returns `ProjectBriefResource`. A project has at most one brief.

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `get` | `string $gid`, `array $optFields = []` | `ProjectBriefData` | Get a project brief |
| `create` | `string $projectGid`, `array $data`, `array $optFields = []` | `ProjectBriefData` | Create the brief for a project |
| `update` | `string $gid`, `array $data`, `array $optFields = []` | `ProjectBriefData` | Update a brief |
| `delete` | `string $gid` | `bool` | Delete a brief |

```php
$brief = Asana::projectBriefs()->create('project_gid', [
    'title' => 'Launch plan',
    'html_text' => '<body><strong>Goal:</strong> ship in Q3</body>',
]);

Asana::projectBriefs()->update($brief->gid, ['title' => 'Launch plan v2']);
```

#### ProjectBriefData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"project_brief"` |
| `title` | `?string` | Title |
| `html_text` | `?string` | HTML body |
| `text` | `?string` | Plain-text body |
| `permalink_url` | `?string` | URL to the brief in Asana |
| `project` | `?CompactResource` | Owning project |

---

```

- [ ] **Step 7: Format and commit**

Run: `composer format && composer test`
Expected: tests PASS.

```bash
git add src/Requests/ProjectBriefs src/Data/ProjectBriefData.php src/Resources/ProjectBriefResource.php src/Asana.php tests/Unit/AsanaTest.php tests/ArchTest.php tests/Unit/Resources/ProjectBriefResourceTest.php tests/Unit/Data/ProjectBriefDataTest.php README.md
git commit -m "feat(project-briefs): add ProjectBriefResource with get, create, update, delete

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 11: Events

**Files:**
- Create: `src/Requests/Events/GetEventsRequest.php`
- Create: `src/Requests/Events/GetWorkspaceEventsRequest.php`
- Create: `src/Data/EventData.php`
- Create: `src/Data/Shared/EventsResponse.php`
- Create: `src/Resources/EventResource.php`
- Modify: `src/Asana.php`
- Modify: `tests/Unit/AsanaTest.php`
- Modify: `tests/ArchTest.php` (2 request entries + `ignoring` list)
- Modify: `README.md`
- Test: `tests/Unit/Resources/EventResourceTest.php`
- Test: `tests/Unit/Data/EventDataTest.php`
- Test: `tests/Unit/Data/Shared/EventsResponseTest.php`

- [ ] **Step 1: Write the failing tests**

`tests/Unit/Data/EventDataTest.php`:

```php
<?php

use WMBH\Asana\Data\EventData;
use WMBH\Asana\Data\Shared\CompactResource;

test('EventData can be created from array', function () {
    $data = EventData::from([
        'type' => 'task',
        'action' => 'changed',
        'created_at' => '2025-01-01T00:00:00.000Z',
        'change' => ['field' => 'name', 'action' => 'changed', 'new_value' => 'Renamed'],
    ]);

    expect($data->type)->toBe('task')
        ->and($data->action)->toBe('changed')
        ->and($data->created_at)->toBe('2025-01-01T00:00:00.000Z')
        ->and($data->change)->toBe(['field' => 'name', 'action' => 'changed', 'new_value' => 'Renamed']);
});

test('EventData has no required fields', function () {
    $data = EventData::from([]);

    expect($data->user)->toBeNull()
        ->and($data->resource)->toBeNull()
        ->and($data->type)->toBeNull()
        ->and($data->action)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->created_at)->toBeNull()
        ->and($data->change)->toBeNull();
});

test('EventData casts nested user, resource and parent to CompactResource', function () {
    $data = EventData::from([
        'user' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
        'resource' => ['gid' => '222', 'name' => 'Task', 'resource_type' => 'task'],
        'parent' => ['gid' => '333', 'name' => 'Project', 'resource_type' => 'project'],
    ]);

    expect($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->user->gid)->toBe('111')
        ->and($data->resource)->toBeInstanceOf(CompactResource::class)
        ->and($data->resource->gid)->toBe('222')
        ->and($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->parent->gid)->toBe('333');
});
```

`tests/Unit/Data/Shared/EventsResponseTest.php`:

```php
<?php

use WMBH\Asana\Data\EventData;
use WMBH\Asana\Data\Shared\EventsResponse;

test('EventsResponse::fromResponse maps data, sync and has_more', function () {
    $result = EventsResponse::fromResponse([
        'data' => [
            ['type' => 'task', 'action' => 'added'],
            ['type' => 'task', 'action' => 'changed'],
        ],
        'sync' => 'tok456',
        'has_more' => true,
    ]);

    expect($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(EventData::class)
        ->and($result->data[1]->action)->toBe('changed')
        ->and($result->sync)->toBe('tok456')
        ->and($result->hasMore)->toBeTrue();
});

test('EventsResponse::fromResponse tolerates missing keys', function () {
    $result = EventsResponse::fromResponse([]);

    expect($result->data)->toBe([])
        ->and($result->sync)->toBeNull()
        ->and($result->hasMore)->toBeFalse();
});
```

`tests/Unit/Resources/EventResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\EventData;
use WMBH\Asana\Data\Shared\EventsResponse;
use WMBH\Asana\Exceptions\AsanaException;
use WMBH\Asana\Resources\EventResource;

function createEventResource(MockClient $mockClient): EventResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new EventResource($connector);
}

test('get returns EventsResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['type' => 'task', 'action' => 'changed', 'resource' => ['gid' => '1', 'resource_type' => 'task']],
            ],
            'sync' => 'tok789',
            'has_more' => false,
        ], 200),
    ]);

    $resource = createEventResource($mockClient);
    $result = $resource->get('proj1', 'tok123', ['resource.name']);

    expect($result)->toBeInstanceOf(EventsResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(EventData::class)
        ->and($result->data[0]->action)->toBe('changed')
        ->and($result->sync)->toBe('tok789')
        ->and($result->hasMore)->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/events'
            && $request->query()->all() === [
                'resource' => 'proj1',
                'sync' => 'tok123',
                'opt_fields' => 'resource.name',
            ];
    });
});

test('get without sync token returns the fresh token from a 412 response', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'errors' => [['message' => 'Sync token invalid or too old']],
            'sync' => 'tok123',
        ], 412),
    ]);

    $resource = createEventResource($mockClient);
    $result = $resource->get('proj1');

    expect($result)->toBeInstanceOf(EventsResponse::class)
        ->and($result->data)->toBe([])
        ->and($result->sync)->toBe('tok123')
        ->and($result->hasMore)->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->query()->all() === ['resource' => 'proj1'];
    });
});

test('get rethrows non-412 errors', function () {
    $mockClient = new MockClient([
        MockResponse::make(['errors' => [['message' => 'Server Error']]], 500),
    ]);

    $resource = createEventResource($mockClient);
    $resource->get('proj1', 'tok123');
})->throws(AsanaException::class, 'Server Error');

test('getForWorkspace returns EventsResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['type' => 'project', 'action' => 'added']],
            'sync' => 'tok999',
            'has_more' => true,
        ], 200),
    ]);

    $resource = createEventResource($mockClient);
    $result = $resource->getForWorkspace('ws1', 'tok123');

    expect($result)->toBeInstanceOf(EventsResponse::class)
        ->and($result->data[0]->type)->toBe('project')
        ->and($result->sync)->toBe('tok999')
        ->and($result->hasMore)->toBeTrue();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/workspaces/ws1/events'
            && $request->query()->all() === ['sync' => 'tok123'];
    });
});

test('getForWorkspace without sync token returns the fresh token from a 412 response', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'errors' => [['message' => 'Sync token invalid or too old']],
            'sync' => 'tok123',
        ], 412),
    ]);

    $resource = createEventResource($mockClient);
    $result = $resource->getForWorkspace('ws1');

    expect($result->data)->toBe([])
        ->and($result->sync)->toBe('tok123')
        ->and($result->hasMore)->toBeFalse();
});

test('getForWorkspace rethrows non-412 errors', function () {
    $mockClient = new MockClient([
        MockResponse::make(['errors' => [['message' => 'Server Error']]], 500),
    ]);

    $resource = createEventResource($mockClient);
    $resource->getForWorkspace('ws1', 'tok123');
})->throws(AsanaException::class, 'Server Error');
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/EventDataTest.php tests/Unit/Data/Shared/EventsResponseTest.php tests/Unit/Resources/EventResourceTest.php`
Expected: FAIL with `Error: Class "WMBH\Asana\Data\EventData" not found`, `Error: Class "WMBH\Asana\Data\Shared\EventsResponse" not found`, `Error: Class "WMBH\Asana\Resources\EventResource" not found`

- [ ] **Step 3: Implement DTO, EventsResponse, requests, resource, accessor**

`src/Data/EventData.php` (Asana events carry no `gid`, so nothing is required):

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class EventData extends Data
{
    public function __construct(
        public readonly ?CompactResource $user = null,
        public readonly ?CompactResource $resource = null,
        public readonly ?string $type = null,
        public readonly ?string $action = null,
        public readonly ?CompactResource $parent = null,
        public readonly ?string $created_at = null,
        public readonly ?array $change = null,
    ) {}
}
```

`src/Data/Shared/EventsResponse.php`:

```php
<?php

namespace WMBH\Asana\Data\Shared;

use WMBH\Asana\Data\EventData;

class EventsResponse
{
    public function __construct(
        public readonly array $data,
        public readonly ?string $sync = null,
        public readonly bool $hasMore = false,
    ) {}

    public static function fromResponse(array $response): self
    {
        $items = array_map(
            fn (array $item) => EventData::from($item),
            $response['data'] ?? []
        );

        return new self(
            data: $items,
            sync: $response['sync'] ?? null,
            hasMore: (bool) ($response['has_more'] ?? false),
        );
    }
}
```

`src/Requests/Events/GetEventsRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Events;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetEventsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $resourceGid,
        protected readonly ?string $sync = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/events';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'resource' => $this->resourceGid,
            'sync' => $this->sync,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

`src/Requests/Events/GetWorkspaceEventsRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Events;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWorkspaceEventsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly ?string $sync = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/events";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'sync' => $this->sync,
        ]);
    }
}
```

`src/Resources/EventResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\EventsResponse;
use WMBH\Asana\Exceptions\AsanaException;
use WMBH\Asana\Requests\Events\GetEventsRequest;
use WMBH\Asana\Requests\Events\GetWorkspaceEventsRequest;

class EventResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $resourceGid, ?string $sync = null, array $optFields = []): EventsResponse
    {
        return $this->send(new GetEventsRequest($resourceGid, $sync, $optFields));
    }

    public function getForWorkspace(string $workspaceGid, ?string $sync = null): EventsResponse
    {
        return $this->send(new GetWorkspaceEventsRequest($workspaceGid, $sync));
    }

    /**
     * Asana answers a request without a (valid) sync token with HTTP 412 and a fresh
     * token in the body. Surface that as an empty page carrying the token.
     */
    protected function send(Request $request): EventsResponse
    {
        try {
            $response = $this->connector->send($request);
        } catch (AsanaException $e) {
            if ($e->getCode() !== 412) {
                throw $e;
            }

            return new EventsResponse(data: [], sync: $e->getResponseBody()['sync'] ?? null, hasMore: false);
        }

        return EventsResponse::fromResponse($response->json());
    }
}
```

`src/Asana.php` — add the import (alphabetical):

```php
use WMBH\Asana\Resources\EventResource;
```

add the property after `private ?BatchResource $batchResource = null;`:

```php
    private ?EventResource $eventResource = null;
```

add the accessor after the `batch()` method:

```php
    public function events(): EventResource
    {
        return $this->eventResource ??= new EventResource($this->connector);
    }
```

`tests/Unit/AsanaTest.php` — add the import:

```php
use WMBH\Asana\Resources\EventResource;
```

and insert immediately after `->and($asana->webhooks())->toBeInstanceOf(WebhookResource::class)`:

```php
        ->and($asana->events())->toBeInstanceOf(EventResource::class)
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/EventDataTest.php tests/Unit/Data/Shared/EventsResponseTest.php tests/Unit/Resources/EventResourceTest.php tests/Unit/AsanaTest.php`
Expected: PASS (3 + 2 + 6 + 5 tests)

- [ ] **Step 5: ArchTest — request entries and the `ignoring` list**

Append to the `// ── Requests (Lawman)` section of `tests/ArchTest.php`:

```php
arch('GetEventsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Events\GetEventsRequest')
    ->toSendGetRequest();

arch('GetWorkspaceEventsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Events\GetWorkspaceEventsRequest')
    ->toSendGetRequest();
```

Replace the final `all DTOs extend Data` block of `tests/ArchTest.php` with:

```php
arch('all DTOs extend Data')
    ->expect('WMBH\Asana\Data')
    ->toExtend('Spatie\LaravelData\Data')
    ->ignoring([
        'WMBH\Asana\Data\Shared\PaginatedResponse',
        'WMBH\Asana\Data\Shared\ErrorResponse',
        'WMBH\Asana\Data\Shared\EventsResponse',
    ]);
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS

- [ ] **Step 6: README**

In the Table of Contents, after `- [Project Briefs](#project-briefs)` add:

```markdown
- [Events](#events)
```

Insert immediately before the `### Batch Requests` heading:

```markdown
### Events

Access via `Asana::events()` — returns `EventResource`. Events are a polling feed of changes on a project, task or workspace, driven by a `sync` token.

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `get` | `string $resourceGid`, `?string $sync = null`, `array $optFields = []` | `EventsResponse` | Events on a project or task since `$sync` |
| `getForWorkspace` | `string $workspaceGid`, `?string $sync = null` | `EventsResponse` | Events across a workspace since `$sync` |

The first call has no token. Asana answers it with HTTP 412 and a fresh token; this package turns that into an `EventsResponse` with empty `data` and the token in `sync`, so the loop below just works:

```php
$page = Asana::events()->get('project_gid');           // first call: data = [], sync = fresh token
$sync = $page->sync;

do {
    $page = Asana::events()->get('project_gid', $sync);
    foreach ($page->data as $event) {
        echo "{$event->type} {$event->action} on {$event->resource?->gid}\n";
    }
    $sync = $page->sync;
} while ($page->hasMore);
```

#### EventsResponse Properties

| Property | Type | Description |
|----------|------|-------------|
| `data` | `array` | Array of `EventData` |
| `sync` | `?string` | Token to pass to the next call |
| `hasMore` | `bool` | Whether more events are waiting (Asana caps a page at 100) |

#### EventData Properties

Events have no `gid`; every property is nullable.

| Property | Type | Description |
|----------|------|-------------|
| `user` | `?CompactResource` | User who triggered the event |
| `resource` | `?CompactResource` | Resource that changed |
| `type` | `?string` | Resource type (`"task"`, `"project"`, `"story"`, ...) |
| `action` | `?string` | `"added"`, `"removed"`, `"changed"`, `"deleted"`, `"undeleted"` |
| `parent` | `?CompactResource` | Parent of the changed resource |
| `created_at` | `?string` | Event timestamp |
| `change` | `?array` | Field-level change (`field`, `action`, `new_value`, `added_value`, `removed_value`) |

---

```

- [ ] **Step 7: Format and commit**

Run: `composer format && composer test`
Expected: tests PASS.

```bash
git add src/Requests/Events src/Data/EventData.php src/Data/Shared/EventsResponse.php src/Resources/EventResource.php src/Asana.php tests/Unit/AsanaTest.php tests/ArchTest.php tests/Unit/Resources/EventResourceTest.php tests/Unit/Data/EventDataTest.php tests/Unit/Data/Shared/EventsResponseTest.php README.md
git commit -m "feat(events): add EventResource with sync-token handling for 412 responses

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 12: Custom types

**Files:**
- Create: `src/Requests/CustomTypes/GetCustomTypesRequest.php`
- Create: `src/Requests/CustomTypes/GetCustomTypeRequest.php`
- Create: `src/Data/CustomTypeData.php`
- Create: `src/Resources/CustomTypeResource.php`
- Modify: `src/Asana.php`
- Modify: `tests/Unit/AsanaTest.php`
- Modify: `tests/ArchTest.php`
- Modify: `README.md`
- Test: `tests/Unit/Resources/CustomTypeResourceTest.php`
- Test: `tests/Unit/Data/CustomTypeDataTest.php`

- [ ] **Step 1: Write the failing tests**

`tests/Unit/Data/CustomTypeDataTest.php`:

```php
<?php

use WMBH\Asana\Data\CustomTypeData;

test('CustomTypeData can be created from array', function () {
    $data = CustomTypeData::from([
        'gid' => '1000',
        'resource_type' => 'custom_type',
        'name' => 'Bug',
        'asana_created_type_identifier' => null,
        'status_options' => [
            ['gid' => '1', 'name' => 'Open', 'enabled' => true],
        ],
    ]);

    expect($data->gid)->toBe('1000')
        ->and($data->name)->toBe('Bug')
        ->and($data->asana_created_type_identifier)->toBeNull()
        ->and($data->status_options)->toHaveCount(1)
        ->and($data->status_options[0]['name'])->toBe('Open');
});

test('CustomTypeData handles null optional fields', function () {
    $data = CustomTypeData::from(['gid' => '1000']);

    expect($data->gid)->toBe('1000')
        ->and($data->resource_type)->toBeNull()
        ->and($data->name)->toBeNull()
        ->and($data->asana_created_type_identifier)->toBeNull()
        ->and($data->status_options)->toBeNull();
});
```

`tests/Unit/Resources/CustomTypeResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\CustomTypeData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\CustomTypeResource;

function createCustomTypeResource(MockClient $mockClient): CustomTypeResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new CustomTypeResource($connector);
}

test('list returns PaginatedResponse filtered by project', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Bug', 'resource_type' => 'custom_type'],
                ['gid' => '2', 'name' => 'Feature', 'resource_type' => 'custom_type'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createCustomTypeResource($mockClient);
    $result = $resource->list(projectGid: 'proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(CustomTypeData::class)
        ->and($result->data[0]->name)->toBe('Bug')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/custom_types'
            && $request->query()->all() === ['project' => 'proj1'];
    });
});

test('list passes workspace, pagination and opt_fields', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'custom_type']],
            'next_page' => ['offset' => 'abc', 'uri' => '/custom_types?offset=abc'],
        ], 200),
    ]);

    $resource = createCustomTypeResource($mockClient);
    $result = $resource->list(workspaceGid: 'ws1', optFields: ['name', 'status_options'], offset: 'off1', limit: 25);

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('abc');

    $mockClient->assertSent(function ($request) {
        return $request->query()->all() === [
            'workspace' => 'ws1',
            'opt_fields' => 'name,status_options',
            'offset' => 'off1',
            'limit' => 25,
        ];
    });
});

test('get returns CustomTypeData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1000',
            'resource_type' => 'custom_type',
            'name' => 'Bug',
        ]], 200),
    ]);

    $resource = createCustomTypeResource($mockClient);
    $result = $resource->get('1000', ['name']);

    expect($result)->toBeInstanceOf(CustomTypeData::class)
        ->and($result->gid)->toBe('1000')
        ->and($result->name)->toBe('Bug');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/custom_types/1000'
            && $request->query()->all() === ['opt_fields' => 'name'];
    });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/CustomTypeDataTest.php tests/Unit/Resources/CustomTypeResourceTest.php`
Expected: FAIL with `Error: Class "WMBH\Asana\Data\CustomTypeData" not found` and `Error: Class "WMBH\Asana\Resources\CustomTypeResource" not found`

- [ ] **Step 3: Implement DTO, requests, resource, accessor**

`src/Data/CustomTypeData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;

class CustomTypeData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $asana_created_type_identifier = null,
        public readonly ?array $status_options = null,
    ) {}
}
```

`src/Requests/CustomTypes/GetCustomTypesRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\CustomTypes;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCustomTypesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly ?string $projectGid = null,
        protected readonly ?string $workspaceGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/custom_types';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'project' => $this->projectGid,
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

`src/Requests/CustomTypes/GetCustomTypeRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\CustomTypes;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCustomTypeRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/custom_types/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

`src/Resources/CustomTypeResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\CustomTypeData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\CustomTypes\GetCustomTypeRequest;
use WMBH\Asana\Requests\CustomTypes\GetCustomTypesRequest;

class CustomTypeResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function list(?string $projectGid = null, ?string $workspaceGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetCustomTypesRequest($projectGid, $workspaceGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), CustomTypeData::class);
    }

    public function get(string $gid, array $optFields = []): CustomTypeData
    {
        $response = $this->connector->send(new GetCustomTypeRequest($gid, $optFields));

        return CustomTypeData::from($response->json('data'));
    }
}
```

`src/Asana.php` — add the import (alphabetical):

```php
use WMBH\Asana\Resources\CustomTypeResource;
```

add the property after `private ?BatchResource $batchResource = null;`:

```php
    private ?CustomTypeResource $customTypeResource = null;
```

add the accessor after the `batch()` method:

```php
    public function customTypes(): CustomTypeResource
    {
        return $this->customTypeResource ??= new CustomTypeResource($this->connector);
    }
```

`tests/Unit/AsanaTest.php` — add the import:

```php
use WMBH\Asana\Resources\CustomTypeResource;
```

and insert immediately after `->and($asana->webhooks())->toBeInstanceOf(WebhookResource::class)`:

```php
        ->and($asana->customTypes())->toBeInstanceOf(CustomTypeResource::class)
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/CustomTypeDataTest.php tests/Unit/Resources/CustomTypeResourceTest.php tests/Unit/AsanaTest.php`
Expected: PASS

- [ ] **Step 5: Add ArchTest entries**

Append to the `// ── Requests (Lawman)` section of `tests/ArchTest.php`:

```php
arch('GetCustomTypesRequest sends GET')
    ->expect('WMBH\Asana\Requests\CustomTypes\GetCustomTypesRequest')
    ->toSendGetRequest();

arch('GetCustomTypeRequest sends GET')
    ->expect('WMBH\Asana\Requests\CustomTypes\GetCustomTypeRequest')
    ->toSendGetRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS

- [ ] **Step 6: README**

In the Table of Contents, after `- [Events](#events)` add:

```markdown
- [Custom Types](#custom-types)
```

Insert immediately before the `### Batch Requests` heading:

```markdown
### Custom Types

Access via `Asana::customTypes()` — returns `CustomTypeResource`. Custom types are read-only through the API; pass exactly one of `projectGid` or `workspaceGid` to `list`.

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `list` | `?string $projectGid = null`, `?string $workspaceGid = null`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List custom types in a project or workspace |
| `get` | `string $gid`, `array $optFields = []` | `CustomTypeData` | Get a custom type |

```php
$types = Asana::customTypes()->list(projectGid: 'project_gid', optFields: ['name', 'status_options']);
foreach ($types->data as $type) {
    echo "{$type->name}: " . count($type->status_options ?? []) . " statuses\n";
}
```

#### CustomTypeData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"custom_type"` |
| `name` | `?string` | Type name |
| `asana_created_type_identifier` | `?string` | Set for Asana-provided types (e.g. `"bug"`), `null` for user-created |
| `status_options` | `?array` | Status options (`gid`, `name`, `enabled`, `color`, `completion_state`) |

---

```

- [ ] **Step 7: Format and commit**

Run: `composer format && composer test`
Expected: tests PASS.

```bash
git add src/Requests/CustomTypes src/Data/CustomTypeData.php src/Resources/CustomTypeResource.php src/Asana.php tests/Unit/AsanaTest.php tests/ArchTest.php tests/Unit/Resources/CustomTypeResourceTest.php tests/Unit/Data/CustomTypeDataTest.php README.md
git commit -m "feat(custom-types): add CustomTypeResource with list and get

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 13: User task lists + access requests

**Files:**
- Create: `src/Requests/UserTaskLists/GetUserTaskListRequest.php`
- Create: `src/Requests/UserTaskLists/GetUserTaskListForUserRequest.php`
- Create: `src/Data/UserTaskListData.php`
- Create: `src/Resources/UserTaskListResource.php`
- Create: `src/Requests/AccessRequests/GetAccessRequestsRequest.php`
- Create: `src/Requests/AccessRequests/CreateAccessRequestRequest.php`
- Create: `src/Requests/AccessRequests/ApproveAccessRequestRequest.php`
- Create: `src/Requests/AccessRequests/RejectAccessRequestRequest.php`
- Create: `src/Data/AccessRequestData.php`
- Create: `src/Resources/AccessRequestResource.php`
- Modify: `src/Asana.php` (two accessors)
- Modify: `tests/Unit/AsanaTest.php`
- Modify: `tests/ArchTest.php`
- Modify: `README.md`
- Test: `tests/Unit/Resources/UserTaskListResourceTest.php`
- Test: `tests/Unit/Data/UserTaskListDataTest.php`
- Test: `tests/Unit/Resources/AccessRequestResourceTest.php`
- Test: `tests/Unit/Data/AccessRequestDataTest.php`

- [ ] **Step 1: Write the failing tests**

`tests/Unit/Data/UserTaskListDataTest.php`:

```php
<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\UserTaskListData;

test('UserTaskListData can be created from array', function () {
    $data = UserTaskListData::from([
        'gid' => '1100',
        'resource_type' => 'user_task_list',
        'name' => 'My Tasks',
    ]);

    expect($data->gid)->toBe('1100')
        ->and($data->resource_type)->toBe('user_task_list')
        ->and($data->name)->toBe('My Tasks');
});

test('UserTaskListData handles null optional fields', function () {
    $data = UserTaskListData::from(['gid' => '1100']);

    expect($data->gid)->toBe('1100')
        ->and($data->name)->toBeNull()
        ->and($data->owner)->toBeNull()
        ->and($data->workspace)->toBeNull();
});

test('UserTaskListData casts nested owner and workspace to CompactResource', function () {
    $data = UserTaskListData::from([
        'gid' => '1100',
        'owner' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
        'workspace' => ['gid' => 'ws1', 'name' => 'Acme', 'resource_type' => 'workspace'],
    ]);

    expect($data->owner)->toBeInstanceOf(CompactResource::class)
        ->and($data->owner->gid)->toBe('111')
        ->and($data->workspace)->toBeInstanceOf(CompactResource::class)
        ->and($data->workspace->name)->toBe('Acme');
});
```

`tests/Unit/Resources/UserTaskListResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\UserTaskListData;
use WMBH\Asana\Resources\UserTaskListResource;

function createUserTaskListResource(MockClient $mockClient): UserTaskListResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new UserTaskListResource($connector);
}

test('get returns UserTaskListData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1100',
            'resource_type' => 'user_task_list',
            'name' => 'My Tasks',
        ]], 200),
    ]);

    $resource = createUserTaskListResource($mockClient);
    $result = $resource->get('1100', ['name']);

    expect($result)->toBeInstanceOf(UserTaskListData::class)
        ->and($result->gid)->toBe('1100')
        ->and($result->name)->toBe('My Tasks');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/user_task_lists/1100'
            && $request->query()->all() === ['opt_fields' => 'name'];
    });
});

test('getForUser returns UserTaskListData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1100',
            'resource_type' => 'user_task_list',
            'name' => 'My Tasks',
            'owner' => ['gid' => 'me', 'resource_type' => 'user'],
        ]], 200),
    ]);

    $resource = createUserTaskListResource($mockClient);
    $result = $resource->getForUser('me', 'ws1');

    expect($result)->toBeInstanceOf(UserTaskListData::class)
        ->and($result->gid)->toBe('1100')
        ->and($result->owner->gid)->toBe('me');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/users/me/user_task_list'
            && $request->query()->all() === ['workspace' => 'ws1'];
    });
});
```

`tests/Unit/Data/AccessRequestDataTest.php`:

```php
<?php

use WMBH\Asana\Data\AccessRequestData;
use WMBH\Asana\Data\Shared\CompactResource;

test('AccessRequestData can be created from array', function () {
    $data = AccessRequestData::from([
        'gid' => '1200',
        'resource_type' => 'access_request',
        'message' => 'Please add me',
        'approval_status' => 'pending',
    ]);

    expect($data->gid)->toBe('1200')
        ->and($data->message)->toBe('Please add me')
        ->and($data->approval_status)->toBe('pending');
});

test('AccessRequestData handles null optional fields', function () {
    $data = AccessRequestData::from(['gid' => '1200']);

    expect($data->gid)->toBe('1200')
        ->and($data->resource_type)->toBeNull()
        ->and($data->message)->toBeNull()
        ->and($data->approval_status)->toBeNull()
        ->and($data->requester)->toBeNull()
        ->and($data->target)->toBeNull();
});

test('AccessRequestData casts nested requester and target to CompactResource', function () {
    $data = AccessRequestData::from([
        'gid' => '1200',
        'requester' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
        'target' => ['gid' => '900', 'resource_type' => 'project'],
    ]);

    expect($data->requester)->toBeInstanceOf(CompactResource::class)
        ->and($data->requester->name)->toBe('Jane')
        ->and($data->target)->toBeInstanceOf(CompactResource::class)
        ->and($data->target->gid)->toBe('900')
        ->and($data->target->name)->toBeNull();
});
```

`tests/Unit/Resources/AccessRequestResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\AccessRequestData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\AccessRequestResource;

function createAccessRequestResource(MockClient $mockClient): AccessRequestResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new AccessRequestResource($connector);
}

test('list returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'access_request', 'approval_status' => 'pending'],
                ['gid' => '2', 'resource_type' => 'access_request', 'approval_status' => 'pending'],
            ],
        ], 200),
    ]);

    $resource = createAccessRequestResource($mockClient);
    $result = $resource->list('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(AccessRequestData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/access_requests'
            && $request->query()->all() === ['target' => 'proj1'];
    });
});

test('list passes user and opt_fields', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createAccessRequestResource($mockClient);
    $resource->list('proj1', 'user1', ['message']);

    $mockClient->assertSent(function ($request) {
        return $request->query()->all() === [
            'target' => 'proj1',
            'user' => 'user1',
            'opt_fields' => 'message',
        ];
    });
});

test('create returns AccessRequestData and sends target and message', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1201',
            'resource_type' => 'access_request',
            'message' => 'Please add me',
            'approval_status' => 'pending',
        ]], 201),
    ]);

    $resource = createAccessRequestResource($mockClient);
    $result = $resource->create('proj1', 'Please add me');

    expect($result)->toBeInstanceOf(AccessRequestData::class)
        ->and($result->gid)->toBe('1201')
        ->and($result->message)->toBe('Please add me');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/access_requests'
            && $request->body()->all() === ['data' => ['target' => 'proj1', 'message' => 'Please add me']];
    });
});

test('create omits message when null', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => '1202', 'resource_type' => 'access_request']], 201),
    ]);

    $resource = createAccessRequestResource($mockClient);
    $resource->create('proj1');

    $mockClient->assertSent(function ($request) {
        return $request->body()->all() === ['data' => ['target' => 'proj1']];
    });
});

test('approve returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createAccessRequestResource($mockClient);

    expect($resource->approve('1200'))->toBeTrue();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/access_requests/1200/approve';
    });
});

test('reject returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createAccessRequestResource($mockClient);

    expect($resource->reject('1200'))->toBeTrue();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/access_requests/1200/reject';
    });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/UserTaskListDataTest.php tests/Unit/Resources/UserTaskListResourceTest.php tests/Unit/Data/AccessRequestDataTest.php tests/Unit/Resources/AccessRequestResourceTest.php`
Expected: FAIL with `Error: Class "WMBH\Asana\Data\UserTaskListData" not found`, `Error: Class "WMBH\Asana\Resources\UserTaskListResource" not found`, `Error: Class "WMBH\Asana\Data\AccessRequestData" not found`, `Error: Class "WMBH\Asana\Resources\AccessRequestResource" not found`

- [ ] **Step 3: Implement DTOs, requests, resources, accessors**

`src/Data/UserTaskListData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class UserTaskListData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?CompactResource $owner = null,
        public readonly ?CompactResource $workspace = null,
    ) {}
}
```

`src/Requests/UserTaskLists/GetUserTaskListRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\UserTaskLists;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUserTaskListRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/user_task_lists/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

`src/Requests/UserTaskLists/GetUserTaskListForUserRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\UserTaskLists;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUserTaskListForUserRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $userGid,
        protected readonly string $workspaceGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->userGid}/user_task_list";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

`src/Resources/UserTaskListResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\UserTaskListData;
use WMBH\Asana\Requests\UserTaskLists\GetUserTaskListForUserRequest;
use WMBH\Asana\Requests\UserTaskLists\GetUserTaskListRequest;

class UserTaskListResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): UserTaskListData
    {
        $response = $this->connector->send(new GetUserTaskListRequest($gid, $optFields));

        return UserTaskListData::from($response->json('data'));
    }

    public function getForUser(string $userGid, string $workspaceGid, array $optFields = []): UserTaskListData
    {
        $response = $this->connector->send(new GetUserTaskListForUserRequest($userGid, $workspaceGid, $optFields));

        return UserTaskListData::from($response->json('data'));
    }
}
```

`src/Data/AccessRequestData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class AccessRequestData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $message = null,
        public readonly ?string $approval_status = null,
        public readonly ?CompactResource $requester = null,
        public readonly ?CompactResource $target = null,
    ) {}
}
```

`src/Requests/AccessRequests/GetAccessRequestsRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\AccessRequests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAccessRequestsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $targetGid,
        protected readonly ?string $userGid = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/access_requests';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'target' => $this->targetGid,
            'user' => $this->userGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

`src/Requests/AccessRequests/CreateAccessRequestRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\AccessRequests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateAccessRequestRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $targetGid,
        protected readonly ?string $message = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/access_requests';
    }

    protected function defaultBody(): array
    {
        return ['data' => array_filter([
            'target' => $this->targetGid,
            'message' => $this->message,
        ])];
    }
}
```

`src/Requests/AccessRequests/ApproveAccessRequestRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\AccessRequests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ApproveAccessRequestRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/access_requests/{$this->gid}/approve";
    }
}
```

`src/Requests/AccessRequests/RejectAccessRequestRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\AccessRequests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class RejectAccessRequestRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/access_requests/{$this->gid}/reject";
    }
}
```

`src/Resources/AccessRequestResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\AccessRequestData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\AccessRequests\ApproveAccessRequestRequest;
use WMBH\Asana\Requests\AccessRequests\CreateAccessRequestRequest;
use WMBH\Asana\Requests\AccessRequests\GetAccessRequestsRequest;
use WMBH\Asana\Requests\AccessRequests\RejectAccessRequestRequest;

class AccessRequestResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function list(string $targetGid, ?string $userGid = null, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetAccessRequestsRequest($targetGid, $userGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), AccessRequestData::class);
    }

    public function create(string $targetGid, ?string $message = null): AccessRequestData
    {
        $response = $this->connector->send(new CreateAccessRequestRequest($targetGid, $message));

        return AccessRequestData::from($response->json('data'));
    }

    public function approve(string $gid): bool
    {
        $response = $this->connector->send(new ApproveAccessRequestRequest($gid));

        return $response->status() === 200;
    }

    public function reject(string $gid): bool
    {
        $response = $this->connector->send(new RejectAccessRequestRequest($gid));

        return $response->status() === 200;
    }
}
```

`src/Asana.php` — add both imports (alphabetical):

```php
use WMBH\Asana\Resources\AccessRequestResource;
use WMBH\Asana\Resources\UserTaskListResource;
```

add both properties after `private ?BatchResource $batchResource = null;`:

```php
    private ?UserTaskListResource $userTaskListResource = null;

    private ?AccessRequestResource $accessRequestResource = null;
```

add both accessors after the `batch()` method:

```php
    public function userTaskLists(): UserTaskListResource
    {
        return $this->userTaskListResource ??= new UserTaskListResource($this->connector);
    }

    public function accessRequests(): AccessRequestResource
    {
        return $this->accessRequestResource ??= new AccessRequestResource($this->connector);
    }
```

`tests/Unit/AsanaTest.php` — add both imports:

```php
use WMBH\Asana\Resources\AccessRequestResource;
use WMBH\Asana\Resources\UserTaskListResource;
```

and insert immediately after `->and($asana->webhooks())->toBeInstanceOf(WebhookResource::class)`:

```php
        ->and($asana->userTaskLists())->toBeInstanceOf(UserTaskListResource::class)
        ->and($asana->accessRequests())->toBeInstanceOf(AccessRequestResource::class)
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/UserTaskListDataTest.php tests/Unit/Resources/UserTaskListResourceTest.php tests/Unit/Data/AccessRequestDataTest.php tests/Unit/Resources/AccessRequestResourceTest.php tests/Unit/AsanaTest.php`
Expected: PASS

- [ ] **Step 5: Add ArchTest entries**

Append to the `// ── Requests (Lawman)` section of `tests/ArchTest.php`:

```php
arch('GetUserTaskListRequest sends GET')
    ->expect('WMBH\Asana\Requests\UserTaskLists\GetUserTaskListRequest')
    ->toSendGetRequest();

arch('GetUserTaskListForUserRequest sends GET')
    ->expect('WMBH\Asana\Requests\UserTaskLists\GetUserTaskListForUserRequest')
    ->toSendGetRequest();

arch('GetAccessRequestsRequest sends GET')
    ->expect('WMBH\Asana\Requests\AccessRequests\GetAccessRequestsRequest')
    ->toSendGetRequest();

arch('CreateAccessRequestRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\AccessRequests\CreateAccessRequestRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('ApproveAccessRequestRequest sends POST')
    ->expect('WMBH\Asana\Requests\AccessRequests\ApproveAccessRequestRequest')
    ->toSendPostRequest();

arch('RejectAccessRequestRequest sends POST')
    ->expect('WMBH\Asana\Requests\AccessRequests\RejectAccessRequestRequest')
    ->toSendPostRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS

- [ ] **Step 6: README**

In the Table of Contents, after `- [Custom Types](#custom-types)` add:

```markdown
- [User Task Lists](#user-task-lists)
- [Access Requests](#access-requests)
```

Insert immediately before the `### Batch Requests` heading:

```markdown
### User Task Lists

Access via `Asana::userTaskLists()` — returns `UserTaskListResource`. A user task list is a user's "My Tasks" in a workspace; list its tasks with `Asana::tasks()->getForUserTaskList()`.

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `get` | `string $gid`, `array $optFields = []` | `UserTaskListData` | Get a user task list |
| `getForUser` | `string $userGid`, `string $workspaceGid`, `array $optFields = []` | `UserTaskListData` | Get a user's task list in a workspace (`'me'` works) |

```php
$myTasks = Asana::userTaskLists()->getForUser('me', 'workspace_gid');
$tasks = Asana::tasks()->getForUserTaskList($myTasks->gid, completedSince: 'now');
```

#### UserTaskListData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"user_task_list"` |
| `name` | `?string` | List name |
| `owner` | `?CompactResource` | Owning user |
| `workspace` | `?CompactResource` | Workspace |

---

### Access Requests

Access via `Asana::accessRequests()` — returns `AccessRequestResource`. Requests to join private projects and portfolios.

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `list` | `string $targetGid`, `?string $userGid = null`, `array $optFields = []` | `PaginatedResponse` | Pending requests on a project/portfolio |
| `create` | `string $targetGid`, `?string $message = null` | `AccessRequestData` | Request access to a private object |
| `approve` | `string $gid` | `bool` | Approve a request |
| `reject` | `string $gid` | `bool` | Reject a request |

```php
$pending = Asana::accessRequests()->list('project_gid');
foreach ($pending->data as $request) {
    Asana::accessRequests()->approve($request->gid);
}
```

#### AccessRequestData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"access_request"` |
| `message` | `?string` | Message from the requester |
| `approval_status` | `?string` | `"pending"`, `"approved"` or `"rejected"` |
| `requester` | `?CompactResource` | Requesting user |
| `target` | `?CompactResource` | Project or portfolio requested |

---

```

- [ ] **Step 7: Format and commit**

Run: `composer format && composer test`
Expected: tests PASS.

```bash
git add src/Requests/UserTaskLists src/Requests/AccessRequests src/Data/UserTaskListData.php src/Data/AccessRequestData.php src/Resources/UserTaskListResource.php src/Resources/AccessRequestResource.php src/Asana.php tests/Unit/AsanaTest.php tests/ArchTest.php tests/Unit/Resources/UserTaskListResourceTest.php tests/Unit/Resources/AccessRequestResourceTest.php tests/Unit/Data/UserTaskListDataTest.php tests/Unit/Data/AccessRequestDataTest.php README.md
git commit -m "feat(user-task-lists,access-requests): add UserTaskListResource and AccessRequestResource

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 14: Reactions

**Files:**
- Create: `src/Requests/Reactions/GetReactionsForObjectRequest.php`
- Create: `src/Data/ReactionData.php`
- Create: `src/Resources/ReactionResource.php`
- Modify: `src/Asana.php`
- Modify: `tests/Unit/AsanaTest.php`
- Modify: `tests/ArchTest.php`
- Modify: `README.md`
- Test: `tests/Unit/Resources/ReactionResourceTest.php`
- Test: `tests/Unit/Data/ReactionDataTest.php`

- [ ] **Step 1: Write the failing tests**

`tests/Unit/Data/ReactionDataTest.php`:

```php
<?php

use WMBH\Asana\Data\ReactionData;
use WMBH\Asana\Data\Shared\CompactResource;

test('ReactionData can be created from array', function () {
    $data = ReactionData::from([
        'gid' => '1300',
        'emoji' => '👍',
    ]);

    expect($data->gid)->toBe('1300')
        ->and($data->emoji)->toBe('👍');
});

test('ReactionData handles null optional fields', function () {
    $data = ReactionData::from(['gid' => '1300']);

    expect($data->gid)->toBe('1300')
        ->and($data->emoji)->toBeNull()
        ->and($data->user)->toBeNull();
});

test('ReactionData casts nested user to CompactResource', function () {
    $data = ReactionData::from([
        'gid' => '1300',
        'user' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
    ]);

    expect($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->user->gid)->toBe('111')
        ->and($data->user->name)->toBe('Jane');
});
```

`tests/Unit/Resources/ReactionResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\ReactionData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\ReactionResource;

function createReactionResource(MockClient $mockClient): ReactionResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new ReactionResource($connector);
}

test('getForObject returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'emoji' => '👍', 'user' => ['gid' => '111', 'resource_type' => 'user']],
                ['gid' => '2', 'emoji' => '👍🏽', 'user' => ['gid' => '222', 'resource_type' => 'user']],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createReactionResource($mockClient);
    $result = $resource->getForObject('task1', '👍');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(ReactionData::class)
        ->and($result->data[0]->user->gid)->toBe('111')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/reactions'
            && $request->query()->all() === ['target' => 'task1', 'emoji_base' => '👍'];
    });
});

test('getForObject passes pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'emoji' => '❤️']],
            'next_page' => ['offset' => 'abc', 'uri' => '/reactions?offset=abc'],
        ], 200),
    ]);

    $resource = createReactionResource($mockClient);
    $result = $resource->getForObject('task1', '❤️', 'off1', 50);

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('abc');

    $mockClient->assertSent(function ($request) {
        return $request->query()->all() === [
            'target' => 'task1',
            'emoji_base' => '❤️',
            'offset' => 'off1',
            'limit' => 50,
        ];
    });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/ReactionDataTest.php tests/Unit/Resources/ReactionResourceTest.php`
Expected: FAIL with `Error: Class "WMBH\Asana\Data\ReactionData" not found` and `Error: Class "WMBH\Asana\Resources\ReactionResource" not found`

- [ ] **Step 3: Implement DTO, request, resource, accessor**

`src/Data/ReactionData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class ReactionData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $emoji = null,
        public readonly ?CompactResource $user = null,
    ) {}
}
```

`src/Requests/Reactions/GetReactionsForObjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Reactions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetReactionsForObjectRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $targetGid,
        protected readonly string $emojiBase,
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/reactions';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'target' => $this->targetGid,
            'emoji_base' => $this->emojiBase,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

`src/Resources/ReactionResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\ReactionData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\Reactions\GetReactionsForObjectRequest;

class ReactionResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function getForObject(string $targetGid, string $emojiBase, ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetReactionsForObjectRequest($targetGid, $emojiBase, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), ReactionData::class);
    }
}
```

`src/Asana.php` — add the import (alphabetical):

```php
use WMBH\Asana\Resources\ReactionResource;
```

add the property after `private ?BatchResource $batchResource = null;`:

```php
    private ?ReactionResource $reactionResource = null;
```

add the accessor after the `batch()` method:

```php
    public function reactions(): ReactionResource
    {
        return $this->reactionResource ??= new ReactionResource($this->connector);
    }
```

`tests/Unit/AsanaTest.php` — add the import:

```php
use WMBH\Asana\Resources\ReactionResource;
```

and insert immediately after `->and($asana->webhooks())->toBeInstanceOf(WebhookResource::class)`:

```php
        ->and($asana->reactions())->toBeInstanceOf(ReactionResource::class)
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/ReactionDataTest.php tests/Unit/Resources/ReactionResourceTest.php tests/Unit/AsanaTest.php`
Expected: PASS

- [ ] **Step 5: Add ArchTest entry**

Append to the `// ── Requests (Lawman)` section of `tests/ArchTest.php`:

```php
arch('GetReactionsForObjectRequest sends GET')
    ->expect('WMBH\Asana\Requests\Reactions\GetReactionsForObjectRequest')
    ->toSendGetRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS

- [ ] **Step 6: README**

In the Table of Contents, after `- [Access Requests](#access-requests)` add:

```markdown
- [Reactions](#reactions)
```

Insert immediately before the `### Batch Requests` heading:

```markdown
### Reactions

Access via `Asana::reactions()` — returns `ReactionResource`. Lists who reacted to a task, story or status update with a given emoji. `$emojiBase` is the emoji without skin-tone modifiers; results include every variant.

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `getForObject` | `string $targetGid`, `string $emojiBase`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | Reactions with `$emojiBase` on the target |

```php
$thumbs = Asana::reactions()->getForObject('task_gid', '👍');
foreach ($thumbs->data as $reaction) {
    echo "{$reaction->user->gid} reacted {$reaction->emoji}\n";
}
```

#### ReactionData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `emoji` | `?string` | The exact emoji used (may include a skin-tone variant) |
| `user` | `?CompactResource` | User who reacted |

---

```

- [ ] **Step 7: Format and commit**

Run: `composer format && composer test`
Expected: tests PASS.

```bash
git add src/Requests/Reactions src/Data/ReactionData.php src/Resources/ReactionResource.php src/Asana.php tests/Unit/AsanaTest.php tests/ArchTest.php tests/Unit/Resources/ReactionResourceTest.php tests/Unit/Data/ReactionDataTest.php README.md
git commit -m "feat(reactions): add ReactionResource with getForObject

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

---

### Task 15: Memberships

Generic memberships endpoint: one membership object for project / portfolio / goal / custom_field / custom_type parents.

**Files:**
- Create: `src/Requests/Memberships/GetMembershipsRequest.php`
- Create: `src/Requests/Memberships/GetMembershipRequest.php`
- Create: `src/Requests/Memberships/CreateMembershipRequest.php`
- Create: `src/Requests/Memberships/UpdateMembershipRequest.php`
- Create: `src/Requests/Memberships/DeleteMembershipRequest.php`
- Create: `src/Data/MembershipData.php`
- Create: `src/Resources/MembershipResource.php`
- Modify: `src/Asana.php` (property + accessor)
- Modify: `tests/Unit/AsanaTest.php` (import + one assertion)
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (Table of Contents + new "Memberships" section)
- Test: `tests/Unit/Resources/MembershipResourceTest.php`
- Test: `tests/Unit/Data/MembershipDataTest.php`

- [ ] **Step 1: Write the failing DTO test**

Create `tests/Unit/Data/MembershipDataTest.php`:

```php
<?php

use WMBH\Asana\Data\MembershipData;
use WMBH\Asana\Data\Shared\CompactResource;

test('MembershipData can be created from array', function () {
    $data = MembershipData::from([
        'gid' => '900',
        'resource_type' => 'project_membership',
        'resource_subtype' => 'project_membership',
        'access_level' => 'editor',
    ]);

    expect($data->gid)->toBe('900')
        ->and($data->resource_type)->toBe('project_membership')
        ->and($data->resource_subtype)->toBe('project_membership')
        ->and($data->access_level)->toBe('editor');
});

test('MembershipData handles null optional fields', function () {
    $data = MembershipData::from(['gid' => '900']);

    expect($data->gid)->toBe('900')
        ->and($data->resource_type)->toBeNull()
        ->and($data->resource_subtype)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->member)->toBeNull()
        ->and($data->access_level)->toBeNull()
        ->and($data->role)->toBeNull()
        ->and($data->user)->toBeNull()
        ->and($data->goal)->toBeNull()
        ->and($data->workspace)->toBeNull()
        ->and($data->project)->toBeNull()
        ->and($data->write_access)->toBeNull();
});

test('MembershipData casts nested references to CompactResource', function () {
    $data = MembershipData::from([
        'gid' => '900',
        'parent' => ['gid' => '1', 'name' => 'Project A', 'resource_type' => 'project'],
        'member' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'goal' => ['gid' => '3', 'name' => 'Goal', 'resource_type' => 'goal'],
        'workspace' => ['gid' => '4', 'name' => 'WS', 'resource_type' => 'workspace'],
        'project' => ['gid' => '1', 'name' => 'Project A', 'resource_type' => 'project'],
    ]);

    expect($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->parent->name)->toBe('Project A')
        ->and($data->member)->toBeInstanceOf(CompactResource::class)
        ->and($data->member->gid)->toBe('2')
        ->and($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->goal)->toBeInstanceOf(CompactResource::class)
        ->and($data->workspace)->toBeInstanceOf(CompactResource::class)
        ->and($data->project)->toBeInstanceOf(CompactResource::class);
});
```

- [ ] **Step 2: Write the failing Resource test**

Create `tests/Unit/Resources/MembershipResourceTest.php`:

```php
<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\MembershipData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\MembershipResource;

function createMembershipResource(MockClient $mockClient): MembershipResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new MembershipResource($connector);
}

test('list returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'project_membership', 'access_level' => 'editor'],
                ['gid' => '2', 'resource_type' => 'project_membership', 'access_level' => 'viewer'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $result = $resource->list('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(MembershipData::class)
        ->and($result->data[0]->access_level)->toBe('editor')
        ->and($result->hasNextPage())->toBeFalse();
});

test('list sends filters as query params', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $resource->list('proj1', 'user1', 'project_membership', ['access_level'], 'abc', 50);

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/memberships'
        && $request->query()->all() === [
            'parent' => 'proj1',
            'member' => 'user1',
            'resource_subtype' => 'project_membership',
            'opt_fields' => 'access_level',
            'offset' => 'abc',
            'limit' => 50,
        ]);
});

test('list omits null filters', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $resource->list();

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === []);
});

test('list handles pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'project_membership']],
            'next_page' => ['offset' => 'tok', 'uri' => '/memberships?offset=tok'],
        ], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $result = $resource->list('proj1');

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');
});

test('get returns MembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '900',
            'resource_type' => 'project_membership',
            'access_level' => 'admin',
            'member' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        ]], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $result = $resource->get('900');

    expect($result)->toBeInstanceOf(MembershipData::class)
        ->and($result->gid)->toBe('900')
        ->and($result->access_level)->toBe('admin')
        ->and($result->member->name)->toBe('Jane');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/memberships/900');
});

test('create returns MembershipData and wraps body in data envelope', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '901',
            'resource_type' => 'project_membership',
            'access_level' => 'editor',
        ]], 201),
    ]);

    $resource = createMembershipResource($mockClient);
    $result = $resource->create(['parent' => 'proj1', 'member' => 'user1', 'access_level' => 'editor']);

    expect($result)->toBeInstanceOf(MembershipData::class)
        ->and($result->gid)->toBe('901');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/memberships'
        && $request->body()->all() === ['data' => ['parent' => 'proj1', 'member' => 'user1', 'access_level' => 'editor']]);
});

test('update returns MembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '900',
            'resource_type' => 'project_membership',
            'access_level' => 'viewer',
        ]], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $result = $resource->update('900', ['access_level' => 'viewer']);

    expect($result)->toBeInstanceOf(MembershipData::class)
        ->and($result->access_level)->toBe('viewer');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/memberships/900'
        && $request->body()->all() === ['data' => ['access_level' => 'viewer']]);
});

test('delete returns true on 200', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createMembershipResource($mockClient);

    expect($resource->delete('900'))->toBeTrue();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/memberships/900');
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/MembershipDataTest.php tests/Unit/Resources/MembershipResourceTest.php`
Expected: FAIL with `Error: Class "WMBH\Asana\Data\MembershipData" not found` and `Class "WMBH\Asana\Resources\MembershipResource" not found`.

- [ ] **Step 4: Create the DTO**

Create `src/Data/MembershipData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class MembershipData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?CompactResource $parent = null,
        public readonly ?CompactResource $member = null,
        public readonly ?string $access_level = null,
        public readonly ?string $role = null,
        public readonly ?CompactResource $user = null,
        public readonly ?CompactResource $goal = null,
        public readonly ?CompactResource $workspace = null,
        public readonly ?CompactResource $project = null,
        public readonly ?string $write_access = null,
    ) {}
}
```

- [ ] **Step 5: Create the five Request classes**

Create `src/Requests/Memberships/GetMembershipsRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Memberships;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetMembershipsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly ?string $parentGid = null,
        protected readonly ?string $memberGid = null,
        protected readonly ?string $resourceSubtype = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/memberships';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'parent' => $this->parentGid,
            'member' => $this->memberGid,
            'resource_subtype' => $this->resourceSubtype,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

Create `src/Requests/Memberships/GetMembershipRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Memberships;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetMembershipRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/memberships/{$this->gid}";
    }
}
```

Create `src/Requests/Memberships/CreateMembershipRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Memberships;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateMembershipRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/memberships';
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/Memberships/UpdateMembershipRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Memberships;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateMembershipRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/memberships/{$this->gid}";
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/Memberships/DeleteMembershipRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Memberships;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteMembershipRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/memberships/{$this->gid}";
    }
}
```

- [ ] **Step 6: Create the Resource**

Create `src/Resources/MembershipResource.php`:

```php
<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\MembershipData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\Memberships\CreateMembershipRequest;
use WMBH\Asana\Requests\Memberships\DeleteMembershipRequest;
use WMBH\Asana\Requests\Memberships\GetMembershipRequest;
use WMBH\Asana\Requests\Memberships\GetMembershipsRequest;
use WMBH\Asana\Requests\Memberships\UpdateMembershipRequest;

class MembershipResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function list(?string $parentGid = null, ?string $memberGid = null, ?string $resourceSubtype = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetMembershipsRequest($parentGid, $memberGid, $resourceSubtype, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), MembershipData::class);
    }

    public function get(string $gid): MembershipData
    {
        $response = $this->connector->send(new GetMembershipRequest($gid));

        return MembershipData::from($response->json('data'));
    }

    public function create(array $data): MembershipData
    {
        $response = $this->connector->send(new CreateMembershipRequest($data));

        return MembershipData::from($response->json('data'));
    }

    public function update(string $gid, array $data): MembershipData
    {
        $response = $this->connector->send(new UpdateMembershipRequest($gid, $data));

        return MembershipData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteMembershipRequest($gid));

        return $response->status() === 200;
    }
}
```

- [ ] **Step 7: Wire the accessor into `Asana`**

In `src/Asana.php`, add the import (alphabetical, after `GoalResource`):

```php
use WMBH\Asana\Resources\MembershipResource;
```

Add the property after `private ?GoalResource $goalResource = null;`:

```php
    private ?MembershipResource $membershipResource = null;
```

Add the accessor after `goals()`:

```php
    public function memberships(): MembershipResource
    {
        return $this->membershipResource ??= new MembershipResource($this->connector);
    }
```

- [ ] **Step 8: Extend `AsanaTest`**

In `tests/Unit/AsanaTest.php`, add the import (alphabetical, after `GoalResource`):

```php
use WMBH\Asana\Resources\MembershipResource;
```

In the test `'Asana class returns resource instances'`, add after the `goals()` assertion line:

```php
        ->and($asana->memberships())->toBeInstanceOf(MembershipResource::class)
```

- [ ] **Step 9: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/MembershipDataTest.php tests/Unit/Resources/MembershipResourceTest.php tests/Unit/AsanaTest.php`
Expected: PASS (3 + 8 + 5 tests).

- [ ] **Step 10: Add ArchTest entries**

In `tests/ArchTest.php`, under `// ── Requests (Lawman) ──`, append after the `DeleteWebhookRequest` block:

```php
arch('GetMembershipsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Memberships\GetMembershipsRequest')
    ->toSendGetRequest();

arch('CreateMembershipRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Memberships\CreateMembershipRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('UpdateMembershipRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Memberships\UpdateMembershipRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('DeleteMembershipRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\Memberships\DeleteMembershipRequest')
    ->toSendDeleteRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS.

- [ ] **Step 11: README**

In `README.md` Table of Contents, add after `- [Webhooks](#webhooks)`:

```markdown
- [Memberships](#memberships)
```

Add a new section immediately before `### Batch Requests`:

```markdown
### Memberships

Access via `Asana::memberships()` — returns `MembershipResource`. One endpoint for memberships on projects, portfolios, goals, custom fields and custom types.

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `list` | `?string $parentGid = null`, `?string $memberGid = null`, `?string $resourceSubtype = null`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List memberships filtered by parent, member and/or subtype |
| `get` | `string $gid` | `MembershipData` | Get a membership |
| `create` | `array $data` | `MembershipData` | Create a membership (`parent`, `member`, `access_level`, `role`) |
| `update` | `string $gid`, `array $data` | `MembershipData` | Update a membership (`access_level`) |
| `delete` | `string $gid` | `bool` | Delete a membership |

```php
// List memberships on a project
$memberships = Asana::memberships()->list('project_gid');

// Add a user to a portfolio as editor
$membership = Asana::memberships()->create([
    'parent' => 'portfolio_gid',
    'member' => 'user_gid',
    'access_level' => 'editor',
]);

// Change access level
Asana::memberships()->update($membership->gid, ['access_level' => 'viewer']);

// Remove
Asana::memberships()->delete($membership->gid);
```

#### MembershipData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | e.g. `"project_membership"`, `"goal_membership"` |
| `resource_subtype` | `?string` | Membership subtype |
| `parent` | `?CompactResource` | The project / portfolio / goal / custom field / custom type |
| `member` | `?CompactResource` | The user or team |
| `access_level` | `?string` | `"admin"`, `"editor"`, `"commenter"`, or `"viewer"` |
| `role` | `?string` | Goal memberships only: `"editor"` or `"commenter"` |
| `user` | `?CompactResource` | The user (project / goal memberships) |
| `goal` | `?CompactResource` | The goal (goal memberships) |
| `workspace` | `?CompactResource` | The workspace (goal memberships) |
| `project` | `?CompactResource` | The project (project memberships) |
| `write_access` | `?string` | Project memberships only |

---
```

- [ ] **Step 12: Format and commit**

Run: `composer format`
Run: `composer test` — Expected: all green.

```bash
git add src/Requests/Memberships src/Data/MembershipData.php src/Resources/MembershipResource.php src/Asana.php tests/Unit/AsanaTest.php tests/ArchTest.php tests/Unit/Resources/MembershipResourceTest.php tests/Unit/Data/MembershipDataTest.php README.md
git commit -m "feat(memberships): add MembershipResource with list/get/create/update/delete

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

### Task 16: Project memberships

**Files:**
- Create: `src/Requests/Projects/GetProjectMembershipsRequest.php`
- Create: `src/Requests/Projects/GetProjectMembershipRequest.php`
- Create: `src/Data/ProjectMembershipData.php`
- Modify: `src/Resources/ProjectResource.php` (2 imports for requests, 1 for DTO, 2 methods)
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (Projects table rows + example + ProjectMembershipData table)
- Test: `tests/Unit/Resources/ProjectResourceTest.php` (append; helper `createProjectResource` already exists)
- Test: `tests/Unit/Data/ProjectMembershipDataTest.php`

- [ ] **Step 1: Write the failing DTO test**

Create `tests/Unit/Data/ProjectMembershipDataTest.php`:

```php
<?php

use WMBH\Asana\Data\ProjectMembershipData;
use WMBH\Asana\Data\Shared\CompactResource;

test('ProjectMembershipData can be created from array', function () {
    $data = ProjectMembershipData::from([
        'gid' => '700',
        'resource_type' => 'project_membership',
        'resource_subtype' => 'project_membership',
        'access_level' => 'editor',
        'write_access' => 'full_write',
    ]);

    expect($data->gid)->toBe('700')
        ->and($data->resource_type)->toBe('project_membership')
        ->and($data->access_level)->toBe('editor')
        ->and($data->write_access)->toBe('full_write');
});

test('ProjectMembershipData handles null optional fields', function () {
    $data = ProjectMembershipData::from(['gid' => '700']);

    expect($data->gid)->toBe('700')
        ->and($data->resource_type)->toBeNull()
        ->and($data->resource_subtype)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->member)->toBeNull()
        ->and($data->access_level)->toBeNull()
        ->and($data->user)->toBeNull()
        ->and($data->project)->toBeNull()
        ->and($data->write_access)->toBeNull();
});

test('ProjectMembershipData casts nested references to CompactResource', function () {
    $data = ProjectMembershipData::from([
        'gid' => '700',
        'parent' => ['gid' => '1', 'name' => 'Project A', 'resource_type' => 'project'],
        'member' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'project' => ['gid' => '1', 'name' => 'Project A', 'resource_type' => 'project'],
    ]);

    expect($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->member)->toBeInstanceOf(CompactResource::class)
        ->and($data->member->name)->toBe('Jane')
        ->and($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->project)->toBeInstanceOf(CompactResource::class)
        ->and($data->project->gid)->toBe('1');
});
```

- [ ] **Step 2: Write the failing Resource tests**

Append to `tests/Unit/Resources/ProjectResourceTest.php`. Add these imports at the top of the file (keep alphabetical order with the existing ones):

```php
use Saloon\Http\Request;
use WMBH\Asana\Data\ProjectMembershipData;
```

Append these tests at the end of the file:

```php
test('getMemberships returns PaginatedResponse of ProjectMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'project_membership', 'access_level' => 'editor'],
                ['gid' => '2', 'resource_type' => 'project_membership', 'access_level' => 'viewer'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getMemberships('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(ProjectMembershipData::class)
        ->and($result->data[0]->access_level)->toBe('editor')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/projects/proj1/project_memberships');
});

test('getMemberships sends user filter and pagination as query params', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $resource->getMemberships('proj1', 'user1', ['access_level'], 'abc', 25);

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === [
        'user' => 'user1',
        'opt_fields' => 'access_level',
        'offset' => 'abc',
        'limit' => 25,
    ]);
});

test('getMemberships handles pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'project_membership']],
            'next_page' => ['offset' => 'tok', 'uri' => '/projects/proj1/project_memberships?offset=tok'],
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getMemberships('proj1');

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');
});

test('getMembership returns ProjectMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '700',
            'resource_type' => 'project_membership',
            'access_level' => 'admin',
            'write_access' => 'full_write',
            'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getMembership('700', ['write_access']);

    expect($result)->toBeInstanceOf(ProjectMembershipData::class)
        ->and($result->gid)->toBe('700')
        ->and($result->write_access)->toBe('full_write')
        ->and($result->user->name)->toBe('Jane');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/project_memberships/700'
        && $request->query()->all() === ['opt_fields' => 'write_access']);
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/ProjectMembershipDataTest.php tests/Unit/Resources/ProjectResourceTest.php`
Expected: FAIL with `Class "WMBH\Asana\Data\ProjectMembershipData" not found` and `Call to undefined method WMBH\Asana\Resources\ProjectResource::getMemberships()`.

- [ ] **Step 4: Create the DTO**

Create `src/Data/ProjectMembershipData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class ProjectMembershipData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?CompactResource $parent = null,
        public readonly ?CompactResource $member = null,
        public readonly ?string $access_level = null,
        public readonly ?CompactResource $user = null,
        public readonly ?CompactResource $project = null,
        public readonly ?string $write_access = null,
    ) {}
}
```

- [ ] **Step 5: Create the Request classes**

Create `src/Requests/Projects/GetProjectMembershipsRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectMembershipsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $projectGid,
        protected readonly ?string $userGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->projectGid}/project_memberships";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'user' => $this->userGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

Create `src/Requests/Projects/GetProjectMembershipRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectMembershipRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/project_memberships/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

- [ ] **Step 6: Add the Resource methods**

In `src/Resources/ProjectResource.php`, add imports (alphabetical among existing ones):

```php
use WMBH\Asana\Data\ProjectMembershipData;
use WMBH\Asana\Requests\Projects\GetProjectMembershipRequest;
use WMBH\Asana\Requests\Projects\GetProjectMembershipsRequest;
```

Append these methods inside the class, after `getTaskCounts()`:

```php
    public function getMemberships(string $gid, ?string $userGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetProjectMembershipsRequest($gid, $userGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), ProjectMembershipData::class);
    }

    public function getMembership(string $membershipGid, array $optFields = []): ProjectMembershipData
    {
        $response = $this->connector->send(new GetProjectMembershipRequest($membershipGid, $optFields));

        return ProjectMembershipData::from($response->json('data'));
    }
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/ProjectMembershipDataTest.php tests/Unit/Resources/ProjectResourceTest.php`
Expected: PASS.

- [ ] **Step 8: Add ArchTest entries**

In `tests/ArchTest.php`, append under the Requests section:

```php
arch('GetProjectMembershipsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Projects\GetProjectMembershipsRequest')
    ->toSendGetRequest();

arch('GetProjectMembershipRequest sends GET')
    ->expect('WMBH\Asana\Requests\Projects\GetProjectMembershipRequest')
    ->toSendGetRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php` — Expected: PASS.

- [ ] **Step 9: README**

In the `### Projects` method table, append these rows after the `getTaskCounts` row:

```markdown
| `getMemberships` | `string $gid`, `?string $userGid = null`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List project memberships (items are `ProjectMembershipData`) |
| `getMembership` | `string $membershipGid`, `array $optFields = []` | `ProjectMembershipData` | Get a single project membership |
```

In the Projects PHP example block, append before the closing fence:

```php
// List who has access to a project
$memberships = Asana::projects()->getMemberships('project_gid');
foreach ($memberships->data as $membership) {
    echo "{$membership->member->name}: {$membership->access_level}";
}
```

Immediately after the `#### ProjectData Properties` table (before the `---` that precedes `### Sections`), add:

```markdown
#### ProjectMembershipData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"project_membership"` |
| `resource_subtype` | `?string` | Membership subtype |
| `parent` | `?CompactResource` | The project |
| `member` | `?CompactResource` | The user or team |
| `access_level` | `?string` | `"admin"`, `"editor"`, `"commenter"`, or `"viewer"` |
| `user` | `?CompactResource` | The user (when member is a user) |
| `project` | `?CompactResource` | The project |
| `write_access` | `?string` | `"full_write"` or `"comment_only"` |
```

- [ ] **Step 10: Format and commit**

Run: `composer format`
Run: `composer test` — Expected: all green.

```bash
git add src/Requests/Projects/GetProjectMembershipsRequest.php src/Requests/Projects/GetProjectMembershipRequest.php src/Data/ProjectMembershipData.php src/Resources/ProjectResource.php tests/ArchTest.php tests/Unit/Resources/ProjectResourceTest.php tests/Unit/Data/ProjectMembershipDataTest.php README.md
git commit -m "feat(projects): add project membership endpoints

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

### Task 17: Teams — update + team memberships

**Files:**
- Create: `src/Requests/Teams/UpdateTeamRequest.php`
- Create: `src/Requests/Teams/GetTeamMembershipsRequest.php`
- Create: `src/Requests/Teams/GetTeamMembershipsForTeamRequest.php`
- Create: `src/Requests/Teams/GetTeamMembershipRequest.php`
- Create: `src/Data/TeamMembershipData.php`
- Modify: `src/Resources/TeamResource.php` (imports + 4 methods)
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (Teams table rows + example + TeamMembershipData table)
- Test: `tests/Unit/Resources/TeamResourceTest.php` (append; helper `createTeamResource` already exists)
- Test: `tests/Unit/Data/TeamMembershipDataTest.php`

- [ ] **Step 1: Write the failing DTO test**

Create `tests/Unit/Data/TeamMembershipDataTest.php`:

```php
<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\TeamMembershipData;

test('TeamMembershipData can be created from array', function () {
    $data = TeamMembershipData::from([
        'gid' => '800',
        'resource_type' => 'team_membership',
        'is_guest' => false,
        'is_limited_access' => false,
        'is_admin' => true,
    ]);

    expect($data->gid)->toBe('800')
        ->and($data->resource_type)->toBe('team_membership')
        ->and($data->is_guest)->toBeFalse()
        ->and($data->is_limited_access)->toBeFalse()
        ->and($data->is_admin)->toBeTrue();
});

test('TeamMembershipData handles null optional fields', function () {
    $data = TeamMembershipData::from(['gid' => '800']);

    expect($data->gid)->toBe('800')
        ->and($data->resource_type)->toBeNull()
        ->and($data->user)->toBeNull()
        ->and($data->team)->toBeNull()
        ->and($data->is_guest)->toBeNull()
        ->and($data->is_limited_access)->toBeNull()
        ->and($data->is_admin)->toBeNull();
});

test('TeamMembershipData casts user and team to CompactResource', function () {
    $data = TeamMembershipData::from([
        'gid' => '800',
        'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'team' => ['gid' => '50', 'name' => 'Engineering', 'resource_type' => 'team'],
    ]);

    expect($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->user->name)->toBe('Jane')
        ->and($data->team)->toBeInstanceOf(CompactResource::class)
        ->and($data->team->gid)->toBe('50');
});
```

- [ ] **Step 2: Write the failing Resource tests**

In `tests/Unit/Resources/TeamResourceTest.php`, add imports (alphabetical with existing):

```php
use Saloon\Http\Request;
use WMBH\Asana\Data\TeamMembershipData;
```

Append at the end of the file:

```php
test('update returns TeamData and sends PUT body with opt_fields', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '50',
            'name' => 'Platform',
            'resource_type' => 'team',
        ]], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->update('50', ['name' => 'Platform'], ['name']);

    expect($result)->toBeInstanceOf(TeamData::class)
        ->and($result->name)->toBe('Platform');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/teams/50'
        && $request->body()->all() === ['data' => ['name' => 'Platform']]
        && $request->query()->all() === ['opt_fields' => 'name']);
});

test('getMemberships returns PaginatedResponse of TeamMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'team_membership', 'is_admin' => true],
                ['gid' => '2', 'resource_type' => 'team_membership', 'is_admin' => false],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->getMemberships('team1', 'user1', 'ws1', ['is_admin'], 'abc', 10);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(TeamMembershipData::class)
        ->and($result->data[0]->is_admin)->toBeTrue()
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/team_memberships'
        && $request->query()->all() === [
            'team' => 'team1',
            'user' => 'user1',
            'workspace' => 'ws1',
            'opt_fields' => 'is_admin',
            'offset' => 'abc',
            'limit' => 10,
        ]);
});

test('getMemberships omits null filters', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $resource->getMemberships();

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === []);
});

test('getMembershipsForTeam returns PaginatedResponse and handles pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'team_membership']],
            'next_page' => ['offset' => 'tok', 'uri' => '/teams/team1/team_memberships?offset=tok'],
        ], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->getMembershipsForTeam('team1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(TeamMembershipData::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/teams/team1/team_memberships');
});

test('getMembership returns TeamMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '800',
            'resource_type' => 'team_membership',
            'is_guest' => true,
            'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        ]], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->getMembership('800', ['is_guest']);

    expect($result)->toBeInstanceOf(TeamMembershipData::class)
        ->and($result->gid)->toBe('800')
        ->and($result->is_guest)->toBeTrue()
        ->and($result->user->name)->toBe('Jane');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/team_memberships/800'
        && $request->query()->all() === ['opt_fields' => 'is_guest']);
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/TeamMembershipDataTest.php tests/Unit/Resources/TeamResourceTest.php`
Expected: FAIL with `Class "WMBH\Asana\Data\TeamMembershipData" not found` and `Call to undefined method WMBH\Asana\Resources\TeamResource::update()`.

- [ ] **Step 4: Create the DTO**

Create `src/Data/TeamMembershipData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class TeamMembershipData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?CompactResource $user = null,
        public readonly ?CompactResource $team = null,
        public readonly ?bool $is_guest = null,
        public readonly ?bool $is_limited_access = null,
        public readonly ?bool $is_admin = null,
    ) {}
}
```

- [ ] **Step 5: Create the Request classes**

Create `src/Requests/Teams/UpdateTeamRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Teams;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateTeamRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/teams/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/Teams/GetTeamMembershipsRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Teams;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTeamMembershipsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly ?string $teamGid = null,
        protected readonly ?string $userGid = null,
        protected readonly ?string $workspaceGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/team_memberships';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'team' => $this->teamGid,
            'user' => $this->userGid,
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

Create `src/Requests/Teams/GetTeamMembershipsForTeamRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Teams;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTeamMembershipsForTeamRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $teamGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/teams/{$this->teamGid}/team_memberships";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

Create `src/Requests/Teams/GetTeamMembershipRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Teams;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTeamMembershipRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/team_memberships/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

- [ ] **Step 6: Add the Resource methods**

In `src/Resources/TeamResource.php`, add imports (alphabetical among existing):

```php
use WMBH\Asana\Data\TeamMembershipData;
use WMBH\Asana\Requests\Teams\GetTeamMembershipRequest;
use WMBH\Asana\Requests\Teams\GetTeamMembershipsForTeamRequest;
use WMBH\Asana\Requests\Teams\GetTeamMembershipsRequest;
use WMBH\Asana\Requests\Teams\UpdateTeamRequest;
```

Append inside the class after `removeUser()`:

```php
    public function update(string $gid, array $data, array $optFields = []): TeamData
    {
        $response = $this->connector->send(new UpdateTeamRequest($gid, $data, $optFields));

        return TeamData::from($response->json('data'));
    }

    public function getMemberships(?string $teamGid = null, ?string $userGid = null, ?string $workspaceGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTeamMembershipsRequest($teamGid, $userGid, $workspaceGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TeamMembershipData::class);
    }

    public function getMembershipsForTeam(string $teamGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTeamMembershipsForTeamRequest($teamGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TeamMembershipData::class);
    }

    public function getMembership(string $membershipGid, array $optFields = []): TeamMembershipData
    {
        $response = $this->connector->send(new GetTeamMembershipRequest($membershipGid, $optFields));

        return TeamMembershipData::from($response->json('data'));
    }
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/TeamMembershipDataTest.php tests/Unit/Resources/TeamResourceTest.php`
Expected: PASS.

- [ ] **Step 8: Add ArchTest entries**

In `tests/ArchTest.php`, append under the Requests section:

```php
arch('UpdateTeamRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Teams\UpdateTeamRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('GetTeamMembershipsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Teams\GetTeamMembershipsRequest')
    ->toSendGetRequest();

arch('GetTeamMembershipRequest sends GET')
    ->expect('WMBH\Asana\Requests\Teams\GetTeamMembershipRequest')
    ->toSendGetRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php` — Expected: PASS.

- [ ] **Step 9: README**

In the `### Teams` method table, append after the `removeUser` row:

```markdown
| `update` | `string $gid`, `array $data`, `array $optFields = []` | `TeamData` | Update a team |
| `getMemberships` | `?string $teamGid = null`, `?string $userGid = null`, `?string $workspaceGid = null`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List team memberships filtered by team, user and/or workspace (items are `TeamMembershipData`) |
| `getMembershipsForTeam` | `string $teamGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List memberships of a team |
| `getMembership` | `string $membershipGid`, `array $optFields = []` | `TeamMembershipData` | Get a single team membership |
```

In the Teams PHP example block, append before the closing fence:

```php
// Rename a team
Asana::teams()->update('team_gid', ['name' => 'Platform']);

// Who is on the team, and are they admins?
$memberships = Asana::teams()->getMembershipsForTeam('team_gid');
foreach ($memberships->data as $membership) {
    echo "{$membership->user->name} admin=" . var_export($membership->is_admin, true);
}
```

Immediately after the `#### TeamData Properties` table (before the `---` that precedes `### Tags`), add:

```markdown
#### TeamMembershipData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"team_membership"` |
| `user` | `?CompactResource` | The user |
| `team` | `?CompactResource` | The team |
| `is_guest` | `?bool` | Whether the user is a guest in the team |
| `is_limited_access` | `?bool` | Whether the user has limited access |
| `is_admin` | `?bool` | Whether the user is a team admin |
```

- [ ] **Step 10: Format and commit**

Run: `composer format`
Run: `composer test` — Expected: all green.

```bash
git add src/Requests/Teams/UpdateTeamRequest.php src/Requests/Teams/GetTeamMembershipsRequest.php src/Requests/Teams/GetTeamMembershipsForTeamRequest.php src/Requests/Teams/GetTeamMembershipRequest.php src/Data/TeamMembershipData.php src/Resources/TeamResource.php tests/ArchTest.php tests/Unit/Resources/TeamResourceTest.php tests/Unit/Data/TeamMembershipDataTest.php README.md
git commit -m "feat(teams): add update and team membership endpoints

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

### Task 18: Workspace memberships + typeahead

**Files:**
- Create: `src/Requests/Workspaces/GetWorkspaceMembershipsRequest.php`
- Create: `src/Requests/Workspaces/GetWorkspaceMembershipRequest.php`
- Create: `src/Requests/Workspaces/TypeaheadRequest.php`
- Create: `src/Data/WorkspaceMembershipData.php`
- Modify: `src/Resources/WorkspaceResource.php` (imports + 3 methods)
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (Workspaces table rows + example + WorkspaceMembershipData table)
- Test: `tests/Unit/Resources/WorkspaceResourceTest.php` (append; helper `createWorkspaceResource` already exists)
- Test: `tests/Unit/Data/WorkspaceMembershipDataTest.php`

- [ ] **Step 1: Write the failing DTO test**

Create `tests/Unit/Data/WorkspaceMembershipDataTest.php`:

```php
<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\WorkspaceMembershipData;

test('WorkspaceMembershipData can be created from array', function () {
    $data = WorkspaceMembershipData::from([
        'gid' => '600',
        'resource_type' => 'workspace_membership',
        'is_active' => true,
        'is_admin' => false,
        'is_guest' => false,
        'is_view_only' => false,
        'created_at' => '2026-01-01T00:00:00.000Z',
        'vacation_dates' => ['start_on' => '2026-07-01', 'end_on' => '2026-07-14'],
    ]);

    expect($data->gid)->toBe('600')
        ->and($data->resource_type)->toBe('workspace_membership')
        ->and($data->is_active)->toBeTrue()
        ->and($data->is_admin)->toBeFalse()
        ->and($data->is_view_only)->toBeFalse()
        ->and($data->created_at)->toBe('2026-01-01T00:00:00.000Z')
        ->and($data->vacation_dates)->toBe(['start_on' => '2026-07-01', 'end_on' => '2026-07-14']);
});

test('WorkspaceMembershipData handles null optional fields', function () {
    $data = WorkspaceMembershipData::from(['gid' => '600']);

    expect($data->gid)->toBe('600')
        ->and($data->resource_type)->toBeNull()
        ->and($data->user)->toBeNull()
        ->and($data->workspace)->toBeNull()
        ->and($data->user_task_list)->toBeNull()
        ->and($data->is_active)->toBeNull()
        ->and($data->is_admin)->toBeNull()
        ->and($data->is_guest)->toBeNull()
        ->and($data->is_view_only)->toBeNull()
        ->and($data->vacation_dates)->toBeNull()
        ->and($data->created_at)->toBeNull();
});

test('WorkspaceMembershipData casts nested references to CompactResource', function () {
    $data = WorkspaceMembershipData::from([
        'gid' => '600',
        'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'workspace' => ['gid' => '10', 'name' => 'My Workspace', 'resource_type' => 'workspace'],
        'user_task_list' => ['gid' => '30', 'name' => 'My Tasks', 'resource_type' => 'user_task_list'],
    ]);

    expect($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->user->name)->toBe('Jane')
        ->and($data->workspace)->toBeInstanceOf(CompactResource::class)
        ->and($data->workspace->gid)->toBe('10')
        ->and($data->user_task_list)->toBeInstanceOf(CompactResource::class)
        ->and($data->user_task_list->name)->toBe('My Tasks');
});
```

- [ ] **Step 2: Write the failing Resource tests**

In `tests/Unit/Resources/WorkspaceResourceTest.php`, add imports (alphabetical with existing):

```php
use Saloon\Http\Request;
use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\WorkspaceMembershipData;
```

Append at the end of the file:

```php
test('getMemberships returns PaginatedResponse of WorkspaceMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'workspace_membership', 'is_admin' => true],
                ['gid' => '2', 'resource_type' => 'workspace_membership', 'is_admin' => false],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->getMemberships('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(WorkspaceMembershipData::class)
        ->and($result->data[0]->is_admin)->toBeTrue()
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/workspace_memberships'
        && $request->query()->all() === []);
});

test('getMemberships sends user filter and pagination as query params', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $resource->getMemberships('ws1', 'user1', ['is_admin'], 'abc', 25);

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === [
        'user' => 'user1',
        'opt_fields' => 'is_admin',
        'offset' => 'abc',
        'limit' => 25,
    ]);
});

test('getMemberships handles pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'workspace_membership']],
            'next_page' => ['offset' => 'tok', 'uri' => '/workspaces/ws1/workspace_memberships?offset=tok'],
        ], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->getMemberships('ws1');

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');
});

test('getMembership returns WorkspaceMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '600',
            'resource_type' => 'workspace_membership',
            'is_guest' => true,
            'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        ]], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->getMembership('600', ['is_guest']);

    expect($result)->toBeInstanceOf(WorkspaceMembershipData::class)
        ->and($result->gid)->toBe('600')
        ->and($result->is_guest)->toBeTrue()
        ->and($result->user->name)->toBe('Jane');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspace_memberships/600'
        && $request->query()->all() === ['opt_fields' => 'is_guest']);
});

test('typeahead returns PaginatedResponse of CompactResource', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Marketing Launch', 'resource_type' => 'project'],
                ['gid' => '2', 'name' => 'Marketing Site', 'resource_type' => 'project'],
            ],
        ], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->typeahead('ws1', 'project', 'Marketing', 5, ['name']);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(CompactResource::class)
        ->and($result->data[0]->name)->toBe('Marketing Launch')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/typeahead'
        && $request->query()->all() === [
            'resource_type' => 'project',
            'query' => 'Marketing',
            'count' => 5,
            'opt_fields' => 'name',
        ]);
});

test('typeahead sends only resource_type when query and count are omitted', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $resource->typeahead('ws1', 'user');

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === ['resource_type' => 'user']);
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/WorkspaceMembershipDataTest.php tests/Unit/Resources/WorkspaceResourceTest.php`
Expected: FAIL with `Class "WMBH\Asana\Data\WorkspaceMembershipData" not found` and `Call to undefined method WMBH\Asana\Resources\WorkspaceResource::getMemberships()`.

- [ ] **Step 4: Create the DTO**

Create `src/Data/WorkspaceMembershipData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class WorkspaceMembershipData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?CompactResource $user = null,
        public readonly ?CompactResource $workspace = null,
        public readonly ?CompactResource $user_task_list = null,
        public readonly ?bool $is_active = null,
        public readonly ?bool $is_admin = null,
        public readonly ?bool $is_guest = null,
        public readonly ?bool $is_view_only = null,
        public readonly ?array $vacation_dates = null,
        public readonly ?string $created_at = null,
    ) {}
}
```

- [ ] **Step 5: Create the Request classes**

Create `src/Requests/Workspaces/GetWorkspaceMembershipsRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Workspaces;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWorkspaceMembershipsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly ?string $userGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/workspace_memberships";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'user' => $this->userGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

Create `src/Requests/Workspaces/GetWorkspaceMembershipRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Workspaces;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWorkspaceMembershipRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspace_memberships/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

Create `src/Requests/Workspaces/TypeaheadRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Workspaces;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class TypeaheadRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly string $resourceType,
        protected readonly ?string $search = null,
        protected readonly ?int $count = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/typeahead";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'resource_type' => $this->resourceType,
            'query' => $this->search,
            'count' => $this->count,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

- [ ] **Step 6: Add the Resource methods**

In `src/Resources/WorkspaceResource.php`, add imports (alphabetical among existing):

```php
use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\WorkspaceMembershipData;
use WMBH\Asana\Requests\Workspaces\GetWorkspaceMembershipRequest;
use WMBH\Asana\Requests\Workspaces\GetWorkspaceMembershipsRequest;
use WMBH\Asana\Requests\Workspaces\TypeaheadRequest;
```

Append inside the class after `removeUser()`:

```php
    public function getMemberships(string $workspaceGid, ?string $userGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetWorkspaceMembershipsRequest($workspaceGid, $userGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), WorkspaceMembershipData::class);
    }

    public function getMembership(string $membershipGid, array $optFields = []): WorkspaceMembershipData
    {
        $response = $this->connector->send(new GetWorkspaceMembershipRequest($membershipGid, $optFields));

        return WorkspaceMembershipData::from($response->json('data'));
    }

    public function typeahead(string $workspaceGid, string $resourceType, ?string $query = null, ?int $count = null, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new TypeaheadRequest($workspaceGid, $resourceType, $query, $count, $optFields));

        return PaginatedResponse::fromResponse($response->json(), CompactResource::class);
    }
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/WorkspaceMembershipDataTest.php tests/Unit/Resources/WorkspaceResourceTest.php`
Expected: PASS.

- [ ] **Step 8: Add ArchTest entries**

In `tests/ArchTest.php`, append under the Requests section:

```php
arch('GetWorkspaceMembershipsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Workspaces\GetWorkspaceMembershipsRequest')
    ->toSendGetRequest();

arch('GetWorkspaceMembershipRequest sends GET')
    ->expect('WMBH\Asana\Requests\Workspaces\GetWorkspaceMembershipRequest')
    ->toSendGetRequest();

arch('TypeaheadRequest sends GET')
    ->expect('WMBH\Asana\Requests\Workspaces\TypeaheadRequest')
    ->toSendGetRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php` — Expected: PASS.

- [ ] **Step 9: README**

In the `### Workspaces` method table, append after the `removeUser` row:

```markdown
| `getMemberships` | `string $workspaceGid`, `?string $userGid = null`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List workspace memberships (items are `WorkspaceMembershipData`) |
| `getMembership` | `string $membershipGid`, `array $optFields = []` | `WorkspaceMembershipData` | Get a single workspace membership |
| `typeahead` | `string $workspaceGid`, `string $resourceType`, `?string $query = null`, `?int $count = null`, `array $optFields = []` | `PaginatedResponse` | Search-as-you-type across `user`, `project`, `task`, `tag`, `team`, `portfolio`, `goal`, `custom_field` (items are `CompactResource`) |
```

In the Workspaces PHP example block, append before the closing fence:

```php
// Who is in the workspace, and are they guests?
$memberships = Asana::workspaces()->getMemberships('workspace_gid');
foreach ($memberships->data as $membership) {
    echo "{$membership->user->name} guest=" . var_export($membership->is_guest, true);
}

// Typeahead: find projects whose name matches "Marketing"
$matches = Asana::workspaces()->typeahead('workspace_gid', 'project', 'Marketing', 5);
foreach ($matches->data as $match) {
    echo "{$match->gid}: {$match->name}";
}
```

Immediately after the `#### WorkspaceData Properties` table (before the `---` that precedes `### Teams`), add:

```markdown
#### WorkspaceMembershipData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"workspace_membership"` |
| `user` | `?CompactResource` | The user |
| `workspace` | `?CompactResource` | The workspace |
| `user_task_list` | `?CompactResource` | The user's "My Tasks" list in this workspace |
| `is_active` | `?bool` | Whether the membership is active |
| `is_admin` | `?bool` | Whether the user is a workspace admin |
| `is_guest` | `?bool` | Whether the user is a guest |
| `is_view_only` | `?bool` | Whether the user has view-only access |
| `vacation_dates` | `?array` | `['start_on' => ..., 'end_on' => ...]` when out of office |
| `created_at` | `?string` | Creation timestamp |
```

- [ ] **Step 10: Format and commit**

Run: `composer format`
Run: `composer test` — Expected: all green.

```bash
git add src/Requests/Workspaces/GetWorkspaceMembershipsRequest.php src/Requests/Workspaces/GetWorkspaceMembershipRequest.php src/Requests/Workspaces/TypeaheadRequest.php src/Data/WorkspaceMembershipData.php src/Resources/WorkspaceResource.php tests/ArchTest.php tests/Unit/Resources/WorkspaceResourceTest.php tests/Unit/Data/WorkspaceMembershipDataTest.php README.md
git commit -m "feat(workspaces): add workspace membership and typeahead endpoints

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

### Task 19: Users — update, favorites, per-workspace user, memberships

Depends on Task 17 (`TeamMembershipData`) and Task 18 (`WorkspaceMembershipData`) being merged first.

**Files:**
- Create: `src/Requests/Users/UpdateUserRequest.php`
- Create: `src/Requests/Users/GetFavoritesForUserRequest.php`
- Create: `src/Requests/Users/GetUserForWorkspaceRequest.php`
- Create: `src/Requests/Users/UpdateUserForWorkspaceRequest.php`
- Create: `src/Requests/Teams/GetTeamMembershipsForUserRequest.php`
- Create: `src/Requests/Workspaces/GetWorkspaceMembershipsForUserRequest.php`
- Modify: `src/Resources/UserResource.php` (imports + 6 methods)
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (Users table rows + example)
- Test: `tests/Unit/Resources/UserResourceTest.php` (append; helper `createUserResource` already exists)

- [ ] **Step 1: Write the failing Resource tests**

In `tests/Unit/Resources/UserResourceTest.php`, add imports (alphabetical with existing):

```php
use Saloon\Http\Request;
use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\TeamMembershipData;
use WMBH\Asana\Data\WorkspaceMembershipData;
```

Append at the end of the file:

```php
test('update returns UserData and sends PUT body', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '111',
            'name' => 'Johnathan Doe',
            'resource_type' => 'user',
        ]], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->update('111', ['name' => 'Johnathan Doe']);

    expect($result)->toBeInstanceOf(UserData::class)
        ->and($result->name)->toBe('Johnathan Doe');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/users/111'
        && $request->body()->all() === ['data' => ['name' => 'Johnathan Doe']]
        && $request->query()->all() === []);
});

test('update sends workspace and opt_fields as query params', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => '111', 'resource_type' => 'user']], 200),
    ]);

    $resource = createUserResource($mockClient);
    $resource->update('111', ['custom_fields' => ['123' => 'x']], 'ws1', ['name', 'custom_fields']);

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === [
        'workspace' => 'ws1',
        'opt_fields' => 'name,custom_fields',
    ]);
});

test('getFavorites returns PaginatedResponse of CompactResource', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Roadmap', 'resource_type' => 'project'],
                ['gid' => '2', 'name' => 'Backlog', 'resource_type' => 'project'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->getFavorites('me', 'project', 'ws1', 'abc', 20, ['name']);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(CompactResource::class)
        ->and($result->data[0]->name)->toBe('Roadmap')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/users/me/favorites'
        && $request->query()->all() === [
            'resource_type' => 'project',
            'workspace' => 'ws1',
            'offset' => 'abc',
            'limit' => 20,
            'opt_fields' => 'name',
        ]);
});

test('getInWorkspace returns UserData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '111',
            'name' => 'John Doe',
            'resource_type' => 'user',
        ]], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->getInWorkspace('ws1', '111', ['name']);

    expect($result)->toBeInstanceOf(UserData::class)
        ->and($result->gid)->toBe('111');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/users/111'
        && $request->query()->all() === ['opt_fields' => 'name']);
});

test('updateInWorkspace returns UserData and sends PUT body', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '111',
            'name' => 'John Doe',
            'resource_type' => 'user',
        ]], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->updateInWorkspace('ws1', '111', ['custom_fields' => ['123' => 'x']]);

    expect($result)->toBeInstanceOf(UserData::class)
        ->and($result->gid)->toBe('111');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/users/111'
        && $request->body()->all() === ['data' => ['custom_fields' => ['123' => 'x']]]);
});

test('getTeamMemberships returns PaginatedResponse of TeamMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'team_membership', 'is_admin' => false]],
            'next_page' => ['offset' => 'tok', 'uri' => '/users/111/team_memberships?offset=tok'],
        ], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->getTeamMemberships('111', 'ws1', ['is_admin'], null, 10);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(TeamMembershipData::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/users/111/team_memberships'
        && $request->query()->all() === [
            'workspace' => 'ws1',
            'opt_fields' => 'is_admin',
            'limit' => 10,
        ]);
});

test('getWorkspaceMemberships returns PaginatedResponse of WorkspaceMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'workspace_membership', 'is_guest' => true]],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->getWorkspaceMemberships('111', ['is_guest'], 'abc', 5);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(WorkspaceMembershipData::class)
        ->and($result->data[0]->is_guest)->toBeTrue()
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/users/111/workspace_memberships'
        && $request->query()->all() === [
            'opt_fields' => 'is_guest',
            'offset' => 'abc',
            'limit' => 5,
        ]);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Resources/UserResourceTest.php`
Expected: FAIL with `Call to undefined method WMBH\Asana\Resources\UserResource::update()`.

- [ ] **Step 3: Create the Request classes**

Create `src/Requests/Users/UpdateUserRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Users;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateUserRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $data,
        protected readonly ?string $workspaceGid = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/Users/GetFavoritesForUserRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Users;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetFavoritesForUserRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $userGid,
        protected readonly string $resourceType,
        protected readonly string $workspaceGid,
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->userGid}/favorites";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'resource_type' => $this->resourceType,
            'workspace' => $this->workspaceGid,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

Create `src/Requests/Users/GetUserForWorkspaceRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Users;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUserForWorkspaceRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly string $userGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/users/{$this->userGid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

Create `src/Requests/Users/UpdateUserForWorkspaceRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Users;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateUserForWorkspaceRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly string $userGid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/users/{$this->userGid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/Teams/GetTeamMembershipsForUserRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Teams;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTeamMembershipsForUserRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $userGid,
        protected readonly string $workspaceGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->userGid}/team_memberships";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

Create `src/Requests/Workspaces/GetWorkspaceMembershipsForUserRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Workspaces;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWorkspaceMembershipsForUserRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $userGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->userGid}/workspace_memberships";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

- [ ] **Step 4: Add the Resource methods**

In `src/Resources/UserResource.php`, add imports (alphabetical among existing):

```php
use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\TeamMembershipData;
use WMBH\Asana\Data\WorkspaceMembershipData;
use WMBH\Asana\Requests\Teams\GetTeamMembershipsForUserRequest;
use WMBH\Asana\Requests\Users\GetFavoritesForUserRequest;
use WMBH\Asana\Requests\Users\GetUserForWorkspaceRequest;
use WMBH\Asana\Requests\Users\UpdateUserForWorkspaceRequest;
use WMBH\Asana\Requests\Users\UpdateUserRequest;
use WMBH\Asana\Requests\Workspaces\GetWorkspaceMembershipsForUserRequest;
```

Append inside the class after `me()`:

```php
    public function update(string $gid, array $data, ?string $workspaceGid = null, array $optFields = []): UserData
    {
        $response = $this->connector->send(new UpdateUserRequest($gid, $data, $workspaceGid, $optFields));

        return UserData::from($response->json('data'));
    }

    public function getFavorites(string $userGid, string $resourceType, string $workspaceGid, ?string $offset = null, ?int $limit = null, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetFavoritesForUserRequest($userGid, $resourceType, $workspaceGid, $offset, $limit, $optFields));

        return PaginatedResponse::fromResponse($response->json(), CompactResource::class);
    }

    public function getInWorkspace(string $workspaceGid, string $userGid, array $optFields = []): UserData
    {
        $response = $this->connector->send(new GetUserForWorkspaceRequest($workspaceGid, $userGid, $optFields));

        return UserData::from($response->json('data'));
    }

    public function updateInWorkspace(string $workspaceGid, string $userGid, array $data, array $optFields = []): UserData
    {
        $response = $this->connector->send(new UpdateUserForWorkspaceRequest($workspaceGid, $userGid, $data, $optFields));

        return UserData::from($response->json('data'));
    }

    public function getTeamMemberships(string $userGid, string $workspaceGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTeamMembershipsForUserRequest($userGid, $workspaceGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TeamMembershipData::class);
    }

    public function getWorkspaceMemberships(string $userGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetWorkspaceMembershipsForUserRequest($userGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), WorkspaceMembershipData::class);
    }
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Resources/UserResourceTest.php`
Expected: PASS.

- [ ] **Step 6: Add ArchTest entries**

In `tests/ArchTest.php`, append under the Requests section:

```php
arch('UpdateUserRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Users\UpdateUserRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('UpdateUserForWorkspaceRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Users\UpdateUserForWorkspaceRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('GetFavoritesForUserRequest sends GET')
    ->expect('WMBH\Asana\Requests\Users\GetFavoritesForUserRequest')
    ->toSendGetRequest();

arch('GetUserForWorkspaceRequest sends GET')
    ->expect('WMBH\Asana\Requests\Users\GetUserForWorkspaceRequest')
    ->toSendGetRequest();

arch('GetTeamMembershipsForUserRequest sends GET')
    ->expect('WMBH\Asana\Requests\Teams\GetTeamMembershipsForUserRequest')
    ->toSendGetRequest();

arch('GetWorkspaceMembershipsForUserRequest sends GET')
    ->expect('WMBH\Asana\Requests\Workspaces\GetWorkspaceMembershipsForUserRequest')
    ->toSendGetRequest();
```

Run: `vendor/bin/pest tests/ArchTest.php` — Expected: PASS.

- [ ] **Step 7: README**

In the `### Users` method table, append after the `me` row:

```markdown
| `update` | `string $gid`, `array $data`, `?string $workspaceGid = null`, `array $optFields = []` | `UserData` | Update a user (`name`, `custom_fields`); pass `$workspaceGid` when setting workspace-scoped custom fields |
| `getFavorites` | `string $userGid`, `string $resourceType`, `string $workspaceGid`, `?string $offset = null`, `?int $limit = null`, `array $optFields = []` | `PaginatedResponse` | The user's sidebar favorites of one type (`project`, `portfolio`, `tag`, `task`, `user`, `project_template`); current user only (items are `CompactResource`) |
| `getInWorkspace` | `string $workspaceGid`, `string $userGid`, `array $optFields = []` | `UserData` | Get a user as seen in one workspace |
| `updateInWorkspace` | `string $workspaceGid`, `string $userGid`, `array $data`, `array $optFields = []` | `UserData` | Update a user within one workspace |
| `getTeamMemberships` | `string $userGid`, `string $workspaceGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | The user's team memberships in a workspace (items are `TeamMembershipData`) |
| `getWorkspaceMemberships` | `string $userGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | The user's workspace memberships (items are `WorkspaceMembershipData`) |
```

In the Users PHP example block, append before the closing fence:

```php
// Rename the current user
Asana::users()->update('me', ['name' => 'Jane Doe']);

// Favourite projects in a workspace
$favorites = Asana::users()->getFavorites('me', 'project', 'workspace_gid');

// Which teams is a user on, and where are they admin?
$memberships = Asana::users()->getTeamMemberships('user_gid', 'workspace_gid');
foreach ($memberships->data as $membership) {
    echo "{$membership->team->name} admin=" . var_export($membership->is_admin, true);
}
```

- [ ] **Step 8: Format and commit**

Run: `composer format`
Run: `composer test` — Expected: all green.

```bash
git add src/Requests/Users/UpdateUserRequest.php src/Requests/Users/GetFavoritesForUserRequest.php src/Requests/Users/GetUserForWorkspaceRequest.php src/Requests/Users/UpdateUserForWorkspaceRequest.php src/Requests/Teams/GetTeamMembershipsForUserRequest.php src/Requests/Workspaces/GetWorkspaceMembershipsForUserRequest.php src/Resources/UserResource.php tests/ArchTest.php tests/Unit/Resources/UserResourceTest.php README.md
git commit -m "feat(users): add update, favorites, per-workspace user and membership endpoints

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```


---

### Task 20: Custom field settings + enum options

Both this task and Task 21 append methods to `src/Resources/ProjectResource.php` and rows to the README Projects table. Do Task 20 first, then Task 21.

**Files:**
- Create: `src/Requests/CustomFields/GetCustomFieldSettingsForProjectRequest.php`
- Create: `src/Requests/CustomFields/GetCustomFieldSettingsForTeamRequest.php`
- Create: `src/Requests/CustomFields/CreateEnumOptionRequest.php`
- Create: `src/Requests/CustomFields/InsertEnumOptionRequest.php`
- Create: `src/Requests/CustomFields/UpdateEnumOptionRequest.php`
- Create: `src/Requests/Projects/AddCustomFieldSettingToProjectRequest.php`
- Create: `src/Requests/Projects/RemoveCustomFieldSettingFromProjectRequest.php`
- Create: `src/Data/CustomFieldSettingData.php`
- Create: `src/Data/EnumOptionData.php`
- Modify: `src/Resources/CustomFieldResource.php` (imports + 5 methods appended after `delete()`)
- Modify: `src/Resources/ProjectResource.php` (imports + 2 methods appended after `getTaskCounts()`)
- Test (create): `tests/Unit/Data/CustomFieldSettingDataTest.php`
- Test (create): `tests/Unit/Data/EnumOptionDataTest.php`
- Test (modify): `tests/Unit/Resources/CustomFieldResourceTest.php` (helper `createCustomFieldResource` already exists — reuse it)
- Test (modify): `tests/Unit/Resources/ProjectResourceTest.php` (helper `createProjectResource` already exists — reuse it)
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (Custom Fields section ~line 661, Projects table ~line 240)

- [ ] **Step 1: Write the failing DTO tests**

Create `tests/Unit/Data/CustomFieldSettingDataTest.php`:

```php
<?php

use WMBH\Asana\Data\CustomFieldSettingData;
use WMBH\Asana\Data\Shared\CompactResource;

test('CustomFieldSettingData can be created from array', function () {
    $data = CustomFieldSettingData::from([
        'gid' => '55',
        'resource_type' => 'custom_field_setting',
        'is_important' => true,
        'custom_field' => ['gid' => '900', 'name' => 'Priority', 'resource_subtype' => 'enum'],
    ]);

    expect($data->gid)->toBe('55')
        ->and($data->resource_type)->toBe('custom_field_setting')
        ->and($data->is_important)->toBeTrue()
        ->and($data->custom_field)->toBeArray()
        ->and($data->custom_field['name'])->toBe('Priority');
});

test('CustomFieldSettingData handles null optional fields', function () {
    $data = CustomFieldSettingData::from(['gid' => '55']);

    expect($data->gid)->toBe('55')
        ->and($data->resource_type)->toBeNull()
        ->and($data->project)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->is_important)->toBeNull()
        ->and($data->custom_field)->toBeNull();
});

test('CustomFieldSettingData casts nested project and parent to CompactResource', function () {
    $data = CustomFieldSettingData::from([
        'gid' => '55',
        'project' => ['gid' => '789', 'resource_type' => 'project', 'name' => 'Sprint'],
        'parent' => ['gid' => '789', 'resource_type' => 'project', 'name' => 'Sprint'],
    ]);

    expect($data->project)->toBeInstanceOf(CompactResource::class)
        ->and($data->project->gid)->toBe('789')
        ->and($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->parent->name)->toBe('Sprint');
});
```

Create `tests/Unit/Data/EnumOptionDataTest.php`:

```php
<?php

use WMBH\Asana\Data\EnumOptionData;

test('EnumOptionData can be created from array', function () {
    $data = EnumOptionData::from([
        'gid' => '11',
        'resource_type' => 'enum_option',
        'name' => 'Urgent',
        'enabled' => true,
        'color' => 'red',
    ]);

    expect($data->gid)->toBe('11')
        ->and($data->resource_type)->toBe('enum_option')
        ->and($data->name)->toBe('Urgent')
        ->and($data->enabled)->toBeTrue()
        ->and($data->color)->toBe('red');
});

test('EnumOptionData handles null optional fields', function () {
    $data = EnumOptionData::from(['gid' => '11']);

    expect($data->gid)->toBe('11')
        ->and($data->resource_type)->toBeNull()
        ->and($data->name)->toBeNull()
        ->and($data->enabled)->toBeNull()
        ->and($data->color)->toBeNull();
});
```

- [ ] **Step 2: Write the failing Resource tests**

Add these imports to the `use` block at the top of `tests/Unit/Resources/CustomFieldResourceTest.php`:

```php
use WMBH\Asana\Data\CustomFieldSettingData;
use WMBH\Asana\Data\EnumOptionData;
use WMBH\Asana\Requests\CustomFields\GetCustomFieldSettingsForTeamRequest;
```

Append to the end of `tests/Unit/Resources/CustomFieldResourceTest.php`:

```php
test('getSettingsForProject returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                [
                    'gid' => '1',
                    'resource_type' => 'custom_field_setting',
                    'is_important' => true,
                    'custom_field' => ['gid' => '900', 'name' => 'Priority'],
                ],
            ],
            'next_page' => ['offset' => 'tok', 'uri' => '/projects/p1/custom_field_settings?offset=tok'],
        ], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->getSettingsForProject('p1', ['is_important'], null, 50);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(CustomFieldSettingData::class)
        ->and($result->data[0]->is_important)->toBeTrue()
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/projects/p1/custom_field_settings'
            && $request->query()->all() === ['opt_fields' => 'is_important', 'limit' => 50];
    });
});

test('getSettingsForTeam returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'custom_field_setting'],
                ['gid' => '2', 'resource_type' => 'custom_field_setting'],
            ],
        ], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->getSettingsForTeam('team1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(CustomFieldSettingData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(GetCustomFieldSettingsForTeamRequest::class);
});

test('createEnumOption returns EnumOptionData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '11',
            'resource_type' => 'enum_option',
            'name' => 'Urgent',
            'enabled' => true,
            'color' => 'red',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->createEnumOption('900', ['name' => 'Urgent', 'color' => 'red']);

    expect($result)->toBeInstanceOf(EnumOptionData::class)
        ->and($result->gid)->toBe('11')
        ->and($result->name)->toBe('Urgent')
        ->and($result->color)->toBe('red');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/custom_fields/900/enum_options'
            && $request->body()->all() === ['data' => ['name' => 'Urgent', 'color' => 'red']];
    });
});

test('insertEnumOption returns EnumOptionData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '11',
            'resource_type' => 'enum_option',
            'name' => 'Urgent',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->insertEnumOption('900', ['enum_option' => '11', 'before_enum_option' => '12']);

    expect($result)->toBeInstanceOf(EnumOptionData::class)
        ->and($result->gid)->toBe('11');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/custom_fields/900/enum_options/insert'
            && $request->body()->all() === ['data' => ['enum_option' => '11', 'before_enum_option' => '12']];
    });
});

test('updateEnumOption returns EnumOptionData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '11',
            'resource_type' => 'enum_option',
            'name' => 'Critical',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->updateEnumOption('11', ['name' => 'Critical']);

    expect($result)->toBeInstanceOf(EnumOptionData::class)
        ->and($result->name)->toBe('Critical');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/enum_options/11'
            && $request->body()->all() === ['data' => ['name' => 'Critical']];
    });
});
```

Add these imports to the `use` block at the top of `tests/Unit/Resources/ProjectResourceTest.php`:

```php
use WMBH\Asana\Data\CustomFieldSettingData;
use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Requests\Projects\RemoveCustomFieldSettingFromProjectRequest;
```

Append to the end of `tests/Unit/Resources/ProjectResourceTest.php`:

```php
test('addCustomFieldSetting returns CustomFieldSettingData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '55',
            'resource_type' => 'custom_field_setting',
            'is_important' => true,
            'project' => ['gid' => '789', 'resource_type' => 'project', 'name' => 'Test Project'],
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->addCustomFieldSetting('789', ['custom_field' => '900', 'is_important' => true]);

    expect($result)->toBeInstanceOf(CustomFieldSettingData::class)
        ->and($result->gid)->toBe('55')
        ->and($result->is_important)->toBeTrue()
        ->and($result->project)->toBeInstanceOf(CompactResource::class)
        ->and($result->project->gid)->toBe('789');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/projects/789/addCustomFieldSetting'
            && $request->body()->all() === ['data' => ['custom_field' => '900', 'is_important' => true]];
    });
});

test('removeCustomFieldSetting sends request with custom_field in body', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $resource->removeCustomFieldSetting('789', '900');

    $mockClient->assertSent(function ($request) {
        return $request instanceof RemoveCustomFieldSettingFromProjectRequest
            && $request->resolveEndpoint() === '/projects/789/removeCustomFieldSetting'
            && $request->body()->all() === ['data' => ['custom_field' => '900']];
    });
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Data/CustomFieldSettingDataTest.php tests/Unit/Data/EnumOptionDataTest.php tests/Unit/Resources/CustomFieldResourceTest.php tests/Unit/Resources/ProjectResourceTest.php`

Expected: FAIL. Data tests: `Error: Class "WMBH\Asana\Data\CustomFieldSettingData" not found` / `Class "WMBH\Asana\Data\EnumOptionData" not found`. Resource tests: `Error: Call to undefined method WMBH\Asana\Resources\CustomFieldResource::getSettingsForProject()` (and the same for the other four new methods) and `Call to undefined method WMBH\Asana\Resources\ProjectResource::addCustomFieldSetting()`. The `use` of `RemoveCustomFieldSettingFromProjectRequest` in ProjectResourceTest only fails when the closure runs (`Class ... not found`).

- [ ] **Step 4: Create the DTOs**

Create `src/Data/CustomFieldSettingData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class CustomFieldSettingData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?CompactResource $project = null,
        public readonly ?CompactResource $parent = null,
        public readonly ?bool $is_important = null,
        public readonly ?array $custom_field = null,
    ) {}
}
```

Create `src/Data/EnumOptionData.php`:

```php
<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;

class EnumOptionData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?bool $enabled = null,
        public readonly ?string $color = null,
    ) {}
}
```

- [ ] **Step 5: Create the Request classes**

Create `src/Requests/CustomFields/GetCustomFieldSettingsForProjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\CustomFields;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCustomFieldSettingsForProjectRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $projectGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->projectGid}/custom_field_settings";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
```

Create `src/Requests/CustomFields/GetCustomFieldSettingsForTeamRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\CustomFields;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCustomFieldSettingsForTeamRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $teamGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/teams/{$this->teamGid}/custom_field_settings";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
```

Create `src/Requests/CustomFields/CreateEnumOptionRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\CustomFields;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateEnumOptionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $customFieldGid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/custom_fields/{$this->customFieldGid}/enum_options";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/CustomFields/InsertEnumOptionRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\CustomFields;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class InsertEnumOptionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $customFieldGid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/custom_fields/{$this->customFieldGid}/enum_options/insert";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/CustomFields/UpdateEnumOptionRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\CustomFields;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateEnumOptionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected readonly string $enumOptionGid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/enum_options/{$this->enumOptionGid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/Projects/AddCustomFieldSettingToProjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AddCustomFieldSettingToProjectRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}/addCustomFieldSetting";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/Projects/RemoveCustomFieldSettingFromProjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveCustomFieldSettingFromProjectRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly string $customFieldGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}/removeCustomFieldSetting";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['custom_field' => $this->customFieldGid]];
    }
}
```

- [ ] **Step 6: Add the Resource methods**

In `src/Resources/CustomFieldResource.php`, add these `use` lines to the import block (Pint will sort them in Step 10):

```php
use WMBH\Asana\Data\CustomFieldSettingData;
use WMBH\Asana\Data\EnumOptionData;
use WMBH\Asana\Requests\CustomFields\CreateEnumOptionRequest;
use WMBH\Asana\Requests\CustomFields\GetCustomFieldSettingsForProjectRequest;
use WMBH\Asana\Requests\CustomFields\GetCustomFieldSettingsForTeamRequest;
use WMBH\Asana\Requests\CustomFields\InsertEnumOptionRequest;
use WMBH\Asana\Requests\CustomFields\UpdateEnumOptionRequest;
```

and append these methods after `delete()` inside the class:

```php
    public function getSettingsForProject(string $projectGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetCustomFieldSettingsForProjectRequest($projectGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), CustomFieldSettingData::class);
    }

    public function getSettingsForTeam(string $teamGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetCustomFieldSettingsForTeamRequest($teamGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), CustomFieldSettingData::class);
    }

    public function createEnumOption(string $customFieldGid, array $data, array $optFields = []): EnumOptionData
    {
        $response = $this->connector->send(new CreateEnumOptionRequest($customFieldGid, $data, $optFields));

        return EnumOptionData::from($response->json('data'));
    }

    public function insertEnumOption(string $customFieldGid, array $data, array $optFields = []): EnumOptionData
    {
        $response = $this->connector->send(new InsertEnumOptionRequest($customFieldGid, $data, $optFields));

        return EnumOptionData::from($response->json('data'));
    }

    public function updateEnumOption(string $enumOptionGid, array $data, array $optFields = []): EnumOptionData
    {
        $response = $this->connector->send(new UpdateEnumOptionRequest($enumOptionGid, $data, $optFields));

        return EnumOptionData::from($response->json('data'));
    }
```

In `src/Resources/ProjectResource.php`, add these `use` lines:

```php
use WMBH\Asana\Data\CustomFieldSettingData;
use WMBH\Asana\Requests\Projects\AddCustomFieldSettingToProjectRequest;
use WMBH\Asana\Requests\Projects\RemoveCustomFieldSettingFromProjectRequest;
```

and append these methods after `getTaskCounts()` inside the class:

```php
    public function addCustomFieldSetting(string $gid, array $data, array $optFields = []): CustomFieldSettingData
    {
        $response = $this->connector->send(new AddCustomFieldSettingToProjectRequest($gid, $data, $optFields));

        return CustomFieldSettingData::from($response->json('data'));
    }

    public function removeCustomFieldSetting(string $gid, string $customFieldGid): void
    {
        $this->connector->send(new RemoveCustomFieldSettingFromProjectRequest($gid, $customFieldGid));
    }
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Data/CustomFieldSettingDataTest.php tests/Unit/Data/EnumOptionDataTest.php tests/Unit/Resources/CustomFieldResourceTest.php tests/Unit/Resources/ProjectResourceTest.php`

Expected: PASS — `Tests: 27 passed` (5 data + 10 custom-field resource + 12 project resource; the count line may differ if other tasks already added tests, but zero failures).

- [ ] **Step 8: Add ArchTest entries**

In `tests/ArchTest.php`, append after the `arch('DeleteWebhookRequest sends DELETE')` block (still inside the "Requests (Lawman)" section):

```php
arch('GetCustomFieldSettingsForProjectRequest sends GET')
    ->expect('WMBH\Asana\Requests\CustomFields\GetCustomFieldSettingsForProjectRequest')
    ->toSendGetRequest();

arch('CreateEnumOptionRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\CustomFields\CreateEnumOptionRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('UpdateEnumOptionRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\CustomFields\UpdateEnumOptionRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('RemoveCustomFieldSettingFromProjectRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Projects\RemoveCustomFieldSettingFromProjectRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS.

- [ ] **Step 9: Update README**

In `README.md`, Custom Fields section: after the table row that starts with ``| `delete` | `string $gid` | `bool` | Delete a custom field |`` add:

```markdown
| `getSettingsForProject` | `string $projectGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null` | `PaginatedResponse` | List custom field settings on a project |
| `getSettingsForTeam` | `string $teamGid`, `array $optFields = []` | `PaginatedResponse` | List custom field settings on a team |
| `createEnumOption` | `string $customFieldGid`, `array $data`, `array $optFields = []` | `EnumOptionData` | Add an enum option to a custom field |
| `insertEnumOption` | `string $customFieldGid`, `array $data`, `array $optFields = []` | `EnumOptionData` | Move an enum option (`enum_option`, `before_enum_option` / `after_enum_option`) |
| `updateEnumOption` | `string $enumOptionGid`, `array $data`, `array $optFields = []` | `EnumOptionData` | Update an enum option |
```

In the same section's ```php example block, before the closing ``` (after the `Asana::customFields()->delete('field_gid');` line) add:

```php

// Custom field settings on a project / team
$settings = Asana::customFields()->getSettingsForProject('project_gid');
$teamSettings = Asana::customFields()->getSettingsForTeam('team_gid');

// Enum options: add, reorder, update
$option = Asana::customFields()->createEnumOption('field_gid', ['name' => 'Urgent', 'color' => 'red']);
Asana::customFields()->insertEnumOption('field_gid', [
    'enum_option' => $option->gid,
    'before_enum_option' => 'other_option_gid',
]);
Asana::customFields()->updateEnumOption($option->gid, ['name' => 'Critical', 'enabled' => false]);
```

After the `#### CustomFieldData Properties` table (before the `---` separator that ends the Custom Fields section) add:

```markdown
#### CustomFieldSettingData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"custom_field_setting"` |
| `project` | `?CompactResource` | Project the setting belongs to (deprecated by Asana, prefer `parent`) |
| `parent` | `?CompactResource` | Project, portfolio, or goal the setting belongs to |
| `is_important` | `?bool` | Shown prominently in the project |
| `custom_field` | `?array` | The custom field record |

#### EnumOptionData Properties

| Property | Type | Description |
|----------|------|-------------|
| `gid` | `string` | Globally unique identifier |
| `resource_type` | `?string` | Always `"enum_option"` |
| `name` | `?string` | Option name |
| `enabled` | `?bool` | Whether the option can be selected |
| `color` | `?string` | Option color |
```

In the Projects section table, after the row that starts with ``| `getTaskCounts` |`` add:

```markdown
| `addCustomFieldSetting` | `string $gid`, `array $data`, `array $optFields = []` | `CustomFieldSettingData` | Add a custom field to a project (`custom_field`, `is_important`, `insert_before` / `insert_after`) |
| `removeCustomFieldSetting` | `string $gid`, `string $customFieldGid` | `void` | Remove a custom field from a project |
```

- [ ] **Step 10: Format**

Run: `composer format`
Expected: Pint reports the touched files as fixed or already clean; no errors.

- [ ] **Step 11: Run the full suite + analysis**

Run: `composer test && composer analyse`
Expected: all tests pass, `[OK] No errors`.

- [ ] **Step 12: Commit**

```bash
git add src/Requests/CustomFields/GetCustomFieldSettingsForProjectRequest.php src/Requests/CustomFields/GetCustomFieldSettingsForTeamRequest.php src/Requests/CustomFields/CreateEnumOptionRequest.php src/Requests/CustomFields/InsertEnumOptionRequest.php src/Requests/CustomFields/UpdateEnumOptionRequest.php src/Requests/Projects/AddCustomFieldSettingToProjectRequest.php src/Requests/Projects/RemoveCustomFieldSettingFromProjectRequest.php src/Data/CustomFieldSettingData.php src/Data/EnumOptionData.php src/Resources/CustomFieldResource.php src/Resources/ProjectResource.php tests/Unit/Data/CustomFieldSettingDataTest.php tests/Unit/Data/EnumOptionDataTest.php tests/Unit/Resources/CustomFieldResourceTest.php tests/Unit/Resources/ProjectResourceTest.php tests/ArchTest.php README.md
git commit -m "feat(custom-fields): custom field settings and enum option endpoints

Adds getSettingsForProject, getSettingsForTeam, createEnumOption,
insertEnumOption, updateEnumOption on CustomFieldResource and
addCustomFieldSetting / removeCustomFieldSetting on ProjectResource,
with CustomFieldSettingData and EnumOptionData DTOs.

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

### Task 21: Projects

**Files:**
- Create: `src/Requests/Projects/GetProjectsForTaskRequest.php`
- Create: `src/Requests/Projects/GetProjectsForWorkspaceRequest.php`
- Create: `src/Requests/Projects/CreateProjectForTeamRequest.php`
- Create: `src/Requests/Projects/CreateProjectForWorkspaceRequest.php`
- Create: `src/Requests/Projects/SearchProjectsRequest.php`
- Create: `src/Requests/Projects/AddMembersToProjectRequest.php`
- Create: `src/Requests/Projects/RemoveMembersFromProjectRequest.php`
- Create: `src/Requests/Projects/AddFollowersToProjectRequest.php`
- Create: `src/Requests/Projects/RemoveFollowersFromProjectRequest.php`
- Modify: `src/Resources/ProjectResource.php` (imports + 9 methods appended after `removeCustomFieldSetting()` from Task 20)
- Test (modify): `tests/Unit/Resources/ProjectResourceTest.php` (helper `createProjectResource` already exists — reuse it)
- Modify: `tests/ArchTest.php`
- Modify: `README.md` (Projects section ~line 236)

- [ ] **Step 1: Write the failing Resource tests**

Append to the end of `tests/Unit/Resources/ProjectResourceTest.php` (imports `MockClient`, `MockResponse`, `ProjectData`, `PaginatedResponse` already exist at the top of the file):

```php
test('getForTask returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Parent Project', 'resource_type' => 'project'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getForTask('task1', [], null, null, true);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(ProjectData::class)
        ->and($result->data[0]->name)->toBe('Parent Project')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/tasks/task1/projects'
            && $request->query()->all() === ['include_inherited_projects' => true];
    });
});

test('getForWorkspace returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Project 1', 'resource_type' => 'project'],
                ['gid' => '2', 'name' => 'Project 2', 'resource_type' => 'project'],
            ],
            'next_page' => ['offset' => 'tok', 'uri' => '/workspaces/ws1/projects?offset=tok'],
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getForWorkspace('ws1', ['name'], null, 2);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(ProjectData::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/workspaces/ws1/projects'
            && $request->query()->all() === ['opt_fields' => 'name', 'limit' => 2];
    });
});

test('getForWorkspace keeps archived=false in the query', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $resource->getForWorkspace('ws1', archived: false);

    $mockClient->assertSent(function ($request) {
        return $request->query()->all() === ['archived' => false];
    });
});

test('createForTeam returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1001',
            'name' => 'Team Project',
            'resource_type' => 'project',
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->createForTeam('team1', ['name' => 'Team Project']);

    expect($result)->toBeInstanceOf(ProjectData::class)
        ->and($result->gid)->toBe('1001')
        ->and($result->name)->toBe('Team Project');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/teams/team1/projects'
            && $request->body()->all() === ['data' => ['name' => 'Team Project']];
    });
});

test('createForWorkspace returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1002',
            'name' => 'Workspace Project',
            'resource_type' => 'project',
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->createForWorkspace('ws1', ['name' => 'Workspace Project']);

    expect($result)->toBeInstanceOf(ProjectData::class)
        ->and($result->gid)->toBe('1002');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/workspaces/ws1/projects'
            && $request->body()->all() === ['data' => ['name' => 'Workspace Project']];
    });
});

test('search returns PaginatedResponse and passes params through', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Sprint 1', 'resource_type' => 'project'],
            ],
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->search('ws1', ['text' => 'sprint', 'completed' => false, 'sort_by' => 'name'], ['name', 'owner']);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(ProjectData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/workspaces/ws1/projects/search'
            && $request->query()->all() === [
                'text' => 'sprint',
                'completed' => false,
                'sort_by' => 'name',
                'opt_fields' => 'name,owner',
            ];
    });
});

test('addMembers returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '789',
            'name' => 'Test Project',
            'resource_type' => 'project',
            'members' => [['gid' => 'u1'], ['gid' => 'u2']],
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->addMembers('789', ['u1', 'u2']);

    expect($result)->toBeInstanceOf(ProjectData::class)
        ->and($result->members)->toHaveCount(2);

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/projects/789/addMembers'
            && $request->body()->all() === ['data' => ['members' => 'u1,u2']];
    });
});

test('removeMembers returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '789',
            'name' => 'Test Project',
            'resource_type' => 'project',
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->removeMembers('789', ['u1']);

    expect($result)->toBeInstanceOf(ProjectData::class)
        ->and($result->gid)->toBe('789');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/projects/789/removeMembers'
            && $request->body()->all() === ['data' => ['members' => 'u1']];
    });
});

test('addFollowers returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '789',
            'name' => 'Test Project',
            'resource_type' => 'project',
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->addFollowers('789', ['u1', 'u2']);

    expect($result)->toBeInstanceOf(ProjectData::class);

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/projects/789/addFollowers'
            && $request->body()->all() === ['data' => ['followers' => 'u1,u2']];
    });
});

test('removeFollowers returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '789',
            'name' => 'Test Project',
            'resource_type' => 'project',
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->removeFollowers('789', ['u2']);

    expect($result)->toBeInstanceOf(ProjectData::class);

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/projects/789/removeFollowers'
            && $request->body()->all() === ['data' => ['followers' => 'u2']];
    });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `vendor/bin/pest tests/Unit/Resources/ProjectResourceTest.php`

Expected: FAIL — `Error: Call to undefined method WMBH\Asana\Resources\ProjectResource::getForTask()` and the same for `getForWorkspace`, `createForTeam`, `createForWorkspace`, `search`, `addMembers`, `removeMembers`, `addFollowers`, `removeFollowers`. Existing tests keep passing.

- [ ] **Step 3: Create the Request classes**

Create `src/Requests/Projects/GetProjectsForTaskRequest.php` (the `array_filter` callback keeps `false` for the bool param):

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectsForTaskRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
        protected readonly ?bool $includeInheritedProjects = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/projects";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'include_inherited_projects' => $this->includeInheritedProjects,
        ], fn ($value) => $value !== null);
    }
}
```

Create `src/Requests/Projects/GetProjectsForWorkspaceRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectsForWorkspaceRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
        protected readonly ?bool $archived = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/projects";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'archived' => $this->archived,
        ], fn ($value) => $value !== null);
    }
}
```

Create `src/Requests/Projects/CreateProjectForTeamRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateProjectForTeamRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $teamGid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/teams/{$this->teamGid}/projects";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/Projects/CreateProjectForWorkspaceRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateProjectForWorkspaceRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/projects";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
```

Create `src/Requests/Projects/SearchProjectsRequest.php` (search params such as `completed` and `sort_ascending` are booleans, so the callback form of `array_filter` is required):

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchProjectsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly array $params = [],
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/projects/search";
    }

    protected function defaultQuery(): array
    {
        return array_filter(array_merge($this->params, [
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]), fn ($value) => $value !== null);
    }
}
```

Create `src/Requests/Projects/AddMembersToProjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AddMembersToProjectRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $memberGids,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}/addMembers";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => ['members' => implode(',', $this->memberGids)]];
    }
}
```

Create `src/Requests/Projects/RemoveMembersFromProjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveMembersFromProjectRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $memberGids,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}/removeMembers";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => ['members' => implode(',', $this->memberGids)]];
    }
}
```

Create `src/Requests/Projects/AddFollowersToProjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AddFollowersToProjectRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $followerGids,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}/addFollowers";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => ['followers' => implode(',', $this->followerGids)]];
    }
}
```

Create `src/Requests/Projects/RemoveFollowersFromProjectRequest.php`:

```php
<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveFollowersFromProjectRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $followerGids,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}/removeFollowers";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => ['followers' => implode(',', $this->followerGids)]];
    }
}
```

- [ ] **Step 4: Add the Resource methods**

In `src/Resources/ProjectResource.php`, add these `use` lines (Pint sorts them in Step 8):

```php
use WMBH\Asana\Requests\Projects\AddFollowersToProjectRequest;
use WMBH\Asana\Requests\Projects\AddMembersToProjectRequest;
use WMBH\Asana\Requests\Projects\CreateProjectForTeamRequest;
use WMBH\Asana\Requests\Projects\CreateProjectForWorkspaceRequest;
use WMBH\Asana\Requests\Projects\GetProjectsForTaskRequest;
use WMBH\Asana\Requests\Projects\GetProjectsForWorkspaceRequest;
use WMBH\Asana\Requests\Projects\RemoveFollowersFromProjectRequest;
use WMBH\Asana\Requests\Projects\RemoveMembersFromProjectRequest;
use WMBH\Asana\Requests\Projects\SearchProjectsRequest;
```

and append these methods after `removeCustomFieldSetting()` inside the class:

```php
    public function getForTask(string $taskGid, array $optFields = [], ?string $offset = null, ?int $limit = null, ?bool $includeInheritedProjects = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetProjectsForTaskRequest($taskGid, $optFields, $offset, $limit, $includeInheritedProjects));

        return PaginatedResponse::fromResponse($response->json(), ProjectData::class);
    }

    public function getForWorkspace(string $workspaceGid, array $optFields = [], ?string $offset = null, ?int $limit = null, ?bool $archived = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetProjectsForWorkspaceRequest($workspaceGid, $optFields, $offset, $limit, $archived));

        return PaginatedResponse::fromResponse($response->json(), ProjectData::class);
    }

    public function createForTeam(string $teamGid, array $data, array $optFields = []): ProjectData
    {
        $response = $this->connector->send(new CreateProjectForTeamRequest($teamGid, $data, $optFields));

        return ProjectData::from($response->json('data'));
    }

    public function createForWorkspace(string $workspaceGid, array $data, array $optFields = []): ProjectData
    {
        $response = $this->connector->send(new CreateProjectForWorkspaceRequest($workspaceGid, $data, $optFields));

        return ProjectData::from($response->json('data'));
    }

    public function search(string $workspaceGid, array $params = [], array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new SearchProjectsRequest($workspaceGid, $params, $optFields));

        return PaginatedResponse::fromResponse($response->json(), ProjectData::class);
    }

    public function addMembers(string $gid, array $memberGids, array $optFields = []): ProjectData
    {
        $response = $this->connector->send(new AddMembersToProjectRequest($gid, $memberGids, $optFields));

        return ProjectData::from($response->json('data'));
    }

    public function removeMembers(string $gid, array $memberGids, array $optFields = []): ProjectData
    {
        $response = $this->connector->send(new RemoveMembersFromProjectRequest($gid, $memberGids, $optFields));

        return ProjectData::from($response->json('data'));
    }

    public function addFollowers(string $gid, array $followerGids, array $optFields = []): ProjectData
    {
        $response = $this->connector->send(new AddFollowersToProjectRequest($gid, $followerGids, $optFields));

        return ProjectData::from($response->json('data'));
    }

    public function removeFollowers(string $gid, array $followerGids, array $optFields = []): ProjectData
    {
        $response = $this->connector->send(new RemoveFollowersFromProjectRequest($gid, $followerGids, $optFields));

        return ProjectData::from($response->json('data'));
    }
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `vendor/bin/pest tests/Unit/Resources/ProjectResourceTest.php`
Expected: PASS — 22 tests in the file (8 original + 2 from Task 20 + 12 new), zero failures.

- [ ] **Step 6: Add ArchTest entries**

In `tests/ArchTest.php`, append after the `arch('RemoveCustomFieldSettingFromProjectRequest sends POST with JSON body')` block added in Task 20:

```php
arch('GetProjectsForWorkspaceRequest sends GET')
    ->expect('WMBH\Asana\Requests\Projects\GetProjectsForWorkspaceRequest')
    ->toSendGetRequest();

arch('SearchProjectsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Projects\SearchProjectsRequest')
    ->toSendGetRequest();

arch('CreateProjectForTeamRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Projects\CreateProjectForTeamRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('AddMembersToProjectRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Projects\AddMembersToProjectRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();
```

Run: `vendor/bin/pest tests/ArchTest.php`
Expected: PASS.

- [ ] **Step 7: Update README**

In `README.md`, Projects section table: after the row that starts with ``| `removeCustomFieldSetting` |`` (added in Task 20) add:

```markdown
| `getForTask` | `string $taskGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null`, `?bool $includeInheritedProjects = null` | `PaginatedResponse` | List projects a task belongs to |
| `getForWorkspace` | `string $workspaceGid`, `array $optFields = []`, `?string $offset = null`, `?int $limit = null`, `?bool $archived = null` | `PaginatedResponse` | List projects in a workspace (`/workspaces/{gid}/projects` route, supports `archived` filter) |
| `createForTeam` | `string $teamGid`, `array $data`, `array $optFields = []` | `ProjectData` | Create a project in a team |
| `createForWorkspace` | `string $workspaceGid`, `array $data`, `array $optFields = []` | `ProjectData` | Create a project in a workspace |
| `search` | `string $workspaceGid`, `array $params = []`, `array $optFields = []` | `PaginatedResponse` | Search projects in a workspace (Asana advanced search params) |
| `addMembers` | `string $gid`, `array $memberGids`, `array $optFields = []` | `ProjectData` | Add members to a project |
| `removeMembers` | `string $gid`, `array $memberGids`, `array $optFields = []` | `ProjectData` | Remove members from a project |
| `addFollowers` | `string $gid`, `array $followerGids`, `array $optFields = []` | `ProjectData` | Add followers to a project |
| `removeFollowers` | `string $gid`, `array $followerGids`, `array $optFields = []` | `ProjectData` | Remove followers from a project |
```

In the Projects section's ```php example block, after the line `$projects = Asana::projects()->getForTeam('team_gid');` (before the closing ```) add:

```php

// Projects a task belongs to (including inherited from parent tasks)
$projects = Asana::projects()->getForTask('task_gid', includeInheritedProjects: true);

// Active projects in a workspace
$projects = Asana::projects()->getForWorkspace('workspace_gid', archived: false);

// Create directly in a team / workspace
$project = Asana::projects()->createForTeam('team_gid', ['name' => 'Team Project']);
$project = Asana::projects()->createForWorkspace('workspace_gid', ['name' => 'Workspace Project']);

// Members and followers (returns the updated project)
$project = Asana::projects()->addMembers('project_gid', ['user_gid_1', 'user_gid_2']);
$project = Asana::projects()->removeMembers('project_gid', ['user_gid_1']);
$project = Asana::projects()->addFollowers('project_gid', ['user_gid_1']);
$project = Asana::projects()->removeFollowers('project_gid', ['user_gid_1']);
```

Then, directly after that ```php block's closing ``` and before `#### ProjectData Properties`, add a new sub-section:

```markdown
#### Project search

`search()` mirrors Asana's advanced project search. Pass the raw Asana params (`text`, `sort_by`, `sort_ascending`, `completed`, `teams.any`, `owner.any`, `members.any`, `members.not`, `portfolios.any`, `due_on.before`, `created_on.after`, …) — booleans are preserved. The response has no pagination cursor.

```php
$results = Asana::projects()->search('workspace_gid', [
    'text' => 'sprint',
    'completed' => false,
    'teams.any' => 'team_gid',
    'sort_by' => 'name',
    'sort_ascending' => true,
], ['name', 'owner', 'due_on']);

foreach ($results->data as $project) {
    echo $project->name;
}
```
```

- [ ] **Step 8: Format**

Run: `composer format`
Expected: Pint reports the touched files as fixed or already clean; no errors.

- [ ] **Step 9: Run the full suite + analysis**

Run: `composer test && composer analyse`
Expected: all tests pass, `[OK] No errors`.

- [ ] **Step 10: Commit**

```bash
git add src/Requests/Projects/GetProjectsForTaskRequest.php src/Requests/Projects/GetProjectsForWorkspaceRequest.php src/Requests/Projects/CreateProjectForTeamRequest.php src/Requests/Projects/CreateProjectForWorkspaceRequest.php src/Requests/Projects/SearchProjectsRequest.php src/Requests/Projects/AddMembersToProjectRequest.php src/Requests/Projects/RemoveMembersFromProjectRequest.php src/Requests/Projects/AddFollowersToProjectRequest.php src/Requests/Projects/RemoveFollowersFromProjectRequest.php src/Resources/ProjectResource.php tests/Unit/Resources/ProjectResourceTest.php tests/ArchTest.php README.md
git commit -m "feat(projects): task/team/workspace listings, search, members and followers

Adds getForTask, getForWorkspace, createForTeam, createForWorkspace,
search, addMembers, removeMembers, addFollowers, removeFollowers on
ProjectResource. Boolean query params (archived, completed,
sort_ascending) survive filtering.

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

---

---

### Task 22: README index, coverage tracker, CLAUDE.md, final gates, PR

**Files:**
- Modify: `README.md:65-83` (Table of Contents)
- Modify: `docs/asana-api-coverage.md` (regenerated)
- Modify: `CLAUDE.md`
- No new tests: this task only touches docs and runs the gates.

- [ ] **Step 1: Extend the README table of contents**

In `README.md`, the `### Table of Contents` list becomes:

```markdown
- [Tasks](#tasks)
- [Task Search (Query Builder)](#task-search-query-builder)
- [Task Templates](#task-templates)
- [Projects](#projects)
- [Project Templates](#project-templates)
- [Project Briefs](#project-briefs)
- [Sections](#sections)
- [Status Updates](#status-updates)
- [Users](#users)
- [User Task Lists](#user-task-lists)
- [Workspaces](#workspaces)
- [Teams](#teams)
- [Memberships](#memberships)
- [Access Requests](#access-requests)
- [Tags](#tags)
- [Stories (Comments)](#stories-comments)
- [Reactions](#reactions)
- [Attachments](#attachments)
- [Custom Fields](#custom-fields)
- [Custom Types](#custom-types)
- [Portfolios](#portfolios)
- [Goals](#goals)
- [Webhooks](#webhooks)
- [Events](#events)
- [Batch Requests](#batch-requests)
- [Jobs](#jobs)
- [Error Handling](#error-handling)
- [Pagination](#pagination)
```

Then check every anchor resolves: `grep -n "^### " README.md` must list a heading for each entry (Tasks 5–21 added the sections; a missing one means that task's README step was skipped — go back and do it).

- [ ] **Step 2: Regenerate the coverage tracker**

From the repo root run the generator (stdlib only, no PyYAML):

```bash
python3 - <<'PY'
import re, json, glob, os, urllib.request
from collections import defaultdict, Counter

SPEC_URL = "https://raw.githubusercontent.com/Asana/openapi/master/defs/asana_oas.yaml"
lines = urllib.request.urlopen(SPEC_URL).read().decode("utf-8").split("\n")
start = next(i for i, l in enumerate(lines) if l.startswith("paths:"))
end = next((i for i in range(start + 1, len(lines)) if lines[i] and not lines[i].startswith(" ")), len(lines))

ops = []; cur = None; path = None; i = start + 1
while i < end:
    l = lines[i]
    m = re.match(r"^  (/\S+):\s*$", l)
    if m: path = m.group(1); i += 1; continue
    m = re.match(r"^    (get|post|put|delete|patch):\s*$", l)
    if m:
        cur = {"method": m.group(1).upper(), "path": path, "op": None, "tags": []}
        ops.append(cur); i += 1; continue
    if cur is not None:
        m = re.match(r"^      operationId:\s*(\S+)", l)
        if m: cur["op"] = m.group(1)
        if re.match(r"^      tags:\s*$", l):
            j = i + 1
            while j < end and re.match(r"^        - ", lines[j]):
                cur["tags"].append(lines[j].strip()[2:]); j += 1
    i += 1

def norm(p): return re.sub(r"\{[^}]+\}", "{x}", p.rstrip("/"))
have = {}
for f in sorted(glob.glob("src/Requests/**/*.php", recursive=True)):
    s = open(f).read()
    m = re.search(r"Method::(\w+)", s)
    e = re.search(r"resolveEndpoint\(\): string\s*\{\s*return\s+(.+?);", s, re.S)
    ep = re.sub(r"\{\$this->\w+\}", "{x}", e.group(1).strip().strip("'\""))
    have[(m.group(1), norm(ep))] = os.path.relpath(f, "src/Requests")
if ("GET", "/users/me") in have:
    have.setdefault(("GET", "/users/{x}"), have[("GET", "/users/me")] + " (me)")

LIST1 = {"AI Studio usage API": "Enterprise (service account)", "Agents": "AI Teammates add-on",
         "Allocations": "Advanced/Enterprise", "Audit log API": "Enterprise",
         "Budgets": "Timesheets & Budgets add-on", "Goal relationships": "Advanced", "Goals": "Advanced",
         "Organization exports": "Enterprise", "Exports": "Enterprise", "Ooo entries": "unverified (likely free)",
         "Portfolio memberships": "Advanced", "Portfolios": "Advanced", "Project portfolio settings": "Advanced",
         "Rates": "Timesheets & Budgets add-on", "Roles": "Enterprise+", "Rules": "Advanced",
         "Time periods": "Advanced", "Time tracking categories": "Advanced", "Time tracking entries": "Advanced",
         "Timesheet approval statuses": "Timesheets & Budgets add-on"}
def tier(r):
    tag = r["tags"][0] if r["tags"] else "?"
    if tag == "Project statuses": return "deprecated"
    if tag == "Stories" and "/goals/" in r["path"]: return "Advanced"
    if tag == "Custom field settings" and ("/portfolios/" in r["path"] or "/goals/" in r["path"]): return "Advanced"
    return LIST1.get(tag, "free/Starter")

rows = []; bytag = defaultdict(list)
for r in ops:
    cls = have.get((r["method"], norm(r["path"])))
    if cls: status = "covered"
    else:
        t = tier(r)
        status = "skip (deprecated)" if t == "deprecated" else ("MISSING — List 2" if t == "free/Starter" else f"MISSING — List 1 ({t})")
    row = {"method": r["method"], "path": r["path"], "op": r["op"], "tag": r["tags"][0] if r["tags"] else "?", "status": status, "cls": cls or ""}
    rows.append(row); bytag[row["tag"]].append(row)

c = Counter("covered" if x["status"] == "covered" else "list2" if "List 2" in x["status"] else "list1" if "List 1" in x["status"] else "deprecated" for x in rows)
import datetime
out = ["# Asana API coverage tracker", "",
       f"Generated {datetime.date.today().isoformat()} from Asana's OpenAPI spec (`{SPEC_URL}`) diffed against `src/Requests/**`. Regenerate with the script in `docs/superpowers/plans/2026-09-11-api-coverage-upgrade.md` (Task 22) whenever endpoints are added: it matches on HTTP method + path with `{gid}` segments normalized.", "",
       "| | Endpoints |", "|---|---|", f"| Total in spec | {len(rows)} |", f"| Covered by package | {c['covered']} |",
       f"| Missing, free/Starter tier (List 2) | {c['list2']} |", f"| Missing, Advanced/Enterprise/add-on (List 1) | {c['list1']} |",
       f"| Deprecated by Asana, skipped | {c['deprecated']} |", "",
       "Status legend: **covered** = a Request class exists (path shown). **MISSING — List 2** = free/Starter, planned. **MISSING — List 1** = paid tier in parentheses, awaiting owner review.", ""]
for tag in sorted(bytag):
    out += [f"## {tag}", "", "| Method | Path | operationId | Status | Package request |", "|---|---|---|---|---|"]
    out += [f"| {x['method']} | `{x['path']}` | {x['op']} | {x['status']} | {('`' + x['cls'] + '`') if x['cls'] else ''} |" for x in bytag[tag]]
    out.append("")
open("docs/asana-api-coverage.md", "w").write("\n".join(out))
print(dict(c))
PY
```

Expected output: `{'covered': 166, 'list1': 79, 'deprecated': 4}` — no `list2` key at all. If `list2` is present, the listed rows name the endpoints still missing; finish their task before continuing.

- [ ] **Step 3: Update `CLAUDE.md`**

In `CLAUDE.md`:
- In the `src/AsanaConnector.php` bullet, delete the sentence starting `**Gotcha:** the \`retryAttempts\`/\`retrySleep\`` through `no retries actually happen today.` and replace it with: `Retries are intentionally not implemented (retrying would require \`AsanaException\` to extend Saloon's \`RequestException\`).`
- In the `src/Asana.php` bullet, replace `(\`tasks()\`, \`projects()\`, \`sections()\`, ..., \`batch()\`)` with `(25 accessors: \`tasks()\`, \`projects()\`, ..., \`taskTemplates()\`, \`events()\`, \`jobs()\`, \`batch()\`)`.
- In the `src/Data/*Data.php` bullet, after the `PaginatedResponse` description add: `, and \`EventsResponse\` (\`data\`, \`sync\`, \`hasMore\`) for the events endpoints — the first call without a sync token yields HTTP 412, which \`EventResource\` turns into an empty \`EventsResponse\` carrying the fresh token`.
- Under `## Architecture`, add a final bullet: `- \`docs/asana-api-coverage.md\` — every Asana endpoint with covered/missing status and paid-tier note; regenerate (script in the 2026-09-11 plan, Task 22) after adding endpoints.`

`CLAUDE.md` is gitignored (owner keeps it local); do not `git add` it.

- [ ] **Step 4: Run every gate and record the real output**

Run each and paste the result line into the PR description:

```bash
composer format            # Expected: no files changed (already formatted per task)
composer test              # Expected: Tests: N passed, 0 failed (N ≥ 400)
composer analyse           # Expected: [OK] No errors
composer audit             # Expected: No security vulnerability advisories found.
git status --short         # Expected: only docs/asana-api-coverage.md and README.md modified
```

If `composer analyse` reports new errors, fix the code — do not add to `phpstan-baseline.neon`.

- [ ] **Step 5: Commit the docs**

```bash
git add README.md docs/asana-api-coverage.md
git commit -m "docs: index new resources, regenerate API coverage tracker

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>
Claude-Session: https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue"
```

- [ ] **Step 6: Push and open the PR (confirm with the owner before pushing)**

```bash
git push -u origin feat/api-coverage-2026-09
gh pr create --title "Add 76 free-tier Asana endpoints, fix mis-pathed requests, patch deps" --body "$(cat <<'BODY'
## Summary
- Adds every non-deprecated free/Starter-tier Asana endpoint the package was missing (76), incl. task templates / `instantiateTask`, project templates, status updates, project briefs, memberships, events, jobs, custom types, user task lists, access requests, reactions, plus new methods on Tasks, Projects, Custom Fields, Teams, Workspaces, Users, Tags and Attachments. Per-endpoint status: `docs/asana-api-coverage.md`.
- Fixes 4 requests that hit paths not in Asana's spec (`getSubgoals`/`addSubgoal` → goal relationships, teams → `/workspaces/{gid}/teams`, attachments → `GET /attachments?parent=`).
- Removes the `asana.retry.*` config and connector args, which were never applied.
- Raises `guzzlehttp/guzzle` to `^7.15.2 || ^8.0`, `saloonphp/saloon` to `^4.0.1`, allows Lawman 5; `composer audit` now runs in CI and passes.

## Release notes (v1.1.0)
### Added
- 76 endpoints, 11 new resources (`taskTemplates()`, `projectTemplates()`, `jobs()`, `statusUpdates()`, `projectBriefs()`, `memberships()`, `events()`, `customTypes()`, `userTaskLists()`, `accessRequests()`, `reactions()`), 16 new DTOs, `EventsResponse`.
- `AttachmentResource::getForObject()`, `ProjectResource::search()`, `TaskResource::getByCustomId()`, and friends (see README).
### Fixed
- `GoalResource::getSubgoals()` / `addSubgoal()` now call real endpoints; `getSubgoals()` items are `CompactResource`.
- `TeamResource::getForWorkspace()` uses the documented `/workspaces/{gid}/teams` path.
- `AttachmentResource::getForTask()` uses `GET /attachments?parent=`.
### Removed
- `asana.retry.attempts` / `asana.retry.sleep` config and the `retryAttempts` / `retrySleep` arguments of `AsanaConnector` (never functional).
- `WMBH\Asana\Requests\Attachments\GetAttachmentsForTaskRequest` (replaced by `GetAttachmentsForObjectRequest`).
### Changed
- Minimum `guzzlehttp/guzzle` is now `7.15.2` (security fixes); `saloonphp/saloon` `4.0.1`.

## Test plan
- [ ] `composer test` green locally (paste count)
- [ ] `composer analyse` green
- [ ] `composer audit` green
- [ ] CI matrix green (PHP 8.3/8.4 × Laravel 11/12/13 × lowest/stable × Ubuntu/Windows)

🤖 Generated with [Claude Code](https://claude.com/claude-code)

https://claude.ai/code/session_01TJzgBzRwWEiHM662dbj6Ue
BODY
)"
```

Do not tag a release from the branch; the owner tags `v1.1.0` after merge (the CHANGELOG is regenerated from the release notes by CI).
