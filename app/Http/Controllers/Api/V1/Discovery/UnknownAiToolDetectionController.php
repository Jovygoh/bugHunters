<?php

namespace App\Http\Controllers\Api\V1\Discovery;

use App\Application\Discovery\Services\DiscoveryFindingService;
use App\Application\Discovery\Services\UnknownAiToolDetectionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Discovery\IndexUnknownAiToolDetectionRequest;
use App\Http\Requests\Api\V1\Discovery\ShowUnknownAiToolDetectionRequest;
use App\Http\Requests\Api\V1\Discovery\StoreUnknownAiToolDetectionRequest;
use App\Http\Requests\Api\V1\Discovery\UpdateUnknownAiToolDetectionStatusRequest;
use App\Http\Resources\Api\V1\UnknownAiToolDetectionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class UnknownAiToolDetectionController extends Controller
{
    public function index(
        IndexUnknownAiToolDetectionRequest $request,
        DiscoveryFindingService $findings,
    ): AnonymousResourceCollection {
        return UnknownAiToolDetectionResource::collection($findings->list(
            $request->user()->organization_id,
            $request->filters(),
            $request->integer('per_page', 25),
        ));
    }

    public function store(
        StoreUnknownAiToolDetectionRequest $request,
        UnknownAiToolDetectionService $detections,
    ): JsonResponse {
        $finding = $detections->detect($request->user()->organization_id, $request->validated());

        return (new UnknownAiToolDetectionResource($finding))->response()->setStatusCode(201);
    }

    public function show(
        ShowUnknownAiToolDetectionRequest $request,
        string $finding,
        DiscoveryFindingService $findings,
    ): UnknownAiToolDetectionResource {
        return new UnknownAiToolDetectionResource(
            $findings->get($request->user()->organization_id, $finding)
        );
    }

    public function updateStatus(
        UpdateUnknownAiToolDetectionStatusRequest $request,
        string $finding,
        DiscoveryFindingService $findings,
    ): UnknownAiToolDetectionResource {
        $data = $request->validated();

        $updated = $data['status'] === 'resolved'
            ? $findings->resolve(
                $request->user()->organization_id,
                $finding,
                $request->user()->getKey(),
                $data['resolution_code'],
                $data['resolution_notes'] ?? null,
            )
            : $findings->changeStatus($request->user()->organization_id, $finding, $data['status']);

        return new UnknownAiToolDetectionResource($updated);
    }
}

