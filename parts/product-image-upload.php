<?php
declare(strict_types=1);

function product_image_extension(array $upload): ?string
{
    $error = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) return null;
    if ($error !== UPLOAD_ERR_OK) throw new RuntimeException('Image upload failed. Please choose a file up to 8 MB.');

    $path = $upload['tmp_name'] ?? '';
    $size = $upload['size'] ?? 0;
    if (!is_string($path) || !is_file($path) || !is_int($size) || $size < 1 || $size > 8 * 1024 * 1024) {
        throw new RuntimeException('Choose a JPG, PNG or WebP image up to 8 MB.');
    }

    $details = @getimagesize($path);
    if ($details === false || $details[0] < 1 || $details[1] < 1 || $details[0] * $details[1] > 24000000) {
        throw new RuntimeException('Choose a valid image with no more than 24 megapixels.');
    }

    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    $extension = $types[$details[2]] ?? null;
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    if ($extension === null || $mime !== ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][$extension]) {
        throw new RuntimeException('Only JPG, PNG and WebP images are supported.');
    }
    return $extension;
}

function save_product_image(array $upload, int $productId, string $extension): string
{
    $source = $upload['tmp_name'] ?? '';
    if (!is_string($source) || !is_uploaded_file($source)) throw new RuntimeException('Invalid image upload.');

    $directory = __DIR__ . '/../assets/images/' . $productId;
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Could not create the product image folder.');
    }
    $filename = 'product-' . $productId . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $directory . '/' . $filename;
    if (!move_uploaded_file($source, $destination)) throw new RuntimeException('Could not save the product image.');
    return 'assets/images/' . $productId . '/' . $filename;
}

function remove_replaced_product_image(?string $path, int $productId): void
{
    if ($path === null || !preg_match('~^assets/images/' . $productId . '/product-' . $productId . '-[a-f0-9]{16}\\.(?:jpg|png|webp)$~', $path)) return;
    $absolute = __DIR__ . '/../' . $path;
    if (is_file($absolute)) @unlink($absolute);
}
