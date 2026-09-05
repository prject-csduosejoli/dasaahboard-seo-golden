<?php
/* ===== change-password.php — ganti password (server-side) ===== */
require __DIR__ . '/config.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$oldPass = $input['old_password'] ?? '';
$newPass = $input['new_password'] ?? '';

if (strlen($newPass) < 6) {
    json_out(['ok' => false, 'error' => 'Password baru minimal 6 karakter.'], 422);
}
if ($oldPass === '' || $newPass === '') {
    json_out(['ok' => false, 'error' => 'Semua field wajib diisi.'], 422);
}

$db = db();
$stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    json_out(['ok' => false, 'error' => 'User tidak ditemukan.'], 404);
}

// Verify old password (bcrypt or legacy sha256)
$ok = false;
if (str_starts_with($user['password_hash'], '$2y$') || str_starts_with($user['password_hash'], '$argon')) {
    $ok = password_verify($oldPass, $user['password_hash']);
} else {
    $ok = hash_equals($user['password_hash'], hash('sha256', $user['salt'] . '::' . $oldPass));
}

if (!$ok) {
    json_out(['ok' => false, 'error' => 'Password lama salah.'], 401);
}

// Update to bcrypt
$newHash = password_hash($newPass, PASSWORD_DEFAULT);
$upd = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
$upd->execute([$newHash, $_SESSION['user_id']]);

// Invalidate other sessions (optional: regenerate session id)
session_regenerate_id(true);
json_out(['ok' => true, 'message' => 'Password berhasil diganti.']);