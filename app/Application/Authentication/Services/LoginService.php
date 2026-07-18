<?php

namespace App\Application\Authentication\Services;

use App\Domain\Authentication\Repositories\AuthTokenRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Carbon;

final readonly class LoginService
{
    private const ROLE_ABILITIES = [
        'security_admin' => [
            'dashboard:view', 'employees:view', 'employees:manage', 'departments:view', 'departments:manage',
            'devices:view', 'devices:manage', 'ai-tools:view', 'ai-tools:manage',
            'discovery:view', 'discovery:manage', 'classifications:view',
            'classifications:manage', 'policies:view', 'policies:manage',
            'policies:publish', 'incidents:view', 'incidents:manage',
            'notifications:view', 'audit:view',
        ],
        'manager' => [
            'dashboard:view', 'employees:view', 'departments:view', 'devices:view',
            'ai-tools:view', 'incidents:view', 'notifications:view',
        ],
        'employee' => [
            'profile:view', 'devices:view-own', 'ai-tools:view',
            'notifications:view',
        ],
    ];

    public function __construct(
        private AuthenticateUserService $authenticate,
        private AuthTokenRepositoryInterface $tokens,
    ) {}

    public function execute(string $organizationId, string $email, string $password, string $deviceName): array
    {
        $user = $this->authenticate->execute($organizationId, $email, $password);
        $abilities = $this->abilitiesFor($user);
        $expiration = config('sanctum.expiration');
        $expiresAt = is_numeric($expiration) ? Carbon::now()->addMinutes((int) $expiration) : null;
        $accessToken = $this->tokens->issue($user, $deviceName, $abilities, $expiresAt);

        return [
            'user' => $user,
            'token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
            'expires_at' => $expiresAt?->toISOString(),
        ];
    }

    private function abilitiesFor(User $user): array
    {
        $roles = $user->roles->pluck('code');

        if ($roles->contains('super_admin')) {
            return ['*'];
        }

        return $roles
            ->flatMap(fn (string $role): array => self::ROLE_ABILITIES[$role] ?? [])
            ->unique()
            ->values()
            ->all();
    }
}
