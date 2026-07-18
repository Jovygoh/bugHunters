<?php

namespace App\Http\Controllers\Api\V1\Departments;

use App\Application\Departments\Services\DepartmentManagementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Departments\DeleteDepartmentRequest;
use App\Http\Requests\Api\V1\Departments\IndexDepartmentRequest;
use App\Http\Requests\Api\V1\Departments\ShowDepartmentRequest;
use App\Http\Requests\Api\V1\Departments\StoreDepartmentRequest;
use App\Http\Requests\Api\V1\Departments\UpdateDepartmentRequest;
use App\Http\Resources\Api\V1\DepartmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentManagementService $departments) {}

    public function index(IndexDepartmentRequest $request): AnonymousResourceCollection
    {
        return DepartmentResource::collection(
            $this->departments->list(
                $request->user()->organization_id,
                $request->filters(),
                $request->integer('per_page', 25),
            )
        );
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departments->create($request->user()->organization_id, $request->validated());

        return (new DepartmentResource($department))->response()->setStatusCode(201);
    }

    public function show(ShowDepartmentRequest $request, string $department): DepartmentResource
    {
        return new DepartmentResource($this->departments->get($request->user()->organization_id, $department));
    }

    public function update(UpdateDepartmentRequest $request, string $department): DepartmentResource
    {
        return new DepartmentResource(
            $this->departments->update($request->user()->organization_id, $department, $request->validated())
        );
    }

    public function destroy(DeleteDepartmentRequest $request, string $department): JsonResponse
    {
        $this->departments->delete($request->user()->organization_id, $department);

        return response()->json(status: 204);
    }
}

