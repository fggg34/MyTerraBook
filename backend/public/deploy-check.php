<?php

/**
 * Temporary diagnostic: open https://yoursite.com/backend/deploy-check.php
 * If this returns 404, Apache is not reaching public/ (rewrite / docroot issue).
 * Delete this file when finished.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');

$sf = isset($_SERVER['SCRIPT_FILENAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_FILENAME']) : '';

echo json_encode([
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? null,
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? null,
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? null,
    'detected_public_parent_segment' => preg_match('#/([^/]+)/public/deploy-check\.php$#', $sf, $m) ? $m[1] : null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
