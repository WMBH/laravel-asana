# Asana API coverage tracker

Generated 2026-09-11 from Asana's OpenAPI spec (`https://raw.githubusercontent.com/Asana/openapi/master/defs/asana_oas.yaml`) diffed against `src/Requests/**`. Regenerate with the script in `docs/superpowers/plans/2026-09-11-api-coverage-upgrade.md` (Task 22) whenever endpoints are added: it matches on HTTP method + path with `{gid}` segments normalized.

| | Endpoints |
|---|---|
| Total in spec | 249 |
| Covered by package | 167 |
| Missing, free/Starter tier (List 2) | 0 |
| Missing, Advanced/Enterprise/add-on (List 1) | 78 |
| Deprecated by Asana, skipped | 4 |

Status legend: **covered** = a Request class exists (path shown). **MISSING — List 2** = free/Starter, planned. **MISSING — List 1** = paid tier in parentheses, awaiting owner review.

## AI Studio usage API

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/workspaces/{workspace_gid}/ai_studio/runs` | getAiStudioRuns | MISSING — List 1 (Enterprise (service account)) |  |
| GET | `/workspaces/{workspace_gid}/ai_studio/seats` | getAiStudioSeats | MISSING — List 1 (Enterprise (service account)) |  |

## Access requests

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/access_requests` | getAccessRequests | covered | `AccessRequests/GetAccessRequestsRequest.php` |
| POST | `/access_requests` | createAccessRequest | covered | `AccessRequests/CreateAccessRequestRequest.php` |
| POST | `/access_requests/{access_request_gid}/approve` | approveAccessRequest | covered | `AccessRequests/ApproveAccessRequestRequest.php` |
| POST | `/access_requests/{access_request_gid}/reject` | rejectAccessRequest | covered | `AccessRequests/RejectAccessRequestRequest.php` |

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
| GET | `/attachments` | getAttachmentsForObject | covered | `Attachments/GetAttachmentsForObjectRequest.php` |
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
| GET | `/projects/{project_gid}/custom_field_settings` | getCustomFieldSettingsForProject | covered | `CustomFields/GetCustomFieldSettingsForProjectRequest.php` |
| GET | `/portfolios/{portfolio_gid}/custom_field_settings` | getCustomFieldSettingsForPortfolio | MISSING — List 1 (Advanced) |  |
| GET | `/goals/{goal_gid}/custom_field_settings` | getCustomFieldSettingsForGoal | MISSING — List 1 (Advanced) |  |
| GET | `/teams/{team_gid}/custom_field_settings` | getCustomFieldSettingsForTeam | covered | `CustomFields/GetCustomFieldSettingsForTeamRequest.php` |

## Custom fields

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| POST | `/custom_fields` | createCustomField | covered | `CustomFields/CreateCustomFieldRequest.php` |
| GET | `/custom_fields/{custom_field_gid}` | getCustomField | covered | `CustomFields/GetCustomFieldRequest.php` |
| PUT | `/custom_fields/{custom_field_gid}` | updateCustomField | covered | `CustomFields/UpdateCustomFieldRequest.php` |
| DELETE | `/custom_fields/{custom_field_gid}` | deleteCustomField | covered | `CustomFields/DeleteCustomFieldRequest.php` |
| GET | `/workspaces/{workspace_gid}/custom_fields` | getCustomFieldsForWorkspace | covered | `CustomFields/GetCustomFieldsForWorkspaceRequest.php` |
| POST | `/custom_fields/{custom_field_gid}/enum_options` | createEnumOptionForCustomField | covered | `CustomFields/CreateEnumOptionRequest.php` |
| POST | `/custom_fields/{custom_field_gid}/enum_options/insert` | insertEnumOptionForCustomField | covered | `CustomFields/InsertEnumOptionRequest.php` |
| PUT | `/enum_options/{enum_option_gid}` | updateEnumOption | covered | `CustomFields/UpdateEnumOptionRequest.php` |

## Custom types

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/custom_types` | getCustomTypes | covered | `CustomTypes/GetCustomTypesRequest.php` |
| GET | `/custom_types/{custom_type_gid}` | getCustomType | covered | `CustomTypes/GetCustomTypeRequest.php` |

## Events

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/events` | getEvents | covered | `Events/GetEventsRequest.php` |

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
| GET | `/goal_relationships` | getGoalRelationships | covered | `Goals/GetSubgoalsRequest.php` |
| POST | `/goals/{goal_gid}/addSupportingRelationship` | addSupportingRelationship | covered | `Goals/AddSubgoalRequest.php` |
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
| GET | `/jobs/{job_gid}` | getJob | covered | `Jobs/GetJobRequest.php` |

## Memberships

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/memberships` | getMemberships | covered | `Memberships/GetMembershipsRequest.php` |
| POST | `/memberships` | createMembership | covered | `Memberships/CreateMembershipRequest.php` |
| GET | `/memberships/{membership_gid}` | getMembership | covered | `Memberships/GetMembershipRequest.php` |
| PUT | `/memberships/{membership_gid}` | updateMembership | covered | `Memberships/UpdateMembershipRequest.php` |
| DELETE | `/memberships/{membership_gid}` | deleteMembership | covered | `Memberships/DeleteMembershipRequest.php` |

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
| GET | `/project_briefs/{project_brief_gid}` | getProjectBrief | covered | `ProjectBriefs/GetProjectBriefRequest.php` |
| PUT | `/project_briefs/{project_brief_gid}` | updateProjectBrief | covered | `ProjectBriefs/UpdateProjectBriefRequest.php` |
| DELETE | `/project_briefs/{project_brief_gid}` | deleteProjectBrief | covered | `ProjectBriefs/DeleteProjectBriefRequest.php` |
| POST | `/projects/{project_gid}/project_briefs` | createProjectBrief | covered | `ProjectBriefs/CreateProjectBriefRequest.php` |

## Project memberships

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/project_memberships/{project_membership_gid}` | getProjectMembership | covered | `Projects/GetProjectMembershipRequest.php` |
| GET | `/projects/{project_gid}/project_memberships` | getProjectMembershipsForProject | covered | `Projects/GetProjectMembershipsRequest.php` |

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
| GET | `/project_templates/{project_template_gid}` | getProjectTemplate | covered | `ProjectTemplates/GetProjectTemplateRequest.php` |
| DELETE | `/project_templates/{project_template_gid}` | deleteProjectTemplate | covered | `ProjectTemplates/DeleteProjectTemplateRequest.php` |
| GET | `/project_templates` | getProjectTemplates | covered | `ProjectTemplates/GetProjectTemplatesRequest.php` |
| GET | `/teams/{team_gid}/project_templates` | getProjectTemplatesForTeam | covered | `ProjectTemplates/GetProjectTemplatesForTeamRequest.php` |
| POST | `/project_templates/{project_template_gid}/instantiateProject` | instantiateProject | covered | `ProjectTemplates/InstantiateProjectRequest.php` |

## Projects

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/projects` | getProjects | covered | `Projects/GetProjectsRequest.php` |
| POST | `/projects` | createProject | covered | `Projects/CreateProjectRequest.php` |
| GET | `/projects/{project_gid}` | getProject | covered | `Projects/GetProjectRequest.php` |
| PUT | `/projects/{project_gid}` | updateProject | covered | `Projects/UpdateProjectRequest.php` |
| DELETE | `/projects/{project_gid}` | deleteProject | covered | `Projects/DeleteProjectRequest.php` |
| POST | `/projects/{project_gid}/duplicate` | duplicateProject | covered | `Projects/DuplicateProjectRequest.php` |
| GET | `/tasks/{task_gid}/projects` | getProjectsForTask | covered | `Projects/GetProjectsForTaskRequest.php` |
| GET | `/teams/{team_gid}/projects` | getProjectsForTeam | covered | `Projects/GetProjectsForTeamRequest.php` |
| POST | `/teams/{team_gid}/projects` | createProjectForTeam | covered | `Projects/CreateProjectForTeamRequest.php` |
| GET | `/workspaces/{workspace_gid}/projects` | getProjectsForWorkspace | covered | `Projects/GetProjectsForWorkspaceRequest.php` |
| POST | `/workspaces/{workspace_gid}/projects` | createProjectForWorkspace | covered | `Projects/CreateProjectForWorkspaceRequest.php` |
| GET | `/workspaces/{workspace_gid}/projects/search` | searchProjectsForWorkspace | covered | `Projects/SearchProjectsRequest.php` |
| POST | `/projects/{project_gid}/addCustomFieldSetting` | addCustomFieldSettingForProject | covered | `Projects/AddCustomFieldSettingToProjectRequest.php` |
| POST | `/projects/{project_gid}/removeCustomFieldSetting` | removeCustomFieldSettingForProject | covered | `Projects/RemoveCustomFieldSettingFromProjectRequest.php` |
| GET | `/projects/{project_gid}/task_counts` | getTaskCountsForProject | covered | `Projects/GetTaskCountsRequest.php` |
| POST | `/projects/{project_gid}/addMembers` | addMembersForProject | covered | `Projects/AddMembersToProjectRequest.php` |
| POST | `/projects/{project_gid}/removeMembers` | removeMembersForProject | covered | `Projects/RemoveMembersFromProjectRequest.php` |
| POST | `/projects/{project_gid}/addFollowers` | addFollowersForProject | covered | `Projects/AddFollowersToProjectRequest.php` |
| POST | `/projects/{project_gid}/removeFollowers` | removeFollowersForProject | covered | `Projects/RemoveFollowersFromProjectRequest.php` |
| POST | `/projects/{project_gid}/saveAsTemplate` | projectSaveAsTemplate | covered | `Projects/SaveProjectAsTemplateRequest.php` |

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
| GET | `/reactions` | getReactionsOnObject | covered | `Reactions/GetReactionsForObjectRequest.php` |

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
| GET | `/status_updates/{status_update_gid}` | getStatus | covered | `StatusUpdates/GetStatusUpdateRequest.php` |
| DELETE | `/status_updates/{status_update_gid}` | deleteStatus | covered | `StatusUpdates/DeleteStatusUpdateRequest.php` |
| GET | `/status_updates` | getStatusesForObject | covered | `StatusUpdates/GetStatusUpdatesForObjectRequest.php` |
| POST | `/status_updates` | createStatusForObject | covered | `StatusUpdates/CreateStatusUpdateRequest.php` |

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
| GET | `/tags` | getTags | covered | `Tags/GetTagsRequest.php` |
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
| GET | `/task_templates` | getTaskTemplates | covered | `TaskTemplates/GetTaskTemplatesRequest.php` |
| GET | `/task_templates/{task_template_gid}` | getTaskTemplate | covered | `TaskTemplates/GetTaskTemplateRequest.php` |
| DELETE | `/task_templates/{task_template_gid}` | deleteTaskTemplate | covered | `TaskTemplates/DeleteTaskTemplateRequest.php` |
| POST | `/task_templates/{task_template_gid}/instantiateTask` | instantiateTask | covered | `TaskTemplates/InstantiateTaskRequest.php` |

## Tasks

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/tasks` | getTasks | covered | `Tasks/GetTasksRequest.php` |
| POST | `/tasks` | createTask | covered | `Tasks/CreateTaskRequest.php` |
| GET | `/tasks/{task_gid}` | getTask | covered | `Tasks/GetTaskRequest.php` |
| PUT | `/tasks/{task_gid}` | updateTask | covered | `Tasks/UpdateTaskRequest.php` |
| DELETE | `/tasks/{task_gid}` | deleteTask | covered | `Tasks/DeleteTaskRequest.php` |
| POST | `/tasks/{task_gid}/duplicate` | duplicateTask | covered | `Tasks/DuplicateTaskRequest.php` |
| GET | `/projects/{project_gid}/tasks` | getTasksForProject | covered | `Tasks/GetTasksForProjectRequest.php` |
| GET | `/sections/{section_gid}/tasks` | getTasksForSection | covered | `Tasks/GetTasksForSectionRequest.php` |
| GET | `/tags/{tag_gid}/tasks` | getTasksForTag | covered | `Tasks/GetTasksForTagRequest.php` |
| GET | `/user_task_lists/{user_task_list_gid}/tasks` | getTasksForUserTaskList | covered | `Tasks/GetTasksForUserTaskListRequest.php` |
| GET | `/tasks/{task_gid}/subtasks` | getSubtasksForTask | covered | `Tasks/GetSubtasksRequest.php` |
| POST | `/tasks/{task_gid}/subtasks` | createSubtaskForTask | covered | `Tasks/CreateSubtaskRequest.php` |
| POST | `/tasks/{task_gid}/setParent` | setParentForTask | covered | `Tasks/SetParentRequest.php` |
| GET | `/tasks/{task_gid}/dependencies` | getDependenciesForTask | covered | `Tasks/GetDependenciesRequest.php` |
| POST | `/tasks/{task_gid}/addDependencies` | addDependenciesForTask | covered | `Tasks/AddDependenciesRequest.php` |
| POST | `/tasks/{task_gid}/removeDependencies` | removeDependenciesForTask | covered | `Tasks/RemoveDependenciesRequest.php` |
| GET | `/tasks/{task_gid}/dependents` | getDependentsForTask | covered | `Tasks/GetDependentsRequest.php` |
| POST | `/tasks/{task_gid}/addDependents` | addDependentsForTask | covered | `Tasks/AddDependentsRequest.php` |
| POST | `/tasks/{task_gid}/removeDependents` | removeDependentsForTask | covered | `Tasks/RemoveDependentsRequest.php` |
| POST | `/tasks/{task_gid}/addProject` | addProjectForTask | covered | `Tasks/AddProjectToTaskRequest.php` |
| POST | `/tasks/{task_gid}/removeProject` | removeProjectForTask | covered | `Tasks/RemoveProjectFromTaskRequest.php` |
| POST | `/tasks/{task_gid}/addTag` | addTagForTask | covered | `Tasks/AddTagToTaskRequest.php` |
| POST | `/tasks/{task_gid}/removeTag` | removeTagForTask | covered | `Tasks/RemoveTagFromTaskRequest.php` |
| POST | `/tasks/{task_gid}/addFollowers` | addFollowersForTask | covered | `Tasks/AddFollowersRequest.php` |
| POST | `/tasks/{task_gid}/removeFollowers` | removeFollowerForTask | covered | `Tasks/RemoveFollowersRequest.php` |
| GET | `/workspaces/{workspace_gid}/tasks/custom_id/{custom_id}` | getTaskForCustomID | covered | `Tasks/GetTaskByCustomIdRequest.php` |
| GET | `/workspaces/{workspace_gid}/tasks/search` | searchTasksForWorkspace | covered | `Tasks/SearchTasksRequest.php` |

## Team memberships

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/team_memberships/{team_membership_gid}` | getTeamMembership | covered | `Teams/GetTeamMembershipRequest.php` |
| GET | `/team_memberships` | getTeamMemberships | covered | `Teams/GetTeamMembershipsRequest.php` |
| GET | `/teams/{team_gid}/team_memberships` | getTeamMembershipsForTeam | covered | `Teams/GetTeamMembershipsForTeamRequest.php` |
| GET | `/users/{user_gid}/team_memberships` | getTeamMembershipsForUser | covered | `Teams/GetTeamMembershipsForUserRequest.php` |

## Teams

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| POST | `/teams` | createTeam | covered | `Teams/CreateTeamRequest.php` |
| GET | `/teams/{team_gid}` | getTeam | covered | `Teams/GetTeamRequest.php` |
| PUT | `/teams/{team_gid}` | updateTeam | covered | `Teams/UpdateTeamRequest.php` |
| GET | `/workspaces/{workspace_gid}/teams` | getTeamsForWorkspace | covered | `Teams/GetTeamsForWorkspaceRequest.php` |
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
| GET | `/workspaces/{workspace_gid}/typeahead` | typeaheadForWorkspace | covered | `Workspaces/TypeaheadRequest.php` |

## User task lists

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/user_task_lists/{user_task_list_gid}` | getUserTaskList | covered | `UserTaskLists/GetUserTaskListRequest.php` |
| GET | `/users/{user_gid}/user_task_list` | getUserTaskListForUser | covered | `UserTaskLists/GetUserTaskListForUserRequest.php` |

## Users

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/users` | getUsers | covered | `Users/GetUsersRequest.php` |
| GET | `/users/{user_gid}` | getUser | covered | `Users/GetUserRequest.php` |
| PUT | `/users/{user_gid}` | updateUser | covered | `Users/UpdateUserRequest.php` |
| GET | `/users/{user_gid}/favorites` | getFavoritesForUser | covered | `Users/GetFavoritesForUserRequest.php` |
| GET | `/teams/{team_gid}/users` | getUsersForTeam | covered | `Users/GetUsersForTeamRequest.php` |
| GET | `/workspaces/{workspace_gid}/users` | getUsersForWorkspace | covered | `Users/GetUsersForWorkspaceRequest.php` |
| GET | `/workspaces/{workspace_gid}/users/{user_gid}` | getUserForWorkspace | covered | `Users/GetUserForWorkspaceRequest.php` |
| PUT | `/workspaces/{workspace_gid}/users/{user_gid}` | updateUserForWorkspace | covered | `Users/UpdateUserForWorkspaceRequest.php` |

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
| GET | `/workspace_memberships/{workspace_membership_gid}` | getWorkspaceMembership | covered | `Workspaces/GetWorkspaceMembershipRequest.php` |
| GET | `/users/{user_gid}/workspace_memberships` | getWorkspaceMembershipsForUser | covered | `Workspaces/GetWorkspaceMembershipsForUserRequest.php` |
| GET | `/workspaces/{workspace_gid}/workspace_memberships` | getWorkspaceMembershipsForWorkspace | covered | `Workspaces/GetWorkspaceMembershipsRequest.php` |

## Workspaces

| Method | Path | operationId | Status | Package request |
|---|---|---|---|---|
| GET | `/workspaces` | getWorkspaces | covered | `Workspaces/GetWorkspacesRequest.php` |
| GET | `/workspaces/{workspace_gid}` | getWorkspace | covered | `Workspaces/GetWorkspaceRequest.php` |
| PUT | `/workspaces/{workspace_gid}` | updateWorkspace | covered | `Workspaces/UpdateWorkspaceRequest.php` |
| POST | `/workspaces/{workspace_gid}/addUser` | addUserForWorkspace | covered | `Workspaces/AddUserToWorkspaceRequest.php` |
| POST | `/workspaces/{workspace_gid}/removeUser` | removeUserForWorkspace | covered | `Workspaces/RemoveUserFromWorkspaceRequest.php` |
| GET | `/workspaces/{workspace_gid}/events` | getWorkspaceEvents | covered | `Events/GetWorkspaceEventsRequest.php` |
