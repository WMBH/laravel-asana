<?php

namespace WMBH\Asana;

use WMBH\Asana\Resources\AttachmentResource;
use WMBH\Asana\Resources\BatchResource;
use WMBH\Asana\Resources\CustomFieldResource;
use WMBH\Asana\Resources\GoalResource;
use WMBH\Asana\Resources\PortfolioResource;
use WMBH\Asana\Resources\ProjectResource;
use WMBH\Asana\Resources\SectionResource;
use WMBH\Asana\Resources\StoryResource;
use WMBH\Asana\Resources\TagResource;
use WMBH\Asana\Resources\TaskResource;
use WMBH\Asana\Resources\TeamResource;
use WMBH\Asana\Resources\UserResource;
use WMBH\Asana\Resources\WebhookResource;
use WMBH\Asana\Resources\WorkspaceResource;

class Asana
{
    private ?TaskResource $taskResource = null;

    private ?ProjectResource $projectResource = null;

    private ?SectionResource $sectionResource = null;

    private ?WorkspaceResource $workspaceResource = null;

    private ?UserResource $userResource = null;

    private ?TeamResource $teamResource = null;

    private ?TagResource $tagResource = null;

    private ?StoryResource $storyResource = null;

    private ?AttachmentResource $attachmentResource = null;

    private ?CustomFieldResource $customFieldResource = null;

    private ?PortfolioResource $portfolioResource = null;

    private ?GoalResource $goalResource = null;

    private ?WebhookResource $webhookResource = null;

    private ?BatchResource $batchResource = null;

    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function tasks(): TaskResource
    {
        return $this->taskResource ??= new TaskResource($this->connector);
    }

    public function projects(): ProjectResource
    {
        return $this->projectResource ??= new ProjectResource($this->connector);
    }

    public function sections(): SectionResource
    {
        return $this->sectionResource ??= new SectionResource($this->connector);
    }

    public function workspaces(): WorkspaceResource
    {
        return $this->workspaceResource ??= new WorkspaceResource($this->connector);
    }

    public function users(): UserResource
    {
        return $this->userResource ??= new UserResource($this->connector);
    }

    public function teams(): TeamResource
    {
        return $this->teamResource ??= new TeamResource($this->connector);
    }

    public function tags(): TagResource
    {
        return $this->tagResource ??= new TagResource($this->connector);
    }

    public function stories(): StoryResource
    {
        return $this->storyResource ??= new StoryResource($this->connector);
    }

    public function attachments(): AttachmentResource
    {
        return $this->attachmentResource ??= new AttachmentResource($this->connector);
    }

    public function customFields(): CustomFieldResource
    {
        return $this->customFieldResource ??= new CustomFieldResource($this->connector);
    }

    public function portfolios(): PortfolioResource
    {
        return $this->portfolioResource ??= new PortfolioResource($this->connector);
    }

    public function goals(): GoalResource
    {
        return $this->goalResource ??= new GoalResource($this->connector);
    }

    public function webhooks(): WebhookResource
    {
        return $this->webhookResource ??= new WebhookResource($this->connector);
    }

    public function batch(): BatchResource
    {
        return $this->batchResource ??= new BatchResource($this->connector);
    }

    public function testConnection(): bool
    {
        try {
            $this->users()->me();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function getConnector(): AsanaConnector
    {
        return $this->connector;
    }
}
