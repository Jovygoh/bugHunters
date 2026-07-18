<?php

namespace App\Http\Controllers\Api\V1\DataClassification;

use App\Application\DataClassification\Services\ClassificationDefinitionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DataClassification\IndexViolationTypeRequest;
use App\Http\Requests\Api\V1\DataClassification\ManageViolationTypeRequest;
use App\Http\Requests\Api\V1\DataClassification\StoreViolationTypeRequest;
use App\Http\Requests\Api\V1\DataClassification\UpdateViolationTypeRequest;
use App\Http\Requests\Api\V1\DataClassification\ViewViolationTypeRequest;
use App\Http\Resources\Api\V1\ViolationTypeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ViolationTypeController extends Controller
{
    public function __construct(private readonly ClassificationDefinitionService $definitions) {}

    public function index(IndexViolationTypeRequest $request): AnonymousResourceCollection
    {
        return ViolationTypeResource::collection($this->definitions->listViolationTypes(
            $request->user()->organization_id,
            $request->filters(),
            $request->integer('per_page', 25),
        ));
    }

    public function store(StoreViolationTypeRequest $request): JsonResponse
    {
        $type = $this->definitions->createViolationType($request->user()->organization_id, $request->validated());

        return (new ViolationTypeResource($type))->response()->setStatusCode(201);
    }

    public function show(ViewViolationTypeRequest $request, string $violationType): ViolationTypeResource
    {
        return new ViolationTypeResource(
            $this->definitions->getViolationType($request->user()->organization_id, $violationType)
        );
    }

    public function update(UpdateViolationTypeRequest $request, string $violationType): ViolationTypeResource
    {
        return new ViolationTypeResource($this->definitions->updateViolationType(
            $request->user()->organization_id,
            $violationType,
            $request->validated(),
        ));
    }

    public function destroy(ManageViolationTypeRequest $request, string $violationType): JsonResponse
    {
        $this->definitions->deleteViolationType($request->user()->organization_id, $violationType);

        return response()->json(status: 204);
    }
}

