<?php


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface RepositoryInterface
{
    public const NO_LOCK = 0;
    public const LOCK_FOR_SELECT = 1;
    public const LOCK_FOR_UPDATE = 2;

    public function find($id, array $columns = ['*']): ?Model;

    public function findOrFail($id, array $columns = ['*']): Model|ModelNotFoundException;

    public function findAll(array|string $columns = ['*']): Collection;

    public function first(array|string $columns = ['*']): ?Model;

    public function firstOrCreate(array $attributes): ?Model;

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): Collection;

    public function findOneBy(array $criteria): ?Model;

    public function persist(array $attributes): Model;

    public function update($id, array $attributes): Model;

    public function updateModel(Model $model, array $attributes): Model;

    public function delete($id): bool|null;

    public function deleteModel(Model $model): bool|null;

    public function orderBy($column, $direction = 'asc'): self;

    public function transactional(callable $callable);

    public function create(array $attributes = []): Model;

    public function findByWithRelation(array $criteria, array $with, array $columns = ['*']): ?Collection;
}
