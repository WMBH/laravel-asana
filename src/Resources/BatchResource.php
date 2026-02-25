<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Requests\Batch\SubmitBatchRequest;

class BatchResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function submit(array $actions): array
    {
        $response = $this->connector->send(new SubmitBatchRequest($actions));

        return $response->json('data');
    }
}
