<?php
/* ===== login.php — autentikasi server-side ===== */
require __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if ($username === '' || $password === '') {
        json_out(['ok' => false, 'error' => 'Username dan password wajib diisi.'], 422);
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Verifikasi: bcrypt (password_hash) ATAU legacy SHA-256 hex
        $hash = $user['password_hash'];
        $verified = false;
        if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$argon')) {
            $verified = password_verify($password, $hash);
        } else {
            // legacy: sha256(salt::password)
            $verified = hash_equals($hash, hash('sha256', $user['salt'] . '::' . $password));
        }

        if ($verified) {
            // Auto-upgrade: jika masih legacy (sha256), simpan sebagai bcrypt
            if (!str_starts_with($hash, '$2y$') && !str_starts_with($hash, '$argon')) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                $upd->execute([$newHash, $user['id']]);
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            json_out(['ok' => true, 'user' => ['username' => $user['username'], 'role' => $user['role']], 'csrf' => $_SESSION['csrf']]);
        }
    }

    // Rate-limit sederhana: max 5 gagal per 5 menit (session-based)
    $fails = (int)($_SESSION['login_fails'] ?? 0);
    $lockUntil = $_SESSION['login_lock_until'] ?? 0;
    if (time() < $lockUntil) {
        json_out(['ok' => false, 'error' => 'Terlalu banyak percobaan. Coba lagi dalam ' . ($lockUntil - time()) . ' detik.'], 429);
    }
    $fails++;
    $_SESSION['login_fails'] = $fails;
    if ($fails >= 5) {
        $_SESSION['login_lock_until'] = time() + 300; // 5 menit
        $_SESSION['login_fails'] = 0;
        json_out(['ok' => false, 'error' => '5x gagal. Terkunci 5 menit.'], 429);
    }
    json_out(['ok' => false, 'error' => 'Username atau password salah. (Percobaan ' . $fails . '/5)'], 401);
}

if ($method === 'GET') {
    // Cek status login
    if (!empty($_SESSION['user_id'])) {
        json_out(['ok' => true, 'logged_in' => true, 'user' => ['username' => $_SESSION['username'], 'role' => $_SESSION['role']], 'csrf' => csrf_token()]);
    }
    json_out(['ok' => true, 'logged_in' => false]);
}

json_out(['ok' => false, 'error' => 'Method not allowed'], 405);