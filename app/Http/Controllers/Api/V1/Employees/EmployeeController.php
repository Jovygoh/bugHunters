<?php

namespace App\Http\Controllers\Api\V1\Employees;

use App\Application\Employees\Services\EmployeeManagementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Employees\DeleteEmployeeRequest;
use App\Http\Requests\Api\V1\Employees\IndexEmployeeRequest;
use App\Http\Requests\Api\V1\Employees\ShowEmployeeRequest;
use App\Http\Requests\Api\V1\Employees\StoreEmployeeRequest;
use App\Http\Requests\Api\V1\Employees\UpdateEmployeeRequest;
use App\Http\Resources\Api\V1\EmployeeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeManagementService $employees) {}

    public function index(IndexEmployeeRequest $request): AnonymousResourceCollection
    {
        return EmployeeResource::collection(
            $this->employees->list(
                $request->user()->organization_id,
                $request->filters(),
                $request->integer('per_page', 25),
            )
        );
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employees->create($request->user()->organization_id, $request->validated());

        return (new EmployeeResource($employee))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ShowEmployeeRequest $request, string $employee): EmployeeResource
    {
        return new EmployeeResource(
            $this->employees->get($request->user()->organization_id, $employee)
        );
    }

    public function update(UpdateEmployeeRequest $request, string $employee): EmployeeResource
    {
        return new EmployeeResource(
            $this->employees->update($request->user()->organization_id, $employee, $request->validated())
        );
    }

    public function destroy(DeleteEmployeeRequest $request, string $employee): JsonResponse
    {
        $this->employees->delete($request->user()->organization_id, $employee);

        return response()->json(status: 204);
    }
}

