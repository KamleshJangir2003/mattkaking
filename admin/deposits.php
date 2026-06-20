<?php
require_once 'auth.php';
$pageTitle = 'Deposits';

$transactions = $conn->query("SELECT t.*,u.name,u.mobile FROM transactions t JOIN users u ON t.user_id=u.id WHERE t.type IN ('deposit','withdraw') ORDER BY t.created_at DESC LIMIT 100");
include 'header.php';
?>
<div class="card">
  <h3>Deposit / Withdraw History</h3>
  <div style="overflow-x:auto;">
  <table>
    <thead><tr><th>#</th><th>User</th><th>Type</th><th>Amount</th><th>Note</th><th>Date</th></tr></thead>
    <tbody>
    <?php while($t = $transactions->fetch_assoc()): ?>
    <tr>
      <td><?= $t['id'] ?></td>
      <td><?= htmlspecialchars($t['name']) ?><br><small><?= $t['mobile'] ?></small></td>
      <td><span class="badge <?= $t['type']=='deposit'?'badge-won':'badge-lost' ?>"><?= strtoupper($t['type']) ?></span></td>
      <td><strong>₹<?= number_format($t['amount'],2) ?></strong></td>
      <td style="font-size:12px;"><?= htmlspecialchars($t['note']) ?></td>
      <td style="font-size:11px;"><?= date('d M Y h:i A', strtotime($t['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  </div>
</div>
<?php include 'footer.php'; ?>
