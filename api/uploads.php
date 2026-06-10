<?php

declare(strict_types=1);

const APPLICATION_PHOTO_MAX_BYTES = 2 * 1024 * 1024;

function applicationUploadDir(): string
{
    return dirname(__DIR__) . '/uploads/applications';
}

function saveApplicationPhoto(array $file, string $roll): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        throw new InvalidArgumentException('Profile photo is required.');
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Could not upload photo. Please try again.');
    }

    if (($file['size'] ?? 0) > APPLICATION_PHOTO_MAX_BYTES) {
        throw new InvalidArgumentException('Photo must be 2 MB or smaller.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name'] ?: '');
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        throw new InvalidArgumentException('Photo must be JPG, PNG, or WEBP.');
    }

    $dir = applicationUploadDir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Upload directory is not writable.');
    }

    $safeRoll = preg_replace('/[^0-9]/', '', $roll) ?: 'unknown';
    $filename = sprintf('%s_%s.%s', $safeRoll, bin2hex(random_bytes(8)), $allowed[$mime]);
    $target = $dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save uploaded photo.');
    }

    return './uploads/applications/' . $filename;
}

function deleteApplicationPhoto(?string $photoPath): void
{
    deleteUploadedFile($photoPath, applicationUploadDir());
}

function appealUploadDir(): string
{
    return dirname(__DIR__) . '/uploads/appeals';
}

function saveAppealPhoto(array $file, string $slug): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Could not upload photo. Please try again.');
    }

    if (($file['size'] ?? 0) > APPLICATION_PHOTO_MAX_BYTES) {
        throw new InvalidArgumentException('Photo must be 2 MB or smaller.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name'] ?: '');
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        throw new InvalidArgumentException('Photo must be JPG, PNG, or WEBP.');
    }

    $dir = appealUploadDir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Upload directory is not writable.');
    }

    $safeSlug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($slug)) ?: 'appeal';
    $safeSlug = trim($safeSlug, '-') ?: 'appeal';
    $filename = sprintf('%s_%s.%s', $safeSlug, bin2hex(random_bytes(8)), $allowed[$mime]);
    $target = $dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save uploaded photo.');
    }

    return './uploads/appeals/' . $filename;
}

function deleteAppealPhoto(?string $photoPath): void
{
    deleteUploadedFile($photoPath, appealUploadDir());
}

function deleteUploadedFile(?string $photoPath, string $allowedDir): void
{
    if ($photoPath === null || $photoPath === '') {
        return;
    }

    $fullPath = realpath(dirname(__DIR__) . '/' . ltrim(str_replace('./', '', $photoPath), '/'));
    $uploadsRoot = realpath($allowedDir);

    if ($fullPath === false || $uploadsRoot === false) {
        return;
    }

    if (!str_starts_with($fullPath, $uploadsRoot)) {
        return;
    }

    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}
