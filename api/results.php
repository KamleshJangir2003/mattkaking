<?php
// API: Get all market results
// URL: /api/results.php
// Returns JSON with all active markets and their results

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/db.php';

$markets = $conn->query("SELECT name, slug, open_time, close_time, result FROM markets WHERE status='active' ORDER BY id");
$data = [];
while ($m = $markets->fetch_assoc()) {
    $data[] = $m;
}

echo json_encode(['status' => 'success', 'date' => date('Y-m-d'), 'markets' => $data]);
?>
