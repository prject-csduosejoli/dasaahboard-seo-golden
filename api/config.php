<?php
/* ===== config.php — koneksi database (local Laragon) ===== */
session_start();

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'seo_dashboard');
define('DB_USER', 'root');
define('DB_PASS', '');

function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

function json_out($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function require_auth() {
    if (empty($_SESSION['user_id'])) {
        json_out(['ok' => false, 'error' => 'Unauthorized'], 401);
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf($token) {
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$token);
}