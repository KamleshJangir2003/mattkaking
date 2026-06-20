<?php
require_once 'auth.php';
$pageTitle = 'User Bets';
$uid = (int)($_GET['user_id'] ?? 0);
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
if (!$user) { header("Location: users.php"); exit; }

$bets = $conn->query("SELECT b.*,m.name as market_name FROM bets b JOIN markets m ON b.market_id=m.id WHERE b.user_id=$uid ORDER BY b.created_at DESC LIMIT 50");
include 'header.php';
?>
<div class="card">
  <h3>Bets of <?= htmlspecialchars($user['name']) ?> (<?= $user['mobile'] ?>)</h3>
  <p>Balance: <strong>₹<?= number_format($user['balance'],2) ?></strong></p><br>
  <table>
    <thead><tr><th>#</th><th>Market</th><th>Type</th><th>Number</th><th>Amt</th><th>Session</th><th>Status</th><th>Win Amt</th><th>Date</th></tr></thead>
    <tbody>
    <?php while($b = $bets->fetch_assoc()): ?>
    <tr>
      <td><?= $b['id'] ?></td>
      <td style="font-size:11px;"><?= htmlspecialchars($b['market_name']) ?></td>
      <td><?= strtoupper(str_replace('_',' ',$b['bet_type'])) ?></td>
      <td><strong><?= $b['number'] ?></strong></td>
      <td>₹<?= $b['amount'] ?></td>
      <td><?= strtoupper($b['session']) ?></td>
      <td><span class="badge badge-<?= $b['status'] ?>"><?= strtoupper($b['status']) ?></span></td>
      <td><?= $b['win_amount'] > 0 ? '₹'.$b['win_amount'] : '-' ?></td>
      <td style="font-size:11px;"><?= date('d M Y', strtotime($b['bet_date'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php include 'footer.php'; ?>
