<?php
require_once 'config/database.php';
$BASE_URL = 'http://localhost:8080/';
function requireDir($fileName) {
    // Menggunakan __DIR__ untuk mendapatkan path direktori file ini
    $filePath = __DIR__ . '/' . $fileName;

    // Memeriksa apakah file ada sebelum menyertakannya
    if (file_exists($filePath)) {
        require $filePath;
    } else {
        throw new Exception("File {$filePath} tidak ditemukan.");
    }
}

// Mengambil request URI
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/Laundry-PHP-Firebase'; // Sesuaikan dengan folder project Anda

// Menghapus base path dan query string dari URI
$path = str_replace($base_path, '', $request_uri);
$path = parse_url($path, PHP_URL_PATH);

// Definisikan routes
$routes = [
    '/' => 'pages/index.php',
    '/admin' => 'pages/admin/index.php',
    '/admin/services' => 'pages/admin/services/index.php',
    '/admin/orders' => 'pages/admin/orders/index.php'
];

// Function untuk handle dynamic routes (contoh: /product/{id})
function matchDynamicRoute($path, $routes) {
    foreach ($routes as $route => $handler) {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
        $pattern = str_replace('/', '\/', $pattern);
        if (preg_match('/^' . $pattern . '$/', $path, $matches)) {
            array_shift($matches); // Hapus full match
            $_GET['params'] = $matches; // Simpan parameter ke $_GET
            return $handler;
        }
    }
    return false;
}

// Handle routing
if (array_key_exists($path, $routes)) {
    // Static route
    require_once __DIR__ . '/' . $routes[$path];
} elseif ($handler = matchDynamicRoute($path, $routes)) {
    // Dynamic route
    require_once __DIR__ . '/' . $handler;
} else {
    // 404 Not Found
    header("HTTP/1.0 404 Not Found");
    require_once __DIR__ . '/pages/404.php';
}