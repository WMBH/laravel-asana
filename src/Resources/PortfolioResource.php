<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\PortfolioData;
use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\Portfolios\AddItemToPortfolioRequest;
use WMBH\Asana\Requests\Portfolios\CreatePortfolioRequest;
use WMBH\Asana\Requests\Portfolios\DeletePortfolioRequest;
use WMBH\Asana\Requests\Portfolios\GetPortfolioItemsRequest;
use WMBH\Asana\Requests\Portfolios\GetPortfolioRequest;
use WMBH\Asana\Requests\Portfolios\GetPortfoliosRequest;
use WMBH\Asana\Requests\Portfolios\RemoveItemFromPortfolioRequest;
use WMBH\Asana\Requests\Portfolios\UpdatePortfolioRequest;

class PortfolioResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): PortfolioData
    {
        $response = $this->connector->send(new GetPortfolioRequest($gid, $optFields));

        return PortfolioData::from($response->json('data'));
    }

    public function list(string $workspaceGid, string $ownerGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetPortfoliosRequest($workspaceGid, $ownerGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), PortfolioData::class);
    }

    public function getItems(string $portfolioGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetPortfolioItemsRequest($portfolioGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), CompactResource::class);
    }

    public function addItem(string $portfolioGid, string $itemGid): bool
    {
        $this->connector->send(new AddItemToPortfolioRequest($portfolioGid, $itemGid));

        return true;
    }

    public function removeItem(string $portfolioGid, string $itemGid): bool
    {
        $this->connector->send(new RemoveItemFromPortfolioRequest($portfolioGid, $itemGid));

        return true;
    }

    public function create(array $data): PortfolioData
    {
        $response = $this->connector->send(new CreatePortfolioRequest($data));

        return PortfolioData::from($response->json('data'));
    }

    public function update(string $gid, array $data): PortfolioData
    {
        $response = $this->connector->send(new UpdatePortfolioRequest($gid, $data));

        return PortfolioData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $this->connector->send(new DeletePortfolioRequest($gid));

        return true;
    }
}
