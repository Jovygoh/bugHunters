<?php

namespace App\Http\Controllers\Api\V1\AiTools;

use App\Application\AiTools\Services\AiToolGovernanceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AiTools\DeleteAiToolRequest;
use App\Http\Requests\Api\V1\AiTools\IndexAiToolRequest;
use App\Http\Requests\Api\V1\AiTools\ShowAiToolRequest;
use App\Http\Requests\Api\V1\AiTools\StoreAiToolRequest;
use App\Http\Requests\Api\V1\AiTools\UpdateAiToolRequest;
use App\Http\Resources\Api\V1\AiToolResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AiToolController extends Controller
{
    public function __construct(private readonly AiToolGovernanceService $aiTools) {}

    public function index(IndexAiToolRequest $request): AnonymousResourceCollection
    {
        return AiToolResource::collection($this->aiTools->list(
            $request->user()->organization_id,
            $request->filters(),
            $request->integer('per_page', 25),
        ));
    }

    public function store(StoreAiToolRequest $request): JsonResponse
    {
        $tool = $this->aiTools->register($request->user()->organization_id, $request->validated());

        return (new AiToolResource($tool))->response()->setStatusCode(201);
    }

    public function show(ShowAiToolRequest $request, string $aiTool): AiToolResource
    {
        return new AiToolResource($this->aiTools->get($request->user()->organization_id, $aiTool));
    }

    public function update(UpdateAiToolRequest $request, string $aiTool): AiToolResource
    {
        return new AiToolResource($this->aiTools->update(
            $request->user()->organization_id,
            $aiTool,
            $request->validated(),
        ));
    }

    public function destroy(DeleteAiToolRequest $request, string $aiTool): JsonResponse
    {
        $this->aiTools->delete($request->user()->organization_id, $aiTool);

        return response()->json(status: 204);
    }
}

