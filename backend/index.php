<?php

/**
 * Some hosts run this file for /backend/* instead of public/index.php. PHP then sets
 * SCRIPT_NAME=/…/backend/index.php, so Symfony/Laravel compute the wrong path and every
 * route 404s. Point $_SERVER at public/index.php like a normal Laravel install.
 */
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
if (preg_match('#^/([^/]+)/index\.php$#', $scriptName, $m) && ($m[1] ?? '') !== 'public') {
    $prefix = $m[1];
    $publicIndex = __DIR__.'/public/index.php';
    $_SERVER['SCRIPT_FILENAME'] = $publicIndex;
    $_SERVER['SCRIPT_NAME'] = '/'.$prefix.'/public/index.php';
    $_SERVER['PHP_SELF'] = '/'.$prefix.'/public/index.php';
}

require __DIR__.'/public/index.php';
