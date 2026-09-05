<?php
/* ===== import.php — upload file GSC (.xlsx) lalu simpan ke DB =====
   Format: JSON { data: { meta, daily[], queries[], pages[], countries[], devices[] } }
   Frontend yang parse xlsx (via JSZip/XLSX) lalu kirim JSON ke sini.
*/
require __DIR__ . '/config.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['data'])) {
    json_out(['ok' => false, 'error' => 'Data kosong / format salah.'], 422);
}

$data = $input['data'];
$db = db();

/* --- meta --- */
$meta = $data['meta'] ?? [];
$stmt = $db->prepare("INSERT INTO meta (source, export_date, sites) VALUES (?,?,?)");
$stmt->execute([
    $meta['source'] ?? 'goldenstudio.odoo.com',
    $meta['export'] ?? date('Y-m-d'),
    $meta['sites'] ?? 1,
]);

/* --- replace data lama (periodik baru) --- */
// Hanya TRUNCATE tabel yang datanya dikirim (jangan hapus tabel lain)
if (!empty($data['daily']))    $db->exec("TRUNCATE TABLE daily;");
if (!empty($data['queries']))  $db->exec("TRUNCATE TABLE queries;");
if (!empty($data['pages']))    $db->exec("TRUNCATE TABLE pages;");
if (!empty($data['countries']))$db->exec("TRUNCATE TABLE countries;");
if (!empty($data['devices']))  $db->exec("TRUNCATE TABLE devices;");

/* --- daily --- */
if (!empty($data['daily'])) {
    $stmt = $db->prepare("INSERT INTO daily (d, clk, imp, ctr, pos) VALUES (?,?,?,?,?)");
    foreach ($data['daily'] as $r) {
        $stmt->execute([$r['d'], $r['clk'], $r['imp'], $r['ctr'], $r['pos']]);
    }
}

/* --- queries --- */
if (!empty($data['queries'])) {
    $stmt = $db->prepare("INSERT INTO queries (q, clk, imp, ctr, pos) VALUES (?,?,?,?,?)");
    foreach ($data['queries'] as $r) {
        $stmt->execute([$r['q'], $r['clk'], $r['imp'], $r['ctr'], $r['pos']]);
    }
}

/* --- pages --- */
if (!empty($data['pages'])) {
    $stmt = $db->prepare("INSERT INTO pages (p, clk, imp, ctr, pos) VALUES (?,?,?,?,?)");
    foreach ($data['pages'] as $r) {
        $stmt->execute([$r['p'] ?? ($r['q'] ?? ''), $r['clk'] ?? 0, $r['imp'] ?? 0, $r['ctr'] ?? 0, $r['pos'] ?? 0]);
    }
}

/* --- countries --- */
if (!empty($data['countries'])) {
    $stmt = $db->prepare("INSERT INTO countries (c, clk, imp, ctr, pos) VALUES (?,?,?,?,?)");
    foreach ($data['countries'] as $r) {
        $stmt->execute([$r['c'] ?? ($r['q'] ?? ''), $r['clk'] ?? 0, $r['imp'] ?? 0, $r['ctr'] ?? 0, $r['pos'] ?? 0]);
    }
}

/* --- devices --- */
if (!empty($data['devices'])) {
    $stmt = $db->prepare("INSERT INTO devices (d, clk, imp, ctr, pos) VALUES (?,?,?,?,?)");
    foreach ($data['devices'] as $r) {
        $stmt->execute([$r['q'], $r['clk'], $r['imp'], $r['ctr'], $r['pos']]);
    }
}

$counts = [
    'daily' => count($data['daily'] ?? []),
    'queries' => count($data['queries'] ?? []),
    'pages' => count($data['pages'] ?? []),
    'countries' => count($data['countries'] ?? []),
    'devices' => count($data['devices'] ?? []),
];

json_out(['ok' => true, 'message' => 'Data GSC berhasil diimport ke database.', 'counts' => $counts]);