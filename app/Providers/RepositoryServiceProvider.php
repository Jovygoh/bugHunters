<?php

namespace App\Providers;

use App\Domain\AiTools\Repositories\AiToolRepositoryInterface;
use App\Domain\Audit\Repositories\AuditLogRepositoryInterface;
use App\Domain\Authentication\Repositories\UserRepositoryInterface;
use App\Domain\Authentication\Repositories\AuthTokenRepositoryInterface;
use App\Domain\Dashboard\Repositories\DashboardRepositoryInterface;
use App\Domain\DataClassification\Repositories\DataClassificationRepositoryInterface;
use App\Domain\Departments\Repositories\DepartmentRepositoryInterface;
use App\Domain\Devices\Repositories\DeviceRepositoryInterface;
use App\Domain\Discovery\Repositories\DiscoveryRepositoryInterface;
use App\Domain\Employees\Repositories\EmployeeRepositoryInterface;
use App\Domain\Incidents\Repositories\IncidentRepositoryInterface;
use App\Domain\Notifications\Repositories\NotificationRepositoryInterface;
use App\Domain\Organizations\Repositories\OrganizationRepositoryInterface;
use App\Domain\Policies\Repositories\PolicyRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentAiToolRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentAuditLogRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDashboardRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDataClassificationRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDepartmentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDeviceRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDiscoveryRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentEmployeeRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentIncidentRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentNotificationRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentOrganizationRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPolicyRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentAuthTokenRepository;
use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

final class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public $bindings = [
        AuthTokenRepositoryInterface::class => EloquentAuthTokenRepository::class,
        UserRepositoryInterface::class => EloquentUserRepository::class,
        OrganizationRepositoryInterface::class => EloquentOrganizationRepository::class,
        EmployeeRepositoryInterface::class => EloquentEmployeeRepository::class,
        DepartmentRepositoryInterface::class => EloquentDepartmentRepository::class,
        DeviceRepositoryInterface::class => EloquentDeviceRepository::class,
        AiToolRepositoryInterface::class => EloquentAiToolRepository::class,
        DiscoveryRepositoryInterface::class => EloquentDiscoveryRepository::class,
        DataClassificationRepositoryInterface::class => EloquentDataClassificationRepository::class,
        PolicyRepositoryInterface::class => EloquentPolicyRepository::class,
        IncidentRepositoryInterface::class => EloquentIncidentRepository::class,
        NotificationRepositoryInterface::class => EloquentNotificationRepository::class,
        DashboardRepositoryInterface::class => EloquentDashboardRepository::class,
        AuditLogRepositoryInterface::class => EloquentAuditLogRepository::class,
    ];

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
