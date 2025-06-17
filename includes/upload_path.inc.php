<?php
// Helper for building and resolving upload paths

declare(strict_types=1);

/**
 * Returns the base directory for uploads or null if missing.
 */
function get_upload_base_path(): ?string
{
    $base = realpath(__DIR__ . '/../uploads');
    return $base === false ? null : $base;
}

/**
 * Builds the relative path for an upload.
 * If the stored name already contains a groups/ prefix it is returned as-is.
 */
function build_upload_relative_path(string $storedName, ?int $groupId): string
{
    if (strpos($storedName, 'groups/') === 0) {
        return ltrim($storedName, '/');
    }
    if ($groupId !== null) {
        return 'groups/' . $groupId . '/' . ltrim($storedName, '/');
    }
    return ltrim($storedName, '/');
}

/**
 * Resolves the absolute path for an upload.
 * Returns null if the base directory cannot be located.
 */
function resolve_upload_path(string $storedName, ?int $groupId): ?string
{
    $base = get_upload_base_path();
    if ($base === null) {
        return null;
    }
    return $base . '/' . build_upload_relative_path($storedName, $groupId);
}
