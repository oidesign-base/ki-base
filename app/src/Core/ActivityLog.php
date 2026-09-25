<?php

declare(strict_types=1);

namespace App\Core;

/** Writes to activity_log: who did what, to which record, what changed. */
final class ActivityLog
{
    /**
     * @param array|null $changes e.g. ['status' => ['received', 'identified']]
     * @param int|null|false $userId false = current user (default), null = system
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $changes = null,
        int|null|false $userId = false,
    ): void {
        if ($userId === false) {
            $userId = Auth::id();
        }

        Database::query(
            'INSERT INTO activity_log (user_id, action, entity_type, entity_id, changes, ip)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $userId,
                $action,
                $entityType,
                $entityId,
                $changes === null ? null : json_encode($changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                PHP_SAPI === 'cli' ? null : ($_SERVER['REMOTE_ADDR'] ?? null),
            ]
        );
    }
}
