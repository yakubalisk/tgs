<?php
// Shared upload helper used by all admin upload pages
function handleImageUpload($fileKey, $subDir) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // No file — caller decides if required
    }
    if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload error code: " . $_FILES[$fileKey]['error']);
    }

    $allowed = ['image/jpeg','image/jpg','image/png','image/webp','image/gif'];
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = finfo_file($finfo, $_FILES[$fileKey]['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        throw new Exception("Invalid file type. Allowed: JPG, PNG, WEBP, GIF.");
    }
    if ($_FILES[$fileKey]['size'] > 5 * 1024 * 1024) {
        throw new Exception("File too large. Max 5 MB.");
    }

    $ext     = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    $fname   = uniqid('img_', true) . '.' . $ext;
    $dir     = __DIR__ . '/../assets/uploads/' . $subDir . '/';
    $dest    = $dir . $fname;

    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $dest)) {
        throw new Exception("Failed to save uploaded file.");
    }

    return 'assets/uploads/' . $subDir . '/' . $fname;
}

function deleteUploadedFile($path) {
    $full = __DIR__ . '/../' . ltrim($path, '/');
    if (file_exists($full) && strpos(realpath($full), realpath(__DIR__ . '/../assets/uploads')) === 0) {
        unlink($full);
    }
}
