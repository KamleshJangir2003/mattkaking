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

<!-- RECENT BETS -->
<?php
$recent_bets = $conn->query("SELECT b.*,m.name as market_name FROM bets b JOIN markets m ON b.market_id=m.id WHERE b.user_id=$uid ORDER BY b.created_at DESC LIMIT 10");
?>
<div class="card">
  <div class="card-title">🎲 My Recent Bets</div>
  <?php if($recent_bets->num_rows === 0): ?>
    <p style="text-align:center;color:#aaa;padding:20px;">Koi bet nahi mili abhi tak</p>
  <?php else: ?>
  <div style="overflow-x:auto;">
  <table style="width:100%;border-collapse:collapse;font-size:13px;">
    <thead>
      <tr style="background:#f8f5ff;">
        <th style="padding:9px 10px;text-align:left;color:#6c2d7e;font-size:11px;">MARKET</th>
        <th style="padding:9px 10px;text-align:left;color:#6c2d7e;font-size:11px;">TYPE</th>
        <th style="padding:9px 10px;text-align:left;color:#6c2d7e;font-size:11px;">NUMBER</th>
        <th style="padding:9px 10px;text-align:left;color:#6c2d7e;font-size:11px;">AMOUNT</th>
        <th style="padding:9px 10px;text-align:left;color:#6c2d7e;font-size:11px;">STATUS</th>
        <th style="padding:9px 10px;text-align:left;color:#6c2d7e;font-size:11px;">WIN AMT</th>
      </tr>
    </thead>
    <tbody>
    <?php while($b = $recent_bets->fetch_assoc()): ?>
    <tr style="border-bottom:1px solid #f5f5f5;">
      <td style="padding:10px;"><strong style="font-size:12px;"><?= htmlspecialchars($b['market_name']) ?></strong><br><span style="font-size:10px;color:#aaa;"><?= $b['bet_date'] ?></span></td>
      <td style="padding:10px;"><span style="background:#f0e8ff;color:#6c2d7e;padding:2px 8px;border-radius:5px;font-size:10px;font-weight:700;"><?= strtoupper(str_replace('_',' ',$b['bet_type'])) ?></span></td>
      <td style="padding:10px;"><strong style="font-size:16px;letter-spacing:1px;"><?= $b['number'] ?></strong></td>
      <td style="padding:10px;"><strong>₹<?= number_format($b['amount'],0) ?></strong></td>
      <td style="padding:10px;">
        <?php if($b['status']==='won'): ?>
          <span style="background:#d4f5e9;color:#00875a;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;">✅ WON</span>
        <?php elseif($b['status']==='lost'): ?>
          <span style="background:#fde8ef;color:#c0003c;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;">❌ LOST</span>
        <?php else: ?>
          <span style="background:#fff3cd;color:#856404;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;">⏳ PENDING</span>
        <?php endif; ?>
      </td>
      <td style="padding:10px;">
        <?php if($b['win_amount'] > 0): ?>
          <strong style="color:#00b894;">₹<?= number_format($b['win_amount'],0) ?></strong>
        <?php else: ?>
          <span style="color:#ddd;">—</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>

<!-- NOTICE BOX -->
<div style="background:#fff3cd;border:2px solid #ff9800;border-radius:12px;padding:14px;margin-bottom:12px;font-style:normal;">
  <div style="color:#856404;font-size:13px;line-height:1.6;">
    <strong>📢 Notice:</strong> Minimum bet ₹10. Results declared by admin after market close. Winnings credited automatically.
  </div>
</div>

<?php include 'footer.php'; ?>
