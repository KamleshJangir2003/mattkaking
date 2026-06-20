<?php
require_once 'auth.php';
$pageTitle = 'Dashboard';

$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_bets = $conn->query("SELECT COUNT(*) as c FROM bets WHERE bet_date=CURDATE()")->fetch_assoc()['c'];
$total_bet_amount = $conn->query("SELECT COALESCE(SUM(amount),0) as c FROM bets WHERE bet_date=CURDATE()")->fetch_assoc()['c'];
$total_win_amount = $conn->query("SELECT COALESCE(SUM(win_amount),0) as c FROM bets WHERE status='won' AND bet_date=CURDATE()")->fetch_assoc()['c'];
$pending_bets = $conn->query("SELECT COUNT(*) as c FROM bets WHERE status='pending'")->fetch_assoc()['c'];
$total_deposits = $conn->query("SELECT COALESCE(SUM(amount),0) as c FROM transactions WHERE type='deposit' AND DATE(created_at)=CURDATE()")->fetch_assoc()['c'];

$recent_bets = $conn->query("SELECT b.*,u.name,u.mobile,m.name as market_name FROM bets b JOIN users u ON b.user_id=u.id JOIN markets m ON b.market_id=m.id ORDER BY b.created_at DESC LIMIT 10");

include 'header.php';
?>

<div class="stat-grid">
  <div class="stat-box blue"><h2><?= $total_users ?></h2><p>Total Users</p></div>
  <div class="stat-box orange"><h2><?= $total_bets ?></h2><p>Today's Bets</p></div>
  <div class="stat-box red"><h2>₹<?= number_format($total_bet_amount,2) ?></h2><p>Today's Bet Amount</p></div>
  <div class="stat-box green"><h2>₹<?= number_format($total_win_amount,2) ?></h2><p>Today's Win Amount</p></div>
  <div class="stat-box"><h2><?= $pending_bets ?></h2><p>Pending Bets</p></div>
  <div class="stat-box green"><h2>₹<?= number_format($total_deposits,2) ?></h2><p>Today's Deposits</p></div>
</div>

<div class="card">
  <h3>Recent Bets (Today)</h3>
  <div style="overflow-x:auto;">
  <table>
    <thead><tr><th>#</th><th>User</th><th>Market</th><th>Type</th><th>Number</th><th>Amount</th><th>Session</th><th>Status</th><th>Time</th></tr></thead>
    <tbody>
    <?php while($b = $recent_bets->fetch_assoc()): ?>
    <tr>
      <td><?= $b['id'] ?></td>
      <td><?= htmlspecialchars($b['name']) ?><br><small><?= $b['mobile'] ?></small></td>
      <td><?= htmlspecialchars($b['market_name']) ?></td>
      <td><?= strtoupper($b['bet_type']) ?></td>
      <td><strong><?= $b['number'] ?></strong></td>
      <td>₹<?= $b['amount'] ?></td>
      <td><?= strtoupper($b['session']) ?></td>
      <td><span class="badge badge-<?= $b['status'] ?>"><?= strtoupper($b['status']) ?></span></td>
      <td><?= date('h:i A', strtotime($b['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  </div>
</div>

<?php include 'footer.php'; ?>
