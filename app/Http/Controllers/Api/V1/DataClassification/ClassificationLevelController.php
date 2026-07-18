<?php

namespace App\Http\Controllers\Api\V1\DataClassification;

use App\Application\DataClassification\Services\ClassificationDefinitionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DataClassification\IndexClassificationLevelRequest;
use App\Http\Requests\Api\V1\DataClassification\ManageClassificationLevelRequest;
use App\Http\Requests\Api\V1\DataClassification\StoreClassificationLevelRequest;
use App\Http\Requests\Api\V1\DataClassification\UpdateClassificationLevelRequest;
use App\Http\Requests\Api\V1\DataClassification\ViewClassificationLevelRequest;
use App\Http\Resources\Api\V1\ClassificationLevelResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ClassificationLevelController extends Controller
{
    public function __construct(private readonly ClassificationDefinitionService $definitions) {}

    public function index(IndexClassificationLevelRequest $request): AnonymousResourceCollection
    {
        return ClassificationLevelResource::collection($this->definitions->listLevels(
            $request->user()->organization_id,
            $request->integer('per_page', 25),
        ));
    }

    public function store(StoreClassificationLevelRequest $request): JsonResponse
    {
        $level = $this->definitions->createLevel($request->user()->organization_id, $request->validated());

        return (new ClassificationLevelResource($level))->response()->setStatusCode(201);
    }

    public function show(ViewClassificationLevelRequest $request, string $classificationLevel): ClassificationLevelResource
    {
        return new ClassificationLevelResource(
            $this->definitions->getLevel($request->user()->organization_id, $classificationLevel)
        );
    }

    public function update(UpdateClassificationLevelRequest $request, string $classificationLevel): ClassificationLevelResource
    {
        return new ClassificationLevelResource($this->definitions->updateLevel(
            $request->user()->organization_id,
            $classificationLevel,
            $request->validated(),
        ));
    }

    public function destroy(ManageClassificationLevelRequest $request, string $classificationLevel): JsonResponse
    {
        $this->definitions->deleteLevel($request->user()->organization_id, $classificationLevel);

        return response()->json(status: 204);
    }
}

