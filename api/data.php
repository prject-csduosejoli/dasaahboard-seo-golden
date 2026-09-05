<?php
/* ===== data.php — endpoint data dashboard (semua tabel) ===== */
require __DIR__ . '/config.php';
require_auth();

$period = $_GET['period'] ?? 'all'; // all | 7d | 28d | 90d

$db = db();

// Meta
$meta = $db->query("SELECT * FROM meta ORDER BY id DESC LIMIT 1")->fetch();

// Daily trend (filter period)
$dailyCond = '';
if ($period === '7d')  $dailyCond = "WHERE d >= DATE_SUB((SELECT MAX(d) FROM daily), INTERVAL 7 DAY)";
if ($period === '28d') $dailyCond = "WHERE d >= DATE_SUB((SELECT MAX(d) FROM daily), INTERVAL 28 DAY)";
if ($period === '90d') $dailyCond = "WHERE d >= DATE_SUB((SELECT MAX(d) FROM daily), INTERVAL 90 DAY)";
$daily = $db->query("SELECT d, clk, imp, ctr, pos FROM daily $dailyCond ORDER BY d")->fetchAll();

// Summary
$sum = $db->query("
    SELECT
      SUM(clk) AS clk, SUM(imp) AS imp,
      ROUND(IFNULL(SUM(clk)/NULLIF(SUM(imp),0)*100,0), 2) AS ctr,
      ROUND(AVG(pos), 2) AS pos
    FROM daily $dailyCond
")->fetch();

// Queries top
$queries = $db->query("
    SELECT q, clk, imp, ctr, pos FROM queries
    ORDER BY clk DESC, imp DESC LIMIT 200
")->fetchAll();

// Pages top
$pages = $db->query("
    SELECT p, clk, imp, ctr, pos FROM pages
    ORDER BY clk DESC, imp DESC LIMIT 100
")->fetchAll();

// Countries
$countries = $db->query("
    SELECT c, clk, imp, ctr, pos FROM countries
    ORDER BY clk DESC LIMIT 50
")->fetchAll();

// Devices
$devices = $db->query("
    SELECT d, clk, imp, ctr, pos FROM devices
    ORDER BY clk DESC
")->fetchAll();

json_out([
    'ok' => true,
    'meta' => $meta,
    'summary' => $sum,
    'daily' => $daily,
    'queries' => $queries,
    'pages' => $pages,
    'countries' => $countries,
    'devices' => $devices,
    'period' => $period,
]);