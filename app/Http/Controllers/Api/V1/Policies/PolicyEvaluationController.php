<?php

namespace App\Http\Controllers\Api\V1\Policies;

use App\Application\Policies\Services\EvaluatePolicyService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Policies\EvaluatePolicyRequest;
use App\Http\Resources\Api\V1\PolicyEvaluationResource;
use Illuminate\Http\JsonResponse;

final class PolicyEvaluationController extends Controller
{
    public function store(EvaluatePolicyRequest $request, EvaluatePolicyService $service): JsonResponse
    {
        $evaluation = $service->execute($request->user()->organization_id, $request->validated());

        return (new PolicyEvaluationResource($evaluation))->response()->setStatusCode(201);
    }
}

