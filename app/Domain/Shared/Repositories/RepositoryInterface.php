<?php

namespace App\Domain\Shared\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface RepositoryInterface
{
    /** @return TModel|null */
    public function find(string $id): ?Model;

    /** @return TModel */
    public function findOrFail(string $id): Model;

    /** @return LengthAwarePaginator<int, TModel> */
    public function paginate(int $perPage = 25, array $filters = []): LengthAwarePaginator;

    /** @return TModel */
    public function create(array $attributes): Model;

    /** @param TModel $model @return TModel */
    public function update(Model $model, array $attributes): Model;

    /** @param TModel $model */
    public function delete(Model $model): bool;
}

