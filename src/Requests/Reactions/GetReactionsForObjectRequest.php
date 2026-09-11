<?php

namespace WMBH\Asana\Requests\Reactions;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetReactionsForObjectRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $targetGid,
        protected readonly string $emojiBase,
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/reactions';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'target' => $this->targetGid,
            'emoji_base' => $this->emojiBase,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
