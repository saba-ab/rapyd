<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Resources\Concerns;

use Illuminate\Support\LazyCollection;
use Sabaab\Rapyd\Pagination\RapydPaginator;

trait HasCrud
{
    abstract protected function basePath(): string;

    abstract protected function dtoClass(): string;

    public function create(array $data): mixed
    {
        return $this->client->post($this->basePath(), $data)->toDto($this->dtoClass());
    }

    public function get(string $id): mixed
    {
        return $this->client->get("{$this->basePath()}/{$id}")->toDto($this->dtoClass());
    }

    public function update(string $id, array $data): mixed
    {
        return $this->client->put("{$this->basePath()}/{$id}", $data)->toDto($this->dtoClass());
    }

    public function delete(string $id): mixed
    {
        return $this->client->delete("{$this->basePath()}/{$id}")->toDto($this->dtoClass());
    }

    public function list(array $params = []): RapydPaginator
    {
        return new RapydPaginator($this->client, $this->basePath(), $params, $this->dtoClass());
    }

    public function all(array $params = []): LazyCollection
    {
        return $this->list($params)->lazy();
    }
}
