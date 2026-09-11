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
