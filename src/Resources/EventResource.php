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
