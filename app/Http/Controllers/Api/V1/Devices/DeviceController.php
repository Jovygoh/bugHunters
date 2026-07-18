<?php

namespace App\Http\Controllers\Api\V1\Devices;

use App\Application\Devices\Services\DeviceRegistrationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Devices\DeleteDeviceRequest;
use App\Http\Requests\Api\V1\Devices\HeartbeatDeviceRequest;
use App\Http\Requests\Api\V1\Devices\IndexDeviceRequest;
use App\Http\Requests\Api\V1\Devices\ShowDeviceRequest;
use App\Http\Requests\Api\V1\Devices\StoreDeviceRequest;
use App\Http\Requests\Api\V1\Devices\UpdateDeviceRequest;
use App\Http\Requests\Api\V1\Devices\VerifyDeviceRequest;
use App\Http\Resources\Api\V1\DeviceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class DeviceController extends Controller
{
    public function __construct(private readonly DeviceRegistrationService $devices) {}

    public function index(IndexDeviceRequest $request): AnonymousResourceCollection
    {
        return DeviceResource::collection($this->devices->list(
            $request->user()->organization_id,
            $request->filters(),
            $request->integer('per_page', 25),
        ));
    }

    public function store(StoreDeviceRequest $request): JsonResponse
    {
        $device = $this->devices->execute($request->user()->organization_id, $request->validated());

        return (new DeviceResource($device))->response()->setStatusCode(201);
    }

    public function show(ShowDeviceRequest $request, string $device): DeviceResource
    {
        return new DeviceResource($this->devices->get($request->user()->organization_id, $device));
    }

    public function update(UpdateDeviceRequest $request, string $device): DeviceResource
    {
        return new DeviceResource($this->devices->update(
            $request->user()->organization_id,
            $device,
            $request->validated(),
        ));
    }

    public function destroy(DeleteDeviceRequest $request, string $device): JsonResponse
    {
        $this->devices->delete($request->user()->organization_id, $device);

        return response()->json(status: 204);
    }

    public function heartbeat(HeartbeatDeviceRequest $request, string $device): DeviceResource
    {
        return new DeviceResource($this->devices->heartbeat(
            $request->user()->organization_id,
            $device,
            $request->validated(),
        ));
    }

    public function verify(VerifyDeviceRequest $request, string $device): DeviceResource
    {
        return new DeviceResource($this->devices->verify($request->user()->organization_id, $device));
    }
}

