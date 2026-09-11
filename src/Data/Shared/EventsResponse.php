<?php

namespace WMBH\Asana\Data\Shared;

use WMBH\Asana\Data\EventData;

class EventsResponse
{
    public function __construct(
        public readonly array $data,
        public readonly ?string $sync = null,
        public readonly bool $hasMore = false,
    ) {}

    public static function fromResponse(array $response): self
    {
        $items = array_map(
            fn (array $item) => EventData::from($item),
            $response['data'] ?? []
        );

        return new self(
            data: $items,
            sync: $response['sync'] ?? null,
            hasMore: (bool) ($response['has_more'] ?? false),
        );
    }
}
