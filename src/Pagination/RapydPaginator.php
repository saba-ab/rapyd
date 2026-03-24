<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Pagination;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Sabaab\Rapyd\Client\RapydClient;

final class RapydPaginator implements \IteratorAggregate
{
    private const DEFAULT_LIMIT = 10;

    public function __construct(
        private readonly RapydClient $client,
        private readonly string $path,
        private readonly array $params,
        private readonly string $dtoClass,
    ) {}

    public function lazy(): LazyCollection
    {
        return LazyCollection::make(function () {
            $limit = (int) ($this->params['limit'] ?? self::DEFAULT_LIMIT);
            $page = (int) ($this->params['page'] ?? 1);
            $dtoClass = $this->dtoClass;

            while (true) {
                $mergedParams = array_merge($this->params, [
                    'page' => $page,
                    'limit' => $limit,
                ]);

                $response = $this->client->get($this->path, $mergedParams);
                $response->throw();
                $items = $response->data();

                if ($items === null || $items === []) {
                    return;
                }

                foreach ($items as $item) {
                    yield $dtoClass::fromArray($item);
                }

                if (count($items) < $limit) {
                    return;
                }

                $page++;
            }
        });
    }

    public function getIterator(): \Traversable
    {
        return $this->lazy()->getIterator();
    }

    public function first(): mixed
    {
        $paginator = new self(
            $this->client,
            $this->path,
            array_merge($this->params, ['limit' => 1, 'page' => 1]),
            $this->dtoClass,
        );

        return $paginator->lazy()->first();
    }

    public function collect(): Collection
    {
        return $this->lazy()->collect();
    }

    public function toArray(): array
    {
        return $this->lazy()->all();
    }
}
