<?php

namespace App\Application\Organizations\Services;

use App\Domain\Organizations\Repositories\OrganizationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/** Creates organizations and manages their lifecycle state and settings. */
final readonly class OrganizationManagementService
{
    public function __construct(private OrganizationRepositoryInterface $organizations) {}

    public function create(array $attributes): Model
    {
        $attributes['slug'] = mb_strtolower(trim($attributes['slug']));
        if ($this->organizations->findBySlug($attributes['slug'])) {
            throw ValidationException::withMessages(['slug' => ['The organization slug is already in use.']]);
        }

        return $this->organizations->create($attributes);
    }

    public function update(string $id, array $attributes): Model
    {
        return $this->organizations->update($this->organizations->findOrFail($id), $attributes);
    }

    public function changeStatus(string $id, string $status): Model
    {
        if (! in_array($status, ['active', 'suspended', 'archived'], true)) {
            throw ValidationException::withMessages(['status' => ['Invalid organization status.']]);
        }

        return $this->organizations->update($this->organizations->findOrFail($id), ['status' => $status]);
    }
}

