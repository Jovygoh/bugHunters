<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Shared\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 * @implements RepositoryInterface<TModel>
 */
abstract class AbstractEloquentRepository implements RepositoryInterface
{
    /** @param TModel $model */
    public function __construct(protected Model $model)
    {
    }

    public function find(string $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findOrFail(string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function paginate(int $perPage = 25, array $filters = []): LengthAwarePaginator
    {
        $query = $this->query();

        foreach (array_intersect_key($filters, array_flip($this->filterable())) as $column => $value) {
            $query->where($column, $value);
        }

        return $query->paginate($perPage);
    }

    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    /** @return Builder<TModel> */
    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    /** @return list<string> */
    protected function filterable(): array
    {
        return [];
    }
}

