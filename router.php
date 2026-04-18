<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/admin' || $uri === '/admin.php') {
    require __DIR__ . '/admin.php';
} elseif ($uri === '/track.php' || $uri === '/track') {
    require __DIR__ . '/track.php';
} elseif (file_exists(__DIR__ . $uri) && $uri !== '/') {
    return false;
} else {
    require __DIR__ . '/zamloans.php';
}
