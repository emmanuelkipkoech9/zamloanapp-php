<?php
// Route all requests to zamloans.php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri === '/' || $uri === '/index.php' || $uri === '') {
    require __DIR__ . '/zamloans.php';
} elseif (file_exists(__DIR__ . $uri)) {
    return false;
} else {
    require __DIR__ . '/zamloans.php';
}
