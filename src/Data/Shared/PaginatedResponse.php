<?php

namespace WMBH\Asana\Data\Shared;

class PaginatedResponse
{
    public function __construct(
        public readonly array $data,
        public readonly ?string $nextPageToken = null,
        public readonly ?string $nextPageUri = null,
    ) {}

    public static function fromResponse(array $response, string $dataClass): self
    {
        $items = array_map(
            fn (array $item) => $dataClass::from($item),
            $response['data'] ?? []
        );

        $nextPage = $response['next_page'] ?? null;

        return new self(
            data: $items,
            nextPageToken: $nextPage['offset'] ?? null,
            nextPageUri: $nextPage['uri'] ?? null,
        );
    }

    public function hasNextPage(): bool
    {
        return $this->nextPageToken !== null;
    }
}
