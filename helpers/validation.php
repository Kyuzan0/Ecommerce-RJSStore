<?php
/**
 * Validation helper functions for RJSStore MVC
 */

function validate_required($value): bool
{
    return $value !== null && $value !== '';
}

function validate_email(string $value): bool
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_min_length(string $value, int $min): bool
{
    return mb_strlen($value) >= $min;
}

function validate_numeric($value): bool
{
    return is_numeric($value);
}

function validate_in($value, array $allowed): bool
{
    return in_array($value, $allowed, true);
}

function validate_file_type(array $file, array $allowedTypes): bool
{
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    return in_array($mime, $allowedTypes, true);
}

function validate_file_size(array $file, int $maxBytes): bool
{
    return isset($file['size']) && $file['size'] <= $maxBytes;
}

function validate_confirm(string $value, string $confirm): bool
{
    return $value === $confirm;
}
