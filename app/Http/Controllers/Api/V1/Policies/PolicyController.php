<?php

namespace App\Http\Controllers\Api\V1\Policies;

use App\Application\Policies\Services\PolicyManagementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Policies\IndexPolicyRequest;
use App\Http\Requests\Api\V1\Policies\ManagePolicyRequest;
use App\Http\Requests\Api\V1\Policies\PublishPolicyRequest;
use App\Http\Requests\Api\V1\Policies\StorePolicyRequest;
use App\Http\Requests\Api\V1\Policies\UpdatePolicyRequest;
use App\Http\Requests\Api\V1\Policies\ViewPolicyRequest;
use App\Http\Resources\Api\V1\PolicyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PolicyController extends Controller
{
    public function __construct(private readonly PolicyManagementService $policies) {}

    public function index(IndexPolicyRequest $request): AnonymousResourceCollection
    {
        return PolicyResource::collection($this->policies->list(
            $request->user()->organization_id,
            $request->filters(),
            $request->integer('per_page', 25),
        ));
    }

    public function store(StorePolicyRequest $request): JsonResponse
    {
        $policy = $this->policies->create($request->user()->organization_id, $request->validated() + [
            'actor_user_id' => $request->user()->getKey(),
        ]);

        return (new PolicyResource($policy))->response()->setStatusCode(201);
    }

    public function show(ViewPolicyRequest $request, string $policy): PolicyResource
    {
        return new PolicyResource($this->policies->get($request->user()->organization_id, $policy));
    }

    public function update(UpdatePolicyRequest $request, string $policy): PolicyResource
    {
        return new PolicyResource($this->policies->update(
            $request->user()->organization_id,
            $policy,
            $request->validated() + ['actor_user_id' => $request->user()->getKey()],
        ));
    }

    public function destroy(ManagePolicyRequest $request, string $policy): JsonResponse
    {
        $this->policies->delete($request->user()->organization_id, $policy);

        return response()->json(status: 204);
    }

    public function publish(PublishPolicyRequest $request, string $policy): PolicyResource
    {
        return new PolicyResource($this->policies->publish(
            $request->user()->organization_id,
            $policy,
            $request->user()->getKey(),
        ));
    }
}

