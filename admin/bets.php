<?php
require_once 'auth.php';
$pageTitle = 'Bets';

$date = isset($_GET['date']) ? sanitize($conn, $_GET['date']) : date('Y-m-d');
$market_filter = isset($_GET['market']) ? (int)$_GET['market'] : 0;
$status_filter = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : '';

$where = "WHERE b.bet_date='$date'";
if ($market_filter) $where .= " AND b.market_id=$market_filter";
if ($status_filter) $where .= " AND b.status='$status_filter'";

$bets = $conn->query("SELECT b.*,u.name,u.mobile,m.name as market_name FROM bets b JOIN users u ON b.user_id=u.id JOIN markets m ON b.market_id=m.id $where ORDER BY b.created_at DESC");
$markets = $conn->query("SELECT id,name FROM markets ORDER BY name");

$stats = $conn->query("SELECT COUNT(*) as total, SUM(amount) as total_amt, SUM(CASE WHEN status='won' THEN win_amount ELSE 0 END) as win_amt FROM bets b $where")->fetch_assoc();

include 'header.php';
?>

<div class="card">
  <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
    <div class="form-group" style="margin:0;min-width:150px;">
      <label>Date</label>
      <input type="date" name="date" value="<?= $date ?>">
    </div>
    <div class="form-group" style="margin:0;min-width:180px;">
      <label>Market</label>
      <select name="market">
        <option value="">All Markets</option>
        <?php while($m = $markets->fetch_assoc()): ?>
        <option value="<?= $m['id'] ?>" <?= $market_filter==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="form-group" style="margin:0;">
      <label>Status</label>
      <select name="status">
        <option value="">All</option>
        <option value="pending" <?= $status_filter=='pending'?'selected':'' ?>>Pending</option>
        <option value="won" <?= $status_filter=='won'?'selected':'' ?>>Won</option>
        <option value="lost" <?= $status_filter=='lost'?'selected':'' ?>>Lost</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
  </form>
</div>

<div class="stat-grid">
  <div class="stat-box blue"><h2><?= $stats['total'] ?></h2><p>Total Bets</p></div>
  <div class="stat-box red"><h2>₹<?= number_format($stats['total_amt'] ?? 0, 2) ?></h2><p>Total Bet Amount</p></div>
  <div class="stat-box green"><h2>₹<?= number_format($stats['win_amt'] ?? 0, 2) ?></h2><p>Total Win Amount</p></div>
</div>

<div class="card">
  <h3>Bets - <?= $date ?></h3>
  <div style="overflow-x:auto;">
  <table>
    <thead><tr><th>#</th><th>User</th><th>Market</th><th>Type</th><th>Number</th><th>Amt</th><th>Session</th><th>Status</th><th>Win Amt</th><th>Time</th></tr></thead>
    <tbody>
    <?php while($b = $bets->fetch_assoc()): ?>
    <tr>
      <td><?= $b['id'] ?></td>
      <td><?= htmlspecialchars($b['name']) ?><br><small><?= $b['mobile'] ?></small></td>
      <td style="font-size:11px;"><?= htmlspecialchars($b['market_name']) ?></td>
      <td><?= strtoupper(str_replace('_',' ',$b['bet_type'])) ?></td>
      <td><strong><?= $b['number'] ?></strong></td>
      <td>₹<?= $b['amount'] ?></td>
      <td><?= strtoupper($b['session']) ?></td>
      <td><span class="badge badge-<?= $b['status'] ?>"><?= strtoupper($b['status']) ?></span></td>
      <td><?= $b['win_amount'] > 0 ? '₹'.$b['win_amount'] : '-' ?></td>
      <td style="font-size:11px;"><?= date('h:i A', strtotime($b['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  </div>
</div>

<?php include 'footer.php'; ?>
