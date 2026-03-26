<?php

/**
 * Diagnostic: https://yoursite.com/backend/deploy-check.php
 * If this returns 404, Apache is not reaching public/ (rewrite / docroot issue).
 * Delete this file when finished.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');

$sf = isset($_SERVER['SCRIPT_FILENAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_FILENAME']) : '';

// Same folder as index.php — either filename proves the /{segment}/public/ segment.
$prefixFromFs = preg_match('#/([^/]+)/public/(?:index\.php|deploy-check\.php)$#', $sf, $m) ? $m[1] : null;

$examplePath = '/backend/admin/login';
$simulatedInternalPath = $examplePath;
if ($prefixFromFs !== null && $prefixFromFs !== '') {
    $p = parse_url($examplePath, PHP_URL_PATH) ?: '/';
    if ($p === '/'.$prefixFromFs || str_starts_with($p, '/'.$prefixFromFs.'/')) {
        $trimmed = substr($p, strlen($prefixFromFs) + 1);
        $simulatedInternalPath = ($trimmed === '' || $trimmed === false) ? '/' : '/'.$trimmed;
    }
}

echo json_encode([
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? null,
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? null,
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? null,
    'detected_public_parent_segment' => preg_match('#/([^/]+)/public/deploy-check\.php$#', $sf, $m) ? $m[1] : null,
    'prefix_from_script_filename' => $prefixFromFs,
    'note' => 'For Laravel, public/index.php uses the same …/backend/public/ path; prefix should match prefix_from_script_filename.',
    'example_uri' => $examplePath,
    'simulated_internal_path_after_prefix_strip' => $simulatedInternalPath,
    'filament_login_expects_internal_path' => '/admin/login',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
