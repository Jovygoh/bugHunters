<?php

namespace App\Http\Controllers\Api\V1\Incidents;

use App\Application\Incidents\Services\IncidentManagementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Incidents\IndexIncidentRequest;
use App\Http\Requests\Api\V1\Incidents\StoreIncidentRequest;
use App\Http\Requests\Api\V1\Incidents\TransitionIncidentRequest;
use App\Http\Requests\Api\V1\Incidents\UpdateIncidentRequest;
use App\Http\Requests\Api\V1\Incidents\ViewIncidentRequest;
use App\Http\Resources\Api\V1\IncidentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class IncidentController extends Controller
{
    public function __construct(private readonly IncidentManagementService $incidents) {}

    public function index(IndexIncidentRequest $request): AnonymousResourceCollection
    {
        return IncidentResource::collection($this->incidents->list(
            $request->user()->organization_id,
            $request->filters(),
            $request->integer('per_page', 25),
        ));
    }

    public function store(StoreIncidentRequest $request): JsonResponse
    {
        $incident = $this->incidents->create($request->user()->organization_id, $request->validated() + [
            'actor_user_id' => $request->user()->getKey(),
        ]);

        return (new IncidentResource($incident))->response()->setStatusCode(201);
    }

    public function show(ViewIncidentRequest $request, string $incident): IncidentResource
    {
        return new IncidentResource($this->incidents->get($request->user()->organization_id, $incident));
    }

    public function update(UpdateIncidentRequest $request, string $incident): IncidentResource
    {
        return new IncidentResource($this->incidents->update(
            $request->user()->organization_id,
            $incident,
            $request->validated(),
        ));
    }

    public function transition(TransitionIncidentRequest $request, string $incident): IncidentResource
    {
        return new IncidentResource($this->incidents->transition(
            $request->user()->organization_id,
            $incident,
            $request->validated('status'),
            $request->user()->getKey(),
        ));
    }
}

