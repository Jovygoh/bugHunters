<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Employees\EmployeeController;
use App\Http\Controllers\Api\V1\Departments\DepartmentController;
use App\Http\Controllers\Api\V1\Devices\DeviceController;
use App\Http\Controllers\Api\V1\AiTools\AiToolController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->name('api.v1.auth.')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware('auth:sanctum')
    ->group(function (): void {
        Route::apiResource('employees', EmployeeController::class)
            ->whereUuid('employee');
        Route::apiResource('departments', DepartmentController::class)
            ->whereUuid('department');
        Route::post('devices/{device}/heartbeat', [DeviceController::class, 'heartbeat'])
            ->whereUuid('device')
            ->name('devices.heartbeat');
        Route::post('devices/{device}/verify', [DeviceController::class, 'verify'])
            ->whereUuid('device')
            ->name('devices.verify');
        Route::apiResource('devices', DeviceController::class)
            ->whereUuid('device');
        Route::apiResource('ai-tools', AiToolController::class)
            ->parameters(['ai-tools' => 'aiTool'])
            ->whereUuid('aiTool');
    });
