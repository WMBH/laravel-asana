# Asana API coverage + housekeeping — design

Date: 2026-09-11. Package: `wmbh/laravel-asana` (currently v1.0.1).

## Goals

1. Add every non-deprecated Asana endpoint that works on the free/Starter tier and is missing today: **76 endpoints** ("List 2", tracked per endpoint in `docs/asana-api-coverage.md`), including `instantiateTask`. Two more are covered today only through undocumented legacy paths and are rewritten (see Path fixes).
2. Fix 4 request classes that hit paths absent from Asana's OpenAPI spec.
3. Remove the retry configuration, which has never done anything.
4. Upgrade dependencies so `composer audit` passes, and enforce audit in CI.

## Non-goals

- The 79 Advanced/Enterprise/add-on endpoints ("List 1", appendix A). Owner reviews the list and picks; they get their own plan.
- Making retries work. Owner chose removal over the breaking change (`AsanaException` would have to extend Saloon's `RequestException` to be retried).
- Pest 5 / Lawman-on-Pest-5. Pest 5 needs PHP ^8.4 and PHPUnit ^13; the CI matrix keeps PHP 8.3 and Laravel 11 (testbench 9, PHPUnit 10/11).
- The 4 deprecated `project_statuses` endpoints (Asana: "prefer `/status_updates`").

## Source of truth

Endpoint list, params, bodies and response fields come from `https://raw.githubusercontent.com/Asana/openapi/master/defs/asana_oas.yaml` (249 operations; 90 already covered, 2 of them via legacy paths). Tier split from `asana.com/pricing` plus per-endpoint docs.

## Conventions (unchanged, every new class follows them)

- Request: `src/Requests/{Domain}/{Verb}{Thing}Request.php`, `protected Method $method`, `resolveEndpoint()`, `defaultQuery()` via `array_filter([...])` with `opt_fields` imploded by comma, write requests `implements HasBody` + `use HasJsonBody`, body `['data' => ...]`.
- Resource method: single → `XData::from($response->json('data'))`; list → `PaginatedResponse::fromResponse($response->json(), XData::class)`; delete → `$response->status() === 200`; association → `void`; async → `JobData`.
- DTO: `extends Spatie\LaravelData\Data`, snake_case Asana field names, nullable except `gid`, nested single references typed `?CompactResource`, nested lists `?array`.
- Tests: one `createXResource(MockClient)` helper per Resource test file (global function, unique name), Saloon `MockClient`/`MockResponse`, one test per method plus one per guard/error branch; one `XDataTest.php` per DTO (from array, null optionals, nested CompactResource cast). ArchTest: `toSendGetRequest()` etc. for representative new requests.
- README: API reference table per Resource + "XData Properties" table per DTO.

## List 2 — new public surface

Accessor on `Asana` → Resource → methods (← Request class). All list methods paginate unless the endpoint has no `next_page`.

### New resources

**`taskTemplates()` → `TaskTemplateResource`** (`Requests/TaskTemplates/`)
- `list(string $projectGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetTaskTemplatesRequest` GET `/task_templates?project=`
- `get(string $gid, array $optFields = []): TaskTemplateData` ← `GetTaskTemplateRequest`
- `delete(string $gid): bool` ← `DeleteTaskTemplateRequest`
- `instantiate(string $gid, ?string $name = null, array $optFields = []): JobData` ← `InstantiateTaskRequest` POST `/task_templates/{gid}/instantiateTask`, body `{data: {name?}}`

**`projectTemplates()` → `ProjectTemplateResource`** (`Requests/ProjectTemplates/`)
- `list(?string $workspaceGid = null, ?string $teamGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetProjectTemplatesRequest`
- `getForTeam(string $teamGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetProjectTemplatesForTeamRequest`
- `get(string $gid, array $optFields = []): ProjectTemplateData` ← `GetProjectTemplateRequest`
- `delete(string $gid): bool` ← `DeleteProjectTemplateRequest`
- `instantiate(string $gid, array $data, array $optFields = []): JobData` ← `InstantiateProjectRequest` (body keys: name, team, public, privacy_setting, is_strict, requested_dates, requested_roles)

**`jobs()` → `JobResource`** (`Requests/Jobs/`)
- `get(string $gid, array $optFields = []): JobData` ← `GetJobRequest`

**`statusUpdates()` → `StatusUpdateResource`** (`Requests/StatusUpdates/`)
- `get(string $gid, array $optFields = []): StatusUpdateData` ← `GetStatusUpdateRequest`
- `getForObject(string $parentGid, array $optFields = [], ?string $offset = null, ?int $limit = null, ?string $createdSince = null): PaginatedResponse` ← `GetStatusUpdatesForObjectRequest` GET `/status_updates?parent=`
- `create(string $parentGid, array $data, array $optFields = []): StatusUpdateData` ← `CreateStatusUpdateRequest` POST `/status_updates`, body merges `parent`
- `delete(string $gid): bool` ← `DeleteStatusUpdateRequest`

**`projectBriefs()` → `ProjectBriefResource`** (`Requests/ProjectBriefs/`)
- `get(string $gid, array $optFields = []): ProjectBriefData` ← `GetProjectBriefRequest`
- `create(string $projectGid, array $data, array $optFields = []): ProjectBriefData` ← `CreateProjectBriefRequest` POST `/projects/{gid}/project_briefs`
- `update(string $gid, array $data, array $optFields = []): ProjectBriefData` ← `UpdateProjectBriefRequest`
- `delete(string $gid): bool` ← `DeleteProjectBriefRequest`

**`memberships()` → `MembershipResource`** (`Requests/Memberships/`) — generic memberships for project/portfolio/goal/custom_field/custom_type parents
- `list(?string $parentGid = null, ?string $memberGid = null, ?string $resourceSubtype = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetMembershipsRequest`
- `get(string $gid): MembershipData` ← `GetMembershipRequest`
- `create(array $data): MembershipData` ← `CreateMembershipRequest` (keys: parent, member, access_level, role)
- `update(string $gid, array $data): MembershipData` ← `UpdateMembershipRequest`
- `delete(string $gid): bool` ← `DeleteMembershipRequest`

**`events()` → `EventResource`** (`Requests/Events/`)
- `get(string $resourceGid, ?string $sync = null, array $optFields = []): EventsResponse` ← `GetEventsRequest` GET `/events?resource=&sync=`
- `getForWorkspace(string $workspaceGid, ?string $sync = null): EventsResponse` ← `GetWorkspaceEventsRequest` GET `/workspaces/{gid}/events?sync=`
- Asana answers the first call (no `sync`) with HTTP 412 and a fresh `sync` token in the body. Both methods catch `AsanaException` with code 412 and return `new EventsResponse(data: [], sync: $e->getResponseBody()['sync'] ?? null, hasMore: false)`. Any other status rethrows.
- `Data/Shared/EventsResponse` (plain class like `PaginatedResponse`): `array $data` of `EventData`, `?string $sync`, `bool $hasMore`; `static fromResponse(array $response): self`.

**`customTypes()` → `CustomTypeResource`** (`Requests/CustomTypes/`)
- `list(?string $projectGid = null, ?string $workspaceGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetCustomTypesRequest`
- `get(string $gid, array $optFields = []): CustomTypeData` ← `GetCustomTypeRequest`

**`userTaskLists()` → `UserTaskListResource`** (`Requests/UserTaskLists/`)
- `get(string $gid, array $optFields = []): UserTaskListData` ← `GetUserTaskListRequest`
- `getForUser(string $userGid, string $workspaceGid, array $optFields = []): UserTaskListData` ← `GetUserTaskListForUserRequest`

**`accessRequests()` → `AccessRequestResource`** (`Requests/AccessRequests/`)
- `list(string $targetGid, ?string $userGid = null, array $optFields = []): PaginatedResponse` ← `GetAccessRequestsRequest`
- `create(string $targetGid, ?string $message = null): AccessRequestData` ← `CreateAccessRequestRequest`
- `approve(string $gid): bool` ← `ApproveAccessRequestRequest` POST `/access_requests/{gid}/approve`
- `reject(string $gid): bool` ← `RejectAccessRequestRequest`

**`reactions()` → `ReactionResource`** (`Requests/Reactions/`)
- `getForObject(string $targetGid, string $emojiBase, ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetReactionsForObjectRequest` GET `/reactions?target=&emoji_base=`

### Methods added to existing resources

**`TaskResource`** (`Requests/Tasks/`)
- `list(array $params = [], array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetTasksRequest` GET `/tasks` (params: assignee, project, section, workspace, completed_since, modified_since, custom_type)
- `duplicate(string $gid, array $data, array $optFields = []): JobData` ← `DuplicateTaskRequest` (keys: name, include)
- `getForTag(string $tagGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetTasksForTagRequest`
- `getForUserTaskList(string $userTaskListGid, array $optFields = [], ?string $offset = null, ?int $limit = null, ?string $completedSince = null): PaginatedResponse` ← `GetTasksForUserTaskListRequest`
- `createSubtask(string $taskGid, array $data, array $optFields = []): TaskData` ← `CreateSubtaskRequest` POST `/tasks/{gid}/subtasks`
- `removeDependencies(string $taskGid, array $dependencyGids): void` ← `RemoveDependenciesRequest`
- `removeDependents(string $taskGid, array $dependentGids): void` ← `RemoveDependentsRequest`
- `removeFollowers(string $taskGid, array $followers): void` ← `RemoveFollowersRequest`
- `getByCustomId(string $workspaceGid, string $customId): TaskData` ← `GetTaskByCustomIdRequest` GET `/workspaces/{gid}/tasks/custom_id/{id}`

**`ProjectResource`** (`Requests/Projects/`)
- `getForTask(string $taskGid, array $optFields = [], ?string $offset = null, ?int $limit = null, ?bool $includeInheritedProjects = null): PaginatedResponse` ← `GetProjectsForTaskRequest`
- `getForWorkspace(string $workspaceGid, array $optFields = [], ?string $offset = null, ?int $limit = null, ?bool $archived = null): PaginatedResponse` ← `GetProjectsForWorkspaceRequest` GET `/workspaces/{gid}/projects`
- `createForTeam(string $teamGid, array $data, array $optFields = []): ProjectData` ← `CreateProjectForTeamRequest`
- `createForWorkspace(string $workspaceGid, array $data, array $optFields = []): ProjectData` ← `CreateProjectForWorkspaceRequest`
- `search(string $workspaceGid, array $params = [], array $optFields = []): PaginatedResponse` ← `SearchProjectsRequest` GET `/workspaces/{gid}/projects/search` (params passed through: text, sort_by, teams.any, …)
- `addMembers(string $gid, array $memberGids, array $optFields = []): ProjectData` ← `AddMembersToProjectRequest` body `members` comma-joined
- `removeMembers(string $gid, array $memberGids, array $optFields = []): ProjectData` ← `RemoveMembersFromProjectRequest`
- `addFollowers(string $gid, array $followerGids, array $optFields = []): ProjectData` ← `AddFollowersToProjectRequest`
- `removeFollowers(string $gid, array $followerGids, array $optFields = []): ProjectData` ← `RemoveFollowersFromProjectRequest`
- `saveAsTemplate(string $gid, array $data, array $optFields = []): JobData` ← `SaveProjectAsTemplateRequest` (keys: name, team|workspace, public)
- `addCustomFieldSetting(string $gid, array $data, array $optFields = []): CustomFieldSettingData` ← `AddCustomFieldSettingToProjectRequest` (keys: custom_field, is_important, insert_before, insert_after)
- `removeCustomFieldSetting(string $gid, string $customFieldGid): void` ← `RemoveCustomFieldSettingFromProjectRequest`
- `getMemberships(string $gid, ?string $userGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetProjectMembershipsRequest` GET `/projects/{gid}/project_memberships`
- `getMembership(string $membershipGid, array $optFields = []): ProjectMembershipData` ← `GetProjectMembershipRequest` GET `/project_memberships/{gid}`

**`CustomFieldResource`** (`Requests/CustomFields/`)
- `getSettingsForProject(string $projectGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetCustomFieldSettingsForProjectRequest`
- `getSettingsForTeam(string $teamGid, array $optFields = []): PaginatedResponse` ← `GetCustomFieldSettingsForTeamRequest`
- `createEnumOption(string $customFieldGid, array $data, array $optFields = []): EnumOptionData` ← `CreateEnumOptionRequest` (keys: name, enabled, color, insert_before, insert_after)
- `insertEnumOption(string $customFieldGid, array $data, array $optFields = []): EnumOptionData` ← `InsertEnumOptionRequest` POST `/custom_fields/{gid}/enum_options/insert` (keys: enum_option, before_enum_option, after_enum_option)
- `updateEnumOption(string $enumOptionGid, array $data, array $optFields = []): EnumOptionData` ← `UpdateEnumOptionRequest` PUT `/enum_options/{gid}`

**`TeamResource`** (`Requests/Teams/`)
- `update(string $gid, array $data, array $optFields = []): TeamData` ← `UpdateTeamRequest`
- `getMemberships(?string $teamGid = null, ?string $userGid = null, ?string $workspaceGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetTeamMembershipsRequest` GET `/team_memberships`
- `getMembershipsForTeam(string $teamGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetTeamMembershipsForTeamRequest`
- `getMembership(string $membershipGid, array $optFields = []): TeamMembershipData` ← `GetTeamMembershipRequest`

**`WorkspaceResource`** (`Requests/Workspaces/`)
- `getMemberships(string $workspaceGid, ?string $userGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetWorkspaceMembershipsRequest`
- `getMembership(string $membershipGid, array $optFields = []): WorkspaceMembershipData` ← `GetWorkspaceMembershipRequest`
- `typeahead(string $workspaceGid, string $resourceType, ?string $query = null, ?int $count = null, array $optFields = []): PaginatedResponse` (items `CompactResource`) ← `TypeaheadRequest` GET `/workspaces/{gid}/typeahead?resource_type=`

**`UserResource`** (`Requests/Users/` unless noted)
- `update(string $gid, array $data, ?string $workspaceGid = null, array $optFields = []): UserData` ← `UpdateUserRequest` PUT `/users/{gid}?workspace=`
- `getFavorites(string $userGid, string $resourceType, string $workspaceGid, ?string $offset = null, ?int $limit = null, array $optFields = []): PaginatedResponse` (items `CompactResource`) ← `GetFavoritesForUserRequest`
- `getInWorkspace(string $workspaceGid, string $userGid, array $optFields = []): UserData` ← `GetUserForWorkspaceRequest` (name avoids clash with existing `getForWorkspace()` which lists users)
- `updateInWorkspace(string $workspaceGid, string $userGid, array $data, array $optFields = []): UserData` ← `UpdateUserForWorkspaceRequest`
- `getTeamMemberships(string $userGid, string $workspaceGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `Requests/Teams/GetTeamMembershipsForUserRequest`
- `getWorkspaceMemberships(string $userGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `Requests/Workspaces/GetWorkspaceMembershipsForUserRequest`

**`TagResource`**: `list(?string $workspaceGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetTagsRequest` GET `/tags?workspace=`

**`AttachmentResource`**: `getForObject(string $parentGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse` ← `GetAttachmentsForObjectRequest` GET `/attachments?parent=`. Existing `getForTask()` delegates to `getForObject()`. `GetAttachmentsForTaskRequest` is deleted.

### New DTOs (`src/Data/`)

Field lists from the spec's `*Response` schemas. `CR` = `?CompactResource`.

- `JobData`: gid, resource_type, resource_subtype, status, new_project CR, new_task CR, new_portfolio CR, new_project_template CR, new_graph_export ?array, new_resource_export ?array
- `TaskTemplateData`: gid, resource_type, name, project CR, template ?array, created_by CR, created_at
- `ProjectTemplateData`: gid, resource_type, name, description, html_description, public ?bool, owner CR, team CR, requested_dates ?array, requested_roles ?array, color
- `StatusUpdateData`: gid, resource_type, resource_subtype, title, text, html_text, status_type, author CR, created_at, created_by CR, modified_at, hearted ?bool, hearts ?array, liked ?bool, likes ?array, reaction_summary ?array, num_hearts ?int, num_likes ?int, parent CR
- `ProjectBriefData`: gid, resource_type, title, html_text, text, permalink_url, project CR
- `MembershipData` (union of goal/project/portfolio/custom_field/custom_type membership fields): gid, resource_type, resource_subtype, parent CR, member CR, access_level, role, user CR, goal CR, workspace CR, project CR, write_access
- `ProjectMembershipData`: gid, resource_type, resource_subtype, parent CR, member CR, access_level, user CR, project CR, write_access
- `TeamMembershipData`: gid, resource_type, user CR, team CR, is_guest ?bool, is_limited_access ?bool, is_admin ?bool
- `WorkspaceMembershipData`: gid, resource_type, user CR, workspace CR, user_task_list CR, is_active ?bool, is_admin ?bool, is_guest ?bool, is_view_only ?bool, vacation_dates ?array, created_at
- `EventData` (no gid in Asana's schema; all fields nullable, `gid` omitted): user CR, resource CR, type, action, parent CR, created_at, change ?array
- `CustomTypeData`: gid, resource_type, name, asana_created_type_identifier, status_options ?array
- `UserTaskListData`: gid, resource_type, name, owner CR, workspace CR
- `AccessRequestData`: gid, resource_type, message, approval_status, requester CR, target CR
- `ReactionData`: gid, emoji, user CR
- `CustomFieldSettingData`: gid, resource_type, project CR, parent CR, is_important ?bool, custom_field ?array
- `EnumOptionData`: gid, resource_type, name, enabled ?bool, color

`Data/Shared/EventsResponse` as described under `events()`.

## Path fixes (behavior-preserving method names)

| Class | Today | Fix |
|---|---|---|
| `Requests/Goals/GetSubgoalsRequest` | `GET /goals/{gid}/subgoals` (404) | `GET /goal_relationships?supported_goal={gid}&resource_subtype=subgoal`; `GoalResource::getSubgoals()` maps each relationship's `supporting_resource` to `CompactResource` and builds `PaginatedResponse` manually |
| `Requests/Goals/AddSubgoalRequest` | `POST /goals/{gid}/addSubgoal` (404) | `POST /goals/{gid}/addSupportingRelationship`, body `{data: {supporting_resource: subgoalGid}}` |
| `Requests/Teams/GetTeamsForWorkspaceRequest` | `GET /organizations/{gid}/teams` (undocumented) | `GET /workspaces/{gid}/teams` |
| `Requests/Attachments/GetAttachmentsForTaskRequest` | `GET /tasks/{gid}/attachments` (not in spec) | deleted; `AttachmentResource::getForTask()` calls `getForObject($taskGid)` |

`GoalResource::getSubgoals()` return items change from (never-populated) `GoalData` to `CompactResource`. Documented in CHANGELOG.

## Retry removal

- `AsanaConnector::__construct(string $token, int $timeout = 30)`; drop `$retryAttempts`, `$retrySleep`.
- `AsanaServiceProvider`: stop passing `retryAttempts`/`retrySleep`.
- `config/asana.php`: drop the `retry` block. `tests/TestCase.php`: drop the two `asana.retry.*` sets.
- README config block: drop the `retry` block.
- CHANGELOG "Removed": the `asana.retry.*` config and constructor arguments; they were never applied to requests.

## Dependencies + audit + CI

`composer.json`:
- `guzzlehttp/guzzle`: `^7.6` → `^7.15.2 || ^8.0` (fixes 9 advisories; Saloon 4.0.1 allows Guzzle 8; Laravel 13 still pins 7.x so local installs stay on 7.15.x)
- `saloonphp/saloon`: `^4.0` → `^4.0.1`
- `jonpurvis/lawman`: `^4.2` → `^4.2 || ^5.0`
- everything else unchanged; `composer update` picks up minors (pint, larastan, testbench 11.2, collision, phpstan plugins, package-tools).

`guzzlehttp/psr7` (2 advisories) and `league/commonmark` (10 advisories, dev-only via `laravel/framework`) are transitive; `composer update` resolves them to patched versions. `composer.lock` is gitignored, so CI resolves fresh.

CI: `.github/workflows/phpstan.yml` gains a `composer audit` step after install (prefer-stable). The `run-tests` matrix's `prefer-lowest` legs do not audit.

## Versioning

v1.1.0. Additive API, plus: removal of never-functional retry config/ctor args, `GetAttachmentsForTaskRequest` class removed, `getSubgoals()` item type corrected. Recorded under "Removed"/"Fixed" in CHANGELOG; the CHANGELOG file itself is regenerated from release notes by CI, so the text is authored in the GitHub release.

## Testing

- Every new Request has a Resource test (mocked) asserting DTO type + key fields; list methods also assert pagination; void methods assert `assertSent`; `delete` asserts `true`.
- `EventResource` tests: normal 200 → `EventsResponse` with data/sync/hasMore; 412 → empty data with sync token; 500 → rethrows `AsanaException`.
- `GoalResource::getSubgoals` test: relationship payload → `CompactResource` items; `addSubgoal` asserts endpoint `/addSupportingRelationship` and body `supporting_resource`.
- `TeamResource::getForWorkspace` test asserts the `/workspaces/` path; `AttachmentResource::getForTask` asserts `/attachments?parent=`.
- Every new DTO has a `Data` test: from array, null optionals, nested `CompactResource` cast.
- `AsanaTest`: new accessors return the right Resource and are memoized.
- ArchTest: one `toSendGetRequest`/`toSendPostRequest()->toHaveJsonBody()`/`toSendPutRequest`/`toSendDeleteRequest` entry per new domain folder.
- Gates: `composer test`, `composer analyse`, `composer format --test`, `composer audit` all green locally before the branch is done.

## Appendix A — List 1 (deferred, owner review)

Counts are endpoints. Tier from asana.com/pricing unless noted.

- **Advanced**: Goals extras (setMetric, add/removeFollowers, parentGoals, add/removeCustomFieldSetting) 6; Goal relationships 4; Goal stories 2; Time periods 2; Goal custom field settings 1 → 15. Portfolios extras (add/removeCustomFieldSetting, add/removeMembers, duplicate) 5; Portfolio memberships 3; Project portfolio settings 4; Portfolio custom field settings 1 → 13. Time tracking categories 6 + entries 6 → 12. Allocations 5 (Workload Advanced; capacity planning Enterprise). Rule trigger via incoming web request 1 (Advanced per Asana's incoming-web-request docs; unconfirmed on pricing page).
- **Timesheets & Budgets add-on**: Budgets 5, Rates 5, Timesheet approval statuses 4 → 14.
- **Enterprise**: Audit log 1; Organization exports 2 + graph/resource exports 2 → 4; AI Studio usage 2 (service account).
- **Enterprise+**: Roles (RBAC) 5.
- **AI Teammates add-on**: Agents 2.
- **Tier unverified**: OOO entries 5 (docs silent; likely free).

Total 79.
