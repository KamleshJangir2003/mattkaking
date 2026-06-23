<?php
require_once 'auth.php';
$pageTitle = 'Dashboard';
$uid = $_SESSION['user_id'];
$balance = getUserBalance($conn, $uid);
$today_bets   = $conn->query("SELECT COUNT(*) as c FROM bets WHERE user_id=$uid AND bet_date=CURDATE()")->fetch_assoc()['c'];
$today_wins   = $conn->query("SELECT COALESCE(SUM(win_amount),0) as c FROM bets WHERE user_id=$uid AND status='won' AND bet_date=CURDATE()")->fetch_assoc()['c'];
$pending_bets = $conn->query("SELECT COUNT(*) as c FROM bets WHERE user_id=$uid AND status='pending'")->fetch_assoc()['c'];
$total_bets   = $conn->query("SELECT COUNT(*) as c FROM bets WHERE user_id=$uid")->fetch_assoc()['c'];
$total_won    = $conn->query("SELECT COALESCE(SUM(win_amount),0) as c FROM bets WHERE user_id=$uid AND status='won'")->fetch_assoc()['c'];
$total_wagered= $conn->query("SELECT COALESCE(SUM(amount),0) as c FROM bets WHERE user_id=$uid")->fetch_assoc()['c'];
$won_bets     = $conn->query("SELECT COUNT(*) as c FROM bets WHERE user_id=$uid AND status='won'")->fetch_assoc()['c'];
$user_info    = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
$markets      = $conn->query("SELECT * FROM markets WHERE status='active' ORDER BY id");
include 'header.php';
?>

<!-- PROFILE CARD -->
<div style="background:linear-gradient(135deg,#1a0030,#3a0060);border-radius:16px;padding:16px 18px;margin-bottom:12px;display:flex;align-items:center;gap:14px;box-shadow:0 6px 20px rgba(26,0,48,.4);">
  <div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ffcc00,#ff9900);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:900;color:#1a0030;flex-shrink:0;">
    <?= strtoupper(substr($user_info['name'],0,1)) ?>
  </div>
  <div style="flex:1;min-width:0;">
    <div style="color:#ffcc00;font-size:16px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($user_info['name']) ?></div>
    <div style="color:#ccc;font-size:11px;font-style:normal;margin-top:2px;">📱 <?= $user_info['mobile'] ?></div>
    <div style="margin-top:5px;">
      <span style="background:<?= $user_info['status']==='active'?'#28a745':'#dc3545' ?>;color:#fff;padding:2px 10px;border-radius:20px;font-size:10px;font-style:normal;">
        <?= strtoupper($user_info['status']) ?>
      </span>
    </div>
  </div>
  <div style="text-align:right;">
    <div style="color:#aaa;font-size:10px;font-style:normal;">Joined</div>
    <div style="color:#fff;font-size:11px;font-style:normal;"><?= date('d M Y', strtotime($user_info['created_at'])) ?></div>
  </div>
</div>

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

<!-- LIFETIME STATS -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:12px;">
  <div style="background:#fff;border:2px solid #ff182c;border-radius:12px;padding:10px 6px;text-align:center;">
    <div style="font-size:18px;color:#7c4066;font-weight:900;"><?= $total_bets ?></div>
    <div style="font-size:9px;color:#888;font-style:normal;margin-top:3px;">Total Bets</div>
  </div>
  <div style="background:#fff;border:2px solid #ff182c;border-radius:12px;padding:10px 6px;text-align:center;">
    <div style="font-size:18px;color:#28a745;font-weight:900;"><?= $won_bets ?></div>
    <div style="font-size:9px;color:#888;font-style:normal;margin-top:3px;">Bets Won</div>
  </div>
  <div style="background:#fff;border:2px solid #ff182c;border-radius:12px;padding:10px 6px;text-align:center;">
    <div style="font-size:18px;color:#ff9800;font-weight:900;">₹<?= number_format($total_won,0) ?></div>
    <div style="font-size:9px;color:#888;font-style:normal;margin-top:3px;">Total Won</div>
  </div>
</div>

<!-- QUICK ACTIONS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
  <a href="play.php" style="display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;padding:14px;border-radius:12px;text-decoration:none;font-size:14px;font-style:normal;font-weight:700;box-shadow:0 4px 12px rgba(124,64,102,.3);">🎰 Play Now</a>
  <a href="wallet.php" style="display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(135deg,#28a745,#1e7e34);color:#fff;padding:14px;border-radius:12px;text-decoration:none;font-size:14px;font-style:normal;font-weight:700;box-shadow:0 4px 12px rgba(40,167,69,.3);">💰 Add Money</a>
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
          <span style="background:#d4f5e9;color:#00875a;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;">&#x2705; WON</span>
        <?php elseif($b['status']==='lost'): ?>
          <span style="background:#fde8ef;color:#c0003c;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;">&#x274C; LOST</span>
        <?php elseif($b['status']==='cancelled'): ?>
          <span style="background:#e9ecef;color:#6c757d;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;">&#x1F6AB; CANCELLED</span>
        <?php else: ?>
          <span style="background:#fff3cd;color:#856404;padding:3px 9px;border-radius:12px;font-size:11px;font-weight:700;">&#x23F3; PENDING</span>
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
