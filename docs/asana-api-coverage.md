# Asana API coverage tracker

Generated 2026-09-11 from Asana's OpenAPI spec (`https://raw.githubusercontent.com/Asana/openapi/master/defs/asana_oas.yaml`) diffed against `src/Requests/**`. Regenerate when endpoints are added: match on HTTP method + path with `{gid}` segments normalized.

| | Endpoints |
|---|---|
| Total in spec | 249 |
| Covered by package | 90 |
| Missing, free/Starter tier (List 2) | 76 |
| Missing, Advanced/Enterprise/add-on (List 1) | 79 |
| Deprecated by Asana, skipped | 4 |

Status legend: **covered** = a Request class exists (path shown). **MISSING — List 2** = free/Starter, planned. **MISSING — List 1** = paid tier in parentheses, awaiting owner review. Legacy-path rows are covered but hit an undocumented path (fixed in the 2026-09 plan).

## AI Studio usage API

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/workspaces/{workspace_gid}/ai_studio/runs` | getAiStudioRuns | MISSING — List 1 (Enterprise (service account)) |  |
| GET | `/workspaces/{workspace_gid}/ai_studio/seats` | getAiStudioSeats | MISSING — List 1 (Enterprise (service account)) |  |

## Access requests

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/access_requests` | getAccessRequests | MISSING — List 2 |  |
| POST | `/access_requests` | createAccessRequest | MISSING — List 2 |  |
| POST | `/access_requests/{access_request_gid}/approve` | approveAccessRequest | MISSING — List 2 |  |
| POST | `/access_requests/{access_request_gid}/reject` | rejectAccessRequest | MISSING — List 2 |  |

## Agents

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/workspaces/{workspace_gid}/agents` | getAgentsForWorkspace | MISSING — List 1 (AI Teammates add-on) |  |
| GET | `/agents/{agent_gid}` | getAgent | MISSING — List 1 (AI Teammates add-on) |  |

## Allocations

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/allocations/{allocation_gid}` | getAllocation | MISSING — List 1 (Advanced/Enterprise) |  |
| PUT | `/allocations/{allocation_gid}` | updateAllocation | MISSING — List 1 (Advanced/Enterprise) |  |
| DELETE | `/allocations/{allocation_gid}` | deleteAllocation | MISSING — List 1 (Advanced/Enterprise) |  |
| GET | `/allocations` | getAllocations | MISSING — List 1 (Advanced/Enterprise) |  |
| POST | `/allocations` | createAllocation | MISSING — List 1 (Advanced/Enterprise) |  |

## Attachments

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/attachments/{attachment_gid}` | getAttachment | covered | `Attachments/GetAttachmentRequest.php` |
| DELETE | `/attachments/{attachment_gid}` | deleteAttachment | covered | `Attachments/DeleteAttachmentRequest.php` |
| GET | `/attachments` | getAttachmentsForObject | covered | `Attachments/GetAttachmentsForTaskRequest.php (legacy path)` |
| POST | `/attachments` | createAttachmentForObject | covered | `Attachments/CreateAttachmentRequest.php` |

## Audit log API

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/workspaces/{workspace_gid}/audit_log_events` | getAuditLogEvents | MISSING — List 1 (Enterprise) |  |

## Batch API

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| POST | `/batch` | createBatchRequest | covered | `Batch/SubmitBatchRequest.php` |

## Budgets

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/budgets` | getBudgets | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| POST | `/budgets` | createBudget | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| GET | `/budgets/{budget_gid}` | getBudget | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| PUT | `/budgets/{budget_gid}` | updateBudget | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| DELETE | `/budgets/{budget_gid}` | deleteBudget | MISSING — List 1 (Timesheets & Budgets add-on) |  |

## Custom field settings

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/projects/{project_gid}/custom_field_settings` | getCustomFieldSettingsForProject | MISSING — List 2 |  |
| GET | `/portfolios/{portfolio_gid}/custom_field_settings` | getCustomFieldSettingsForPortfolio | MISSING — List 1 (Advanced) |  |
| GET | `/goals/{goal_gid}/custom_field_settings` | getCustomFieldSettingsForGoal | MISSING — List 1 (Advanced) |  |
| GET | `/teams/{team_gid}/custom_field_settings` | getCustomFieldSettingsForTeam | MISSING — List 2 |  |

## Custom fields

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| POST | `/custom_fields` | createCustomField | covered | `CustomFields/CreateCustomFieldRequest.php` |
| GET | `/custom_fields/{custom_field_gid}` | getCustomField | covered | `CustomFields/GetCustomFieldRequest.php` |
| PUT | `/custom_fields/{custom_field_gid}` | updateCustomField | covered | `CustomFields/UpdateCustomFieldRequest.php` |
| DELETE | `/custom_fields/{custom_field_gid}` | deleteCustomField | covered | `CustomFields/DeleteCustomFieldRequest.php` |
| GET | `/workspaces/{workspace_gid}/custom_fields` | getCustomFieldsForWorkspace | covered | `CustomFields/GetCustomFieldsForWorkspaceRequest.php` |
| POST | `/custom_fields/{custom_field_gid}/enum_options` | createEnumOptionForCustomField | MISSING — List 2 |  |
| POST | `/custom_fields/{custom_field_gid}/enum_options/insert` | insertEnumOptionForCustomField | MISSING — List 2 |  |
| PUT | `/enum_options/{enum_option_gid}` | updateEnumOption | MISSING — List 2 |  |

## Custom types

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/custom_types` | getCustomTypes | MISSING — List 2 |  |
| GET | `/custom_types/{custom_type_gid}` | getCustomType | MISSING — List 2 |  |

## Events

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/events` | getEvents | MISSING — List 2 |  |

## Exports

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| POST | `/exports/graph` | createGraphExport | MISSING — List 1 (Enterprise) |  |
| POST | `/exports/resource` | createResourceExport | MISSING — List 1 (Enterprise) |  |

## Goal relationships

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/goal_relationships/{goal_relationship_gid}` | getGoalRelationship | MISSING — List 1 (Advanced) |  |
| PUT | `/goal_relationships/{goal_relationship_gid}` | updateGoalRelationship | MISSING — List 1 (Advanced) |  |
| GET | `/goal_relationships` | getGoalRelationships | covered | `Goals/GetGoalRelationshipsRequest.php` |
| POST | `/goals/{goal_gid}/addSupportingRelationship` | addSupportingRelationship | MISSING — List 1 (Advanced) |  |
| POST | `/goals/{goal_gid}/removeSupportingRelationship` | removeSupportingRelationship | MISSING — List 1 (Advanced) |  |

## Goals

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/goals/{goal_gid}` | getGoal | covered | `Goals/GetGoalRequest.php` |
| PUT | `/goals/{goal_gid}` | updateGoal | covered | `Goals/UpdateGoalRequest.php` |
| DELETE | `/goals/{goal_gid}` | deleteGoal | covered | `Goals/DeleteGoalRequest.php` |
| GET | `/goals` | getGoals | covered | `Goals/GetGoalsRequest.php` |
| POST | `/goals` | createGoal | covered | `Goals/CreateGoalRequest.php` |
| POST | `/goals/{goal_gid}/setMetric` | createGoalMetric | MISSING — List 1 (Advanced) |  |
| POST | `/goals/{goal_gid}/setMetricCurrentValue` | updateGoalMetric | covered | `Goals/UpdateGoalMetricRequest.php` |
| POST | `/goals/{goal_gid}/addFollowers` | addFollowers | MISSING — List 1 (Advanced) |  |
| POST | `/goals/{goal_gid}/removeFollowers` | removeFollowers | MISSING — List 1 (Advanced) |  |
| GET | `/goals/{goal_gid}/parentGoals` | getParentGoalsForGoal | MISSING — List 1 (Advanced) |  |
| POST | `/goals/{goal_gid}/addCustomFieldSetting` | addCustomFieldSettingForGoal | MISSING — List 1 (Advanced) |  |
| POST | `/goals/{goal_gid}/removeCustomFieldSetting` | removeCustomFieldSettingForGoal | MISSING — List 1 (Advanced) |  |

## Jobs

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/jobs/{job_gid}` | getJob | MISSING — List 2 |  |

## Memberships

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/memberships` | getMemberships | MISSING — List 2 |  |
| POST | `/memberships` | createMembership | MISSING — List 2 |  |
| GET | `/memberships/{membership_gid}` | getMembership | MISSING — List 2 |  |
| PUT | `/memberships/{membership_gid}` | updateMembership | MISSING — List 2 |  |
| DELETE | `/memberships/{membership_gid}` | deleteMembership | MISSING — List 2 |  |

## Ooo entries

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/ooo_entries/{ooo_entry_gid}` | getOooEntry | MISSING — List 1 (unverified (likely free)) |  |
| PUT | `/ooo_entries/{ooo_entry_gid}` | updateOooEntry | MISSING — List 1 (unverified (likely free)) |  |
| DELETE | `/ooo_entries/{ooo_entry_gid}` | deleteOooEntry | MISSING — List 1 (unverified (likely free)) |  |
| GET | `/ooo_entries` | getOooEntries | MISSING — List 1 (unverified (likely free)) |  |
| POST | `/ooo_entries` | createOooEntry | MISSING — List 1 (unverified (likely free)) |  |

## Organization exports

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| POST | `/organization_exports` | createOrganizationExport | MISSING — List 1 (Enterprise) |  |
| GET | `/organization_exports/{organization_export_gid}` | getOrganizationExport | MISSING — List 1 (Enterprise) |  |

## Portfolio memberships

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/portfolio_memberships` | getPortfolioMemberships | MISSING — List 1 (Advanced) |  |
| GET | `/portfolio_memberships/{portfolio_membership_gid}` | getPortfolioMembership | MISSING — List 1 (Advanced) |  |
| GET | `/portfolios/{portfolio_gid}/portfolio_memberships` | getPortfolioMembershipsForPortfolio | MISSING — List 1 (Advanced) |  |

## Portfolios

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/portfolios` | getPortfolios | covered | `Portfolios/GetPortfoliosRequest.php` |
| POST | `/portfolios` | createPortfolio | covered | `Portfolios/CreatePortfolioRequest.php` |
| GET | `/portfolios/{portfolio_gid}` | getPortfolio | covered | `Portfolios/GetPortfolioRequest.php` |
| PUT | `/portfolios/{portfolio_gid}` | updatePortfolio | covered | `Portfolios/UpdatePortfolioRequest.php` |
| DELETE | `/portfolios/{portfolio_gid}` | deletePortfolio | covered | `Portfolios/DeletePortfolioRequest.php` |
| GET | `/portfolios/{portfolio_gid}/items` | getItemsForPortfolio | covered | `Portfolios/GetPortfolioItemsRequest.php` |
| POST | `/portfolios/{portfolio_gid}/addItem` | addItemForPortfolio | covered | `Portfolios/AddItemToPortfolioRequest.php` |
| POST | `/portfolios/{portfolio_gid}/removeItem` | removeItemForPortfolio | covered | `Portfolios/RemoveItemFromPortfolioRequest.php` |
| POST | `/portfolios/{portfolio_gid}/addCustomFieldSetting` | addCustomFieldSettingForPortfolio | MISSING — List 1 (Advanced) |  |
| POST | `/portfolios/{portfolio_gid}/removeCustomFieldSetting` | removeCustomFieldSettingForPortfolio | MISSING — List 1 (Advanced) |  |
| POST | `/portfolios/{portfolio_gid}/addMembers` | addMembersForPortfolio | MISSING — List 1 (Advanced) |  |
| POST | `/portfolios/{portfolio_gid}/removeMembers` | removeMembersForPortfolio | MISSING — List 1 (Advanced) |  |
| POST | `/portfolios/{portfolio_gid}/duplicate` | duplicatePortfolio | MISSING — List 1 (Advanced) |  |

## Project briefs

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/project_briefs/{project_brief_gid}` | getProjectBrief | MISSING — List 2 |  |
| PUT | `/project_briefs/{project_brief_gid}` | updateProjectBrief | MISSING — List 2 |  |
| DELETE | `/project_briefs/{project_brief_gid}` | deleteProjectBrief | MISSING — List 2 |  |
| POST | `/projects/{project_gid}/project_briefs` | createProjectBrief | MISSING — List 2 |  |

## Project memberships

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/project_memberships/{project_membership_gid}` | getProjectMembership | MISSING — List 2 |  |
| GET | `/projects/{project_gid}/project_memberships` | getProjectMembershipsForProject | MISSING — List 2 |  |

## Project portfolio settings

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/project_portfolio_settings/{project_portfolio_setting_gid}` | getProjectPortfolioSetting | MISSING — List 1 (Advanced) |  |
| PUT | `/project_portfolio_settings/{project_portfolio_setting_gid}` | updateProjectPortfolioSetting | MISSING — List 1 (Advanced) |  |
| GET | `/projects/{project_gid}/project_portfolio_settings` | getProjectPortfolioSettingsForProject | MISSING — List 1 (Advanced) |  |
| GET | `/portfolios/{portfolio_gid}/project_portfolio_settings` | getProjectPortfolioSettingsForPortfolio | MISSING — List 1 (Advanced) |  |

## Project statuses

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/project_statuses/{project_status_gid}` | getProjectStatus | skip (deprecated) |  |
| DELETE | `/project_statuses/{project_status_gid}` | deleteProjectStatus | skip (deprecated) |  |
| GET | `/projects/{project_gid}/project_statuses` | getProjectStatusesForProject | skip (deprecated) |  |
| POST | `/projects/{project_gid}/project_statuses` | createProjectStatusForProject | skip (deprecated) |  |

## Project templates

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/project_templates/{project_template_gid}` | getProjectTemplate | MISSING — List 2 |  |
| DELETE | `/project_templates/{project_template_gid}` | deleteProjectTemplate | MISSING — List 2 |  |
| GET | `/project_templates` | getProjectTemplates | MISSING — List 2 |  |
| GET | `/teams/{team_gid}/project_templates` | getProjectTemplatesForTeam | MISSING — List 2 |  |
| POST | `/project_templates/{project_template_gid}/instantiateProject` | instantiateProject | MISSING — List 2 |  |

## Projects

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/projects` | getProjects | covered | `Projects/GetProjectsRequest.php` |
| POST | `/projects` | createProject | covered | `Projects/CreateProjectRequest.php` |
| GET | `/projects/{project_gid}` | getProject | covered | `Projects/GetProjectRequest.php` |
| PUT | `/projects/{project_gid}` | updateProject | covered | `Projects/UpdateProjectRequest.php` |
| DELETE | `/projects/{project_gid}` | deleteProject | covered | `Projects/DeleteProjectRequest.php` |
| POST | `/projects/{project_gid}/duplicate` | duplicateProject | covered | `Projects/DuplicateProjectRequest.php` |
| GET | `/tasks/{task_gid}/projects` | getProjectsForTask | MISSING — List 2 |  |
| GET | `/teams/{team_gid}/projects` | getProjectsForTeam | covered | `Projects/GetProjectsForTeamRequest.php` |
| POST | `/teams/{team_gid}/projects` | createProjectForTeam | MISSING — List 2 |  |
| GET | `/workspaces/{workspace_gid}/projects` | getProjectsForWorkspace | MISSING — List 2 |  |
| POST | `/workspaces/{workspace_gid}/projects` | createProjectForWorkspace | MISSING — List 2 |  |
| GET | `/workspaces/{workspace_gid}/projects/search` | searchProjectsForWorkspace | MISSING — List 2 |  |
| POST | `/projects/{project_gid}/addCustomFieldSetting` | addCustomFieldSettingForProject | MISSING — List 2 |  |
| POST | `/projects/{project_gid}/removeCustomFieldSetting` | removeCustomFieldSettingForProject | MISSING — List 2 |  |
| GET | `/projects/{project_gid}/task_counts` | getTaskCountsForProject | covered | `Projects/GetTaskCountsRequest.php` |
| POST | `/projects/{project_gid}/addMembers` | addMembersForProject | MISSING — List 2 |  |
| POST | `/projects/{project_gid}/removeMembers` | removeMembersForProject | MISSING — List 2 |  |
| POST | `/projects/{project_gid}/addFollowers` | addFollowersForProject | MISSING — List 2 |  |
| POST | `/projects/{project_gid}/removeFollowers` | removeFollowersForProject | MISSING — List 2 |  |
| POST | `/projects/{project_gid}/saveAsTemplate` | projectSaveAsTemplate | MISSING — List 2 |  |

## Rates

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/rates` | getRates | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| POST | `/rates` | createRate | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| GET | `/rates/{rate_gid}` | getRate | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| PUT | `/rates/{rate_gid}` | updateRate | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| DELETE | `/rates/{rate_gid}` | deleteRate | MISSING — List 1 (Timesheets & Budgets add-on) |  |

## Reactions

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/reactions` | getReactionsOnObject | MISSING — List 2 |  |

## Roles

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/roles` | getRoles | MISSING — List 1 (Enterprise+) |  |
| POST | `/roles` | createRole | MISSING — List 1 (Enterprise+) |  |
| GET | `/roles/{role_gid}` | getRole | MISSING — List 1 (Enterprise+) |  |
| PUT | `/roles/{role_gid}` | updateRole | MISSING — List 1 (Enterprise+) |  |
| DELETE | `/roles/{role_gid}` | deleteRole | MISSING — List 1 (Enterprise+) |  |

## Rules

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| POST | `/rule_triggers/{rule_trigger_gid}/run` | triggerRule | MISSING — List 1 (Advanced) |  |

## Sections

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/sections/{section_gid}` | getSection | covered | `Sections/GetSectionRequest.php` |
| PUT | `/sections/{section_gid}` | updateSection | covered | `Sections/UpdateSectionRequest.php` |
| DELETE | `/sections/{section_gid}` | deleteSection | covered | `Sections/DeleteSectionRequest.php` |
| GET | `/projects/{project_gid}/sections` | getSectionsForProject | covered | `Sections/GetSectionsForProjectRequest.php` |
| POST | `/projects/{project_gid}/sections` | createSectionForProject | covered | `Sections/CreateSectionRequest.php` |
| POST | `/sections/{section_gid}/addTask` | addTaskForSection | covered | `Sections/AddTaskToSectionRequest.php` |
| POST | `/projects/{project_gid}/sections/insert` | insertSectionForProject | covered | `Sections/InsertSectionRequest.php` |

## Status updates

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/status_updates/{status_update_gid}` | getStatus | MISSING — List 2 |  |
| DELETE | `/status_updates/{status_update_gid}` | deleteStatus | MISSING — List 2 |  |
| GET | `/status_updates` | getStatusesForObject | MISSING — List 2 |  |
| POST | `/status_updates` | createStatusForObject | MISSING — List 2 |  |

## Stories

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/stories/{story_gid}` | getStory | covered | `Stories/GetStoryRequest.php` |
| PUT | `/stories/{story_gid}` | updateStory | covered | `Stories/UpdateStoryRequest.php` |
| DELETE | `/stories/{story_gid}` | deleteStory | covered | `Stories/DeleteStoryRequest.php` |
| GET | `/tasks/{task_gid}/stories` | getStoriesForTask | covered | `Stories/GetStoriesForTaskRequest.php` |
| POST | `/tasks/{task_gid}/stories` | createStoryForTask | covered | `Stories/CreateStoryRequest.php` |
| GET | `/goals/{goal_gid}/stories` | getStoriesForGoal | MISSING — List 1 (Advanced) |  |
| POST | `/goals/{goal_gid}/stories` | createStoryForGoal | MISSING — List 1 (Advanced) |  |

## Tags

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/tags` | getTags | MISSING — List 2 |  |
| POST | `/tags` | createTag | covered | `Tags/CreateTagRequest.php` |
| GET | `/tags/{tag_gid}` | getTag | covered | `Tags/GetTagRequest.php` |
| PUT | `/tags/{tag_gid}` | updateTag | covered | `Tags/UpdateTagRequest.php` |
| DELETE | `/tags/{tag_gid}` | deleteTag | covered | `Tags/DeleteTagRequest.php` |
| GET | `/tasks/{task_gid}/tags` | getTagsForTask | covered | `Tags/GetTagsForTaskRequest.php` |
| GET | `/workspaces/{workspace_gid}/tags` | getTagsForWorkspace | covered | `Tags/GetTagsForWorkspaceRequest.php` |
| POST | `/workspaces/{workspace_gid}/tags` | createTagForWorkspace | covered | `Tags/CreateTagForWorkspaceRequest.php` |

## Task templates

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/task_templates` | getTaskTemplates | MISSING — List 2 |  |
| GET | `/task_templates/{task_template_gid}` | getTaskTemplate | MISSING — List 2 |  |
| DELETE | `/task_templates/{task_template_gid}` | deleteTaskTemplate | MISSING — List 2 |  |
| POST | `/task_templates/{task_template_gid}/instantiateTask` | instantiateTask | MISSING — List 2 |  |

## Tasks

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/tasks` | getTasks | MISSING — List 2 |  |
| POST | `/tasks` | createTask | covered | `Tasks/CreateTaskRequest.php` |
| GET | `/tasks/{task_gid}` | getTask | covered | `Tasks/GetTaskRequest.php` |
| PUT | `/tasks/{task_gid}` | updateTask | covered | `Tasks/UpdateTaskRequest.php` |
| DELETE | `/tasks/{task_gid}` | deleteTask | covered | `Tasks/DeleteTaskRequest.php` |
| POST | `/tasks/{task_gid}/duplicate` | duplicateTask | MISSING — List 2 |  |
| GET | `/projects/{project_gid}/tasks` | getTasksForProject | covered | `Tasks/GetTasksForProjectRequest.php` |
| GET | `/sections/{section_gid}/tasks` | getTasksForSection | covered | `Tasks/GetTasksForSectionRequest.php` |
| GET | `/tags/{tag_gid}/tasks` | getTasksForTag | MISSING — List 2 |  |
| GET | `/user_task_lists/{user_task_list_gid}/tasks` | getTasksForUserTaskList | MISSING — List 2 |  |
| GET | `/tasks/{task_gid}/subtasks` | getSubtasksForTask | covered | `Tasks/GetSubtasksRequest.php` |
| POST | `/tasks/{task_gid}/subtasks` | createSubtaskForTask | MISSING — List 2 |  |
| POST | `/tasks/{task_gid}/setParent` | setParentForTask | covered | `Tasks/SetParentRequest.php` |
| GET | `/tasks/{task_gid}/dependencies` | getDependenciesForTask | covered | `Tasks/GetDependenciesRequest.php` |
| POST | `/tasks/{task_gid}/addDependencies` | addDependenciesForTask | covered | `Tasks/AddDependenciesRequest.php` |
| POST | `/tasks/{task_gid}/removeDependencies` | removeDependenciesForTask | MISSING — List 2 |  |
| GET | `/tasks/{task_gid}/dependents` | getDependentsForTask | covered | `Tasks/GetDependentsRequest.php` |
| POST | `/tasks/{task_gid}/addDependents` | addDependentsForTask | covered | `Tasks/AddDependentsRequest.php` |
| POST | `/tasks/{task_gid}/removeDependents` | removeDependentsForTask | MISSING — List 2 |  |
| POST | `/tasks/{task_gid}/addProject` | addProjectForTask | covered | `Tasks/AddProjectToTaskRequest.php` |
| POST | `/tasks/{task_gid}/removeProject` | removeProjectForTask | covered | `Tasks/RemoveProjectFromTaskRequest.php` |
| POST | `/tasks/{task_gid}/addTag` | addTagForTask | covered | `Tasks/AddTagToTaskRequest.php` |
| POST | `/tasks/{task_gid}/removeTag` | removeTagForTask | covered | `Tasks/RemoveTagFromTaskRequest.php` |
| POST | `/tasks/{task_gid}/addFollowers` | addFollowersForTask | covered | `Tasks/AddFollowersRequest.php` |
| POST | `/tasks/{task_gid}/removeFollowers` | removeFollowerForTask | MISSING — List 2 |  |
| GET | `/workspaces/{workspace_gid}/tasks/custom_id/{custom_id}` | getTaskForCustomID | MISSING — List 2 |  |
| GET | `/workspaces/{workspace_gid}/tasks/search` | searchTasksForWorkspace | covered | `Tasks/SearchTasksRequest.php` |

## Team memberships

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/team_memberships/{team_membership_gid}` | getTeamMembership | MISSING — List 2 |  |
| GET | `/team_memberships` | getTeamMemberships | MISSING — List 2 |  |
| GET | `/teams/{team_gid}/team_memberships` | getTeamMembershipsForTeam | MISSING — List 2 |  |
| GET | `/users/{user_gid}/team_memberships` | getTeamMembershipsForUser | MISSING — List 2 |  |

## Teams

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| POST | `/teams` | createTeam | covered | `Teams/CreateTeamRequest.php` |
| GET | `/teams/{team_gid}` | getTeam | covered | `Teams/GetTeamRequest.php` |
| PUT | `/teams/{team_gid}` | updateTeam | MISSING — List 2 |  |
| GET | `/workspaces/{workspace_gid}/teams` | getTeamsForWorkspace | covered | `Teams/GetTeamsForWorkspaceRequest.php (legacy path)` |
| GET | `/users/{user_gid}/teams` | getTeamsForUser | covered | `Teams/GetTeamsForUserRequest.php` |
| POST | `/teams/{team_gid}/addUser` | addUserForTeam | covered | `Teams/AddUserToTeamRequest.php` |
| POST | `/teams/{team_gid}/removeUser` | removeUserForTeam | covered | `Teams/RemoveUserFromTeamRequest.php` |

## Time periods

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/time_periods/{time_period_gid}` | getTimePeriod | MISSING — List 1 (Advanced) |  |
| GET | `/time_periods` | getTimePeriods | MISSING — List 1 (Advanced) |  |

## Time tracking categories

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/time_tracking_categories/{time_tracking_category_gid}` | getTimeTrackingCategory | MISSING — List 1 (Advanced) |  |
| PUT | `/time_tracking_categories/{time_tracking_category_gid}` | updateTimeTrackingCategory | MISSING — List 1 (Advanced) |  |
| DELETE | `/time_tracking_categories/{time_tracking_category_gid}` | deleteTimeTrackingCategory | MISSING — List 1 (Advanced) |  |
| GET | `/time_tracking_categories/{time_tracking_category_gid}/time_tracking_entries` | getTimeTrackingEntriesForTimeTrackingCategory | MISSING — List 1 (Advanced) |  |
| GET | `/time_tracking_categories` | getTimeTrackingCategories | MISSING — List 1 (Advanced) |  |
| POST | `/time_tracking_categories` | createTimeTrackingCategory | MISSING — List 1 (Advanced) |  |

## Time tracking entries

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/tasks/{task_gid}/time_tracking_entries` | getTimeTrackingEntriesForTask | MISSING — List 1 (Advanced) |  |
| POST | `/tasks/{task_gid}/time_tracking_entries` | createTimeTrackingEntry | MISSING — List 1 (Advanced) |  |
| GET | `/time_tracking_entries/{time_tracking_entry_gid}` | getTimeTrackingEntry | MISSING — List 1 (Advanced) |  |
| PUT | `/time_tracking_entries/{time_tracking_entry_gid}` | updateTimeTrackingEntry | MISSING — List 1 (Advanced) |  |
| DELETE | `/time_tracking_entries/{time_tracking_entry_gid}` | deleteTimeTrackingEntry | MISSING — List 1 (Advanced) |  |
| GET | `/time_tracking_entries` | getTimeTrackingEntries | MISSING — List 1 (Advanced) |  |

## Timesheet approval statuses

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/timesheet_approval_statuses/{timesheet_approval_status_gid}` | getTimesheetApprovalStatus | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| PUT | `/timesheet_approval_statuses/{timesheet_approval_status_gid}` | updateTimesheetApprovalStatus | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| GET | `/timesheet_approval_statuses` | getTimesheetApprovalStatuses | MISSING — List 1 (Timesheets & Budgets add-on) |  |
| POST | `/timesheet_approval_statuses` | createTimesheetApprovalStatus | MISSING — List 1 (Timesheets & Budgets add-on) |  |

## Typeahead

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/workspaces/{workspace_gid}/typeahead` | typeaheadForWorkspace | MISSING — List 2 |  |

## User task lists

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/user_task_lists/{user_task_list_gid}` | getUserTaskList | MISSING — List 2 |  |
| GET | `/users/{user_gid}/user_task_list` | getUserTaskListForUser | MISSING — List 2 |  |

## Users

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/users` | getUsers | covered | `Users/GetUsersRequest.php` |
| GET | `/users/{user_gid}` | getUser | covered | `Users/GetUserRequest.php` |
| PUT | `/users/{user_gid}` | updateUser | MISSING — List 2 |  |
| GET | `/users/{user_gid}/favorites` | getFavoritesForUser | MISSING — List 2 |  |
| GET | `/teams/{team_gid}/users` | getUsersForTeam | covered | `Users/GetUsersForTeamRequest.php` |
| GET | `/workspaces/{workspace_gid}/users` | getUsersForWorkspace | covered | `Users/GetUsersForWorkspaceRequest.php` |
| GET | `/workspaces/{workspace_gid}/users/{user_gid}` | getUserForWorkspace | MISSING — List 2 |  |
| PUT | `/workspaces/{workspace_gid}/users/{user_gid}` | updateUserForWorkspace | MISSING — List 2 |  |

## Webhooks

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/webhooks` | getWebhooks | covered | `Webhooks/GetWebhooksForWorkspaceRequest.php` |
| POST | `/webhooks` | createWebhook | covered | `Webhooks/CreateWebhookRequest.php` |
| GET | `/webhooks/{webhook_gid}` | getWebhook | covered | `Webhooks/GetWebhookRequest.php` |
| PUT | `/webhooks/{webhook_gid}` | updateWebhook | covered | `Webhooks/UpdateWebhookRequest.php` |
| DELETE | `/webhooks/{webhook_gid}` | deleteWebhook | covered | `Webhooks/DeleteWebhookRequest.php` |

## Workspace memberships

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/workspace_memberships/{workspace_membership_gid}` | getWorkspaceMembership | MISSING — List 2 |  |
| GET | `/users/{user_gid}/workspace_memberships` | getWorkspaceMembershipsForUser | MISSING — List 2 |  |
| GET | `/workspaces/{workspace_gid}/workspace_memberships` | getWorkspaceMembershipsForWorkspace | MISSING — List 2 |  |

## Workspaces

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/workspaces` | getWorkspaces | covered | `Workspaces/GetWorkspacesRequest.php` |
| GET | `/workspaces/{workspace_gid}` | getWorkspace | covered | `Workspaces/GetWorkspaceRequest.php` |
| PUT | `/workspaces/{workspace_gid}` | updateWorkspace | covered | `Workspaces/UpdateWorkspaceRequest.php` |
| POST | `/workspaces/{workspace_gid}/addUser` | addUserForWorkspace | covered | `Workspaces/AddUserToWorkspaceRequest.php` |
| POST | `/workspaces/{workspace_gid}/removeUser` | removeUserForWorkspace | covered | `Workspaces/RemoveUserFromWorkspaceRequest.php` |
| GET | `/workspaces/{workspace_gid}/events` | getWorkspaceEvents | MISSING — List 2 |  |
