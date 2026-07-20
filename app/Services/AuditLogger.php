<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    public static function log(string $action, string $entityType, int|string|null $entityId, array $context = []): void
    {
        $user = Auth::user();

        Log::channel('audit')->info('audit', [
            'action'       => $action,
            'entity_type'  => $entityType,
            'entity_id'    => $entityId,
            'user_id'      => $user?->id,
            'user_email'   => $user?->email,
            'user_role'    => $user?->role?->name,
            'ip'           => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'context'      => $context,
            'timestamp'    => now()->toISOString(),
        ]);
    }

    public static function created(string $entityType, int|string $entityId, array $context = []): void
    {
        self::log('created', $entityType, $entityId, $context);
    }

    public static function updated(string $entityType, int|string $entityId, array $context = []): void
    {
        self::log('updated', $entityType, $entityId, $context);
    }

    public static function deleted(string $entityType, int|string $entityId, array $context = []): void
    {
        self::log('deleted', $entityType, $entityId, $context);
    }

    public static function restored(string $entityType, int|string $entityId, array $context = []): void
    {
        self::log('restored', $entityType, $entityId, $context);
    }

    public static function forceDeleted(string $entityType, int|string $entityId, array $context = []): void
    {
        self::log('force_deleted', $entityType, $entityId, $context);
    }

    public static function closed(string $entityType, int|string $entityId, array $context = []): void
    {
        self::log('closed', $entityType, $entityId, $context);
    }

    public static function reopened(string $entityType, int|string $entityId, array $context = []): void
    {
        self::log('reopened', $entityType, $entityId, $context);
    }

    public static function login(int|string $userId, array $context = []): void
    {
        self::log('login', 'user', $userId, $context);
    }

    public static function logout(int|string $userId, array $context = []): void
    {
        self::log('logout', 'user', $userId, $context);
    }

    public static function failedLogin(string $email, array $context = []): void
    {
        Log::channel('audit')->warning('audit', [
            'action'       => 'failed_login',
            'entity_type'  => 'user',
            'entity_id'    => null,
            'email'        => $email,
            'ip'           => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'context'      => $context,
            'timestamp'    => now()->toISOString(),
        ]);
    }
}
