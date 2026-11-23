<?php
function save_upload(string $field, string $directory, array $allowedTypes, int $maxBytes = 5242880): string
{
    $file = $_FILES[$field] ?? null;
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Please upload a valid file (maximum 5 MB).');
    }
    if (!is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name']) || $file['size'] > $maxBytes) {
        throw new RuntimeException('The upload is invalid or exceeds 5 MB.');
    }
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowedTypes[$extension]) || !in_array($mime, $allowedTypes[$extension], true)) {
        throw new RuntimeException('The file type is not allowed.');
    }
    $path = __DIR__ . '/' . $directory;
    if (!is_dir($path) && !mkdir($path, 0755, true)) {
        throw new RuntimeException('Unable to create the upload folder.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], $path . '/' . $name)) {
        throw new RuntimeException('Unable to save the uploaded file.');
    }
    return $name;
}

function image_upload_types(): array
{
    return ['jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png']];
}
