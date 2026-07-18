<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Application\Authentication\Services\CurrentUserService;
use App\Application\Authentication\Services\LoginService;
use App\Application\Authentication\Services\LogoutService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\CurrentUserRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\LogoutRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class AuthController extends Controller
{
    public function login(LoginRequest $request, LoginService $service): JsonResponse
    {
        $credentials = $request->credentials();

        return response()->json([
            'data' => $service->execute(
                $credentials['organization_id'],
                $credentials['email'],
                $credentials['password'],
                $credentials['device_name'],
            ),
        ]);
    }

    public function logout(LogoutRequest $request, LogoutService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $service->execute($user);

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(CurrentUserRequest $request, CurrentUserService $service): JsonResponse
    {
        /** @var User $authenticated */
        $authenticated = $request->user();
        $user = $service->execute($authenticated->getKey());

        return response()->json([
            'data' => [
                'id' => $user->id,
                'organization_id' => $user->organization_id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $user->roles->pluck('code')->values(),
                'employee' => $user->employee,
            ],
        ]);
    }
}

