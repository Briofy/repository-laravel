<?php

namespace Briofy\RepositoryLaravel\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

abstract class AbstractRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->setInstance();


    }

    public function find($id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    public function findOrFail($id, array $columns = ['*']): Model|ModelNotFoundException
    {
        return $this->model->findOrFail($id, $columns);
    }

    public function firstOrFail(array $criteria): Model|ModelNotFoundException
    {
        return $this->model->where($criteria)->firstOrFail();
    }

    public function findAll(array|string $columns = ['*']): Collection
    {
        return $this->model->get($columns);
    }

    public function first(array|string $columns = ['*']): ?Model
    {
        return $this->model->first($columns);
    }

    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        $limit = null,
        $offset = null,
        $columns = ['*']
    ): Collection {
        return $this->model
            ->where($criteria)
            ->when($orderBy, function ($query, $orderBy) {
                foreach ($orderBy as $column => $direction) {
                    $query->orderBy($column, $direction);
                }

                return $query;
            })
            ->when($limit, fn($query, $limit) => $query->limit($limit))
            ->when($offset, fn($query, $offset) => $query->offset($offset))
            ->get($columns);
    }

    public function findOneBy(array $criteria): ?Model
    {
        return $this->model->where($criteria)->first();
    }

    public function firstOrCreate(array $attributes): ?Model
    {
        return $this->model->firstOrCreate($attributes);
    }

    public function updateOrCreate(array $attributes, array $values): ?Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function persist(array $attributes): Model
    {
        return tap($this->instance($attributes), fn(Model $instance) => $instance->save());
    }

    public function update($id, array $attributes): Model
    {
        $model = $this->model->findOrFail($id);

        return $this->updateModel($model, $attributes);
    }

    public function updateModel(Model $model, array $attributes): Model
    {
        $model->fill($attributes)->save();

        return $model;
    }

    public function increment(Model $model, string $column, float|int $amount = 1): int
    {
        return $model->increment($column, $amount);
    }

    public function decrement(Model $model, string $column, float|int $amount = 1): int
    {
        return $model->decrement($column, $amount);
    }

    public function create(array $attributes = []): Model
    {
        return $this->model->create($attributes);
    }

    public function delete($id): bool|null
    {
        $model = $this->model->findOrFail($id);

        return $this->deleteModel($model);
    }

    public function deleteModel(Model $model): bool|null
    {
        return $model->delete();
    }

    public function orderBy($column, $direction = 'asc'): self
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    public function onlyFirstLevel(?String $column = 'parent_id'): self
    {
        $this->model = $this->model->whereNull($column);
        return $this;
    }

    public function transactional(callable $callable)
    {
        try {
            DB::beginTransaction();

            $result = $callable($this);

            DB::commit();

            return $result;
        } catch (Exception | Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->model, $method], $arguments);
    }

    /**
     *
     * @throws ModelNotFoundException
     */
    public function findWithRelation(
        int|string $id,
        array $with,
        array $columns = ['*'],
        bool $trowFailException = false
    ): ?Model {
        $builder = $this->model->with($with);

        return !$trowFailException ? $builder->find($id, $columns) : $builder->findOrFail($id, $columns);
    }

    protected function setInstance(array $attributes = []): Model
    {
        $this->model = $this->instance($attributes);

        return $this->model;
    }

    public function findByWithRelation(array $criteria, array $with, array $columns = ['*']): ?Collection
    {
        return $this->model->with($with)->where($criteria)->get($columns);
    }

    abstract protected function instance(array $attributes = []): Model;
}
