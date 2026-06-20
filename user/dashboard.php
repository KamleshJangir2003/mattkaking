<?php
require_once 'auth.php';
$pageTitle = 'Dashboard';
$uid = $_SESSION['user_id'];
$balance = getUserBalance($conn, $uid);
$today_bets = $conn->query("SELECT COUNT(*) as c FROM bets WHERE user_id=$uid AND bet_date=CURDATE()")->fetch_assoc()['c'];
$today_wins = $conn->query("SELECT COALESCE(SUM(win_amount),0) as c FROM bets WHERE user_id=$uid AND status='won' AND bet_date=CURDATE()")->fetch_assoc()['c'];
$pending_bets = $conn->query("SELECT COUNT(*) as c FROM bets WHERE user_id=$uid AND status='pending'")->fetch_assoc()['c'];
$markets = $conn->query("SELECT * FROM markets WHERE status='active' ORDER BY id");
include 'header.php';
?>

<!-- STATS -->
<div class="stat-row">
  <div class="stat-box purple">
    <div class="val"><?= $today_bets ?></div>
    <div class="lbl">Today Bets</div>
  </div>
  <div class="stat-box red">
    <div class="val"><?= $pending_bets ?></div>
    <div class="lbl">Pending</div>
  </div>
  <div class="stat-box green">
    <div class="val">₹<?= number_format($today_wins,0) ?></div>
    <div class="lbl">Today Won</div>
  </div>
</div>

<!-- MARKETS -->
<div class="card">
  <div class="card-title">📋 Today's Markets & Results</div>

  <?php while($m = $markets->fetch_assoc()):
    $is_highlight = in_array($m['slug'], ['kalyan','main-bazar','kalyan-night']);
  ?>
  <div class="market-item <?= $is_highlight ? 'highlight' : '' ?>">
    <div style="flex:1;min-width:0;">
      <div class="mkt-name"><?= htmlspecialchars($m['name']) ?></div>
      <div class="mkt-time">⏰ <?= $m['open_time'] ?> &nbsp;→&nbsp; <?= $m['close_time'] ?></div>
    </div>
    <div class="mkt-result <?= $m['result'] ? '' : 'pending' ?>">
      <?= $m['result'] ? $m['result'] : '⏳ Awaited' ?>
    </div>
    <a href="play.php?market=<?= $m['id'] ?>" class="bet-now-btn">BET NOW</a>
  </div>
  <?php endwhile; ?>
</div>

<!-- NOTICE BOX -->
<div style="background:#fff3cd;border:2px solid #ff9800;border-radius:12px;padding:14px;margin-bottom:12px;font-style:normal;">
  <div style="color:#856404;font-size:13px;line-height:1.6;">
    <strong>📢 Notice:</strong> Minimum bet ₹10. Results declared by admin after market close. Winnings credited automatically.
  </div>
</div>

<?php include 'footer.php'; ?>
