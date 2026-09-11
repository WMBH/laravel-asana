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
