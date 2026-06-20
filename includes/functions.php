<?php
function getMarkets($conn) {
    return $conn->query("SELECT * FROM markets WHERE status='active' ORDER BY id");
}

function getUserBalance($conn, $user_id) {
    $r = $conn->query("SELECT balance FROM users WHERE id=$user_id");
    return $r->fetch_assoc()['balance'] ?? 0;
}

function addTransaction($conn, $user_id, $type, $amount, $note='') {
    $conn->query("INSERT INTO transactions (user_id,type,amount,note) VALUES ($user_id,'$type',$amount,'$note')");
}

function updateBalance($conn, $user_id, $amount, $action='add') {
    $op = $action === 'add' ? '+' : '-';
    $conn->query("UPDATE users SET balance=balance$op$amount WHERE id=$user_id");
}

function getWinRatio($conn, $bet_type) {
    $r = $conn->query("SELECT ratio FROM win_ratios WHERE bet_type='$bet_type'");
    return $r->fetch_assoc()['ratio'] ?? 1;
}
?>
