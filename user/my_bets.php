<?php
require_once 'auth.php';
$pageTitle = 'My Bets';
$uid = $_SESSION['user_id'];
$date = isset($_GET['date']) ? sanitize($conn, $_GET['date']) : date('Y-m-d');

$bets = $conn->query("SELECT b.*,m.name as market_name FROM bets b JOIN markets m ON b.market_id=m.id WHERE b.user_id=$uid AND b.bet_date='$date' ORDER BY b.created_at DESC");
$stats = $conn->query("SELECT COUNT(*) as total, COALESCE(SUM(amount),0) as amt, COALESCE(SUM(win_amount),0) as win, SUM(CASE WHEN status='won' THEN 1 ELSE 0 END) as won_count, SUM(CASE WHEN status='lost' THEN 1 ELSE 0 END) as lost_count FROM bets WHERE user_id=$uid AND bet_date='$date'")->fetch_assoc();

$type_labels = ['single'=>'Single Ank','jodi'=>'Jodi','sp'=>'SP','dp'=>'DP','tp'=>'TP','half_sangam'=>'Half Sangam','full_sangam'=>'Full Sangam'];

include 'header.php';
?>

<!-- DATE FILTER -->
<div class="card">
  <div class="card-title">📅 Date Filter</div>
  <form method="GET" style="display:flex;gap:10px;align-items:flex-end;">
    <div class="form-group" style="margin:0;flex:1;">
      <input type="date" name="date" value="<?= $date ?>" style="width:100%;padding:11px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;">
    </div>
    <button type="submit" class="btn btn-yellow btn-sm" style="width:auto;padding:11px 20px;">Show</button>
  </form>
</div>

<!-- STATS -->
<div class="stat-row">
  <div class="stat-box purple">
    <div class="val"><?= $stats['total'] ?></div>
    <div class="lbl">Total Bets</div>
  </div>
  <div class="stat-box green">
    <div class="val"><?= $stats['won_count'] ?></div>
    <div class="lbl">Won</div>
  </div>
  <div class="stat-box red">
    <div class="val"><?= $stats['lost_count'] ?></div>
    <div class="lbl">Lost</div>
  </div>
</div>

<!-- AMOUNT STATS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
  <div style="background:#fff;border:2px solid #ff182c;border-radius:12px;padding:12px;text-align:center;">
    <div style="font-size:18px;color:#ff0016;">₹<?= number_format($stats['amt'],2) ?></div>
    <div style="font-size:10px;color:#888;font-style:normal;">Total Bet Amount</div>
  </div>
  <div style="background:#fff;border:2px solid #ff182c;border-radius:12px;padding:12px;text-align:center;">
    <div style="font-size:18px;color:#28a745;">₹<?= number_format($stats['win'],2) ?></div>
    <div style="font-size:10px;color:#888;font-style:normal;">Total Won Amount</div>
  </div>
</div>

<!-- BETS LIST -->
<div class="card">
  <div class="card-title">🎰 <?= date('d M Y', strtotime($date)) ?> ki Bets</div>

  <?php if ($bets->num_rows == 0): ?>
  <div style="text-align:center;padding:30px 20px;font-style:normal;">
    <div style="font-size:40px;margin-bottom:10px;">🎲</div>
    <div style="color:#999;font-size:14px;">Is date par koi bet nahi mili.</div>
    <a href="play.php" class="btn btn-primary" style="margin-top:15px;display:inline-block;width:auto;padding:10px 25px;">Bet Lagaiye</a>
  </div>
  <?php else: ?>

  <?php while($b = $bets->fetch_assoc()): ?>
  <div style="border:1px solid #f0e0f0;border-radius:10px;padding:12px;margin-bottom:8px;background:<?= $b['status']=='won'?'#f0fff4':($b['status']=='lost'?'#fff5f5':($b['status']=='cancelled'?'#f8f9fa':'#fffde7')) ?>;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;">
      <div>
        <div style="font-size:11px;color:#888;font-style:normal;"><?= htmlspecialchars($b['market_name']) ?></div>
        <div style="font-size:13px;color:#7c4066;"><?= $type_labels[$b['bet_type']] ?? strtoupper($b['bet_type']) ?> &nbsp;&middot;&nbsp; <?= strtoupper($b['session']) ?></div>
      </div>
      <span class="badge badge-<?= $b['status'] ?>"><?= strtoupper($b['status']) ?></span>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;flex-wrap:wrap;gap:6px;">
      <div style="font-size:26px;font-weight:900;color:#1a0030;"><?= $b['number'] ?></div>
      <div style="text-align:right;font-style:normal;">
        <div style="font-size:12px;color:#555;">Bet: <strong>&#x20B9;<?= number_format($b['amount'],2) ?></strong></div>
        <?php if ($b['status']=='won'): ?>
        <div style="font-size:14px;color:#28a745;font-weight:700;">Won: &#x20B9;<?= number_format($b['win_amount'],2) ?> &#x1F389;</div>
        <?php elseif($b['status']=='lost'): ?>
        <div style="font-size:12px;color:#999;">Better luck next time!</div>
        <?php elseif($b['status']=='cancelled'): ?>
        <div style="font-size:12px;color:#6c757d;">&#x20B9;<?= number_format($b['amount'],2) ?> refund ho gaya</div>
        <?php else: ?>
        <div style="font-size:11px;color:#856404;">Result awaited...</div>
        <?php endif; ?>
      </div>
    </div>
    <div style="font-size:10px;color:#bbb;margin-top:5px;font-style:normal;text-align:right;">#<?= $b['id'] ?> &middot; <?= date('h:i A', strtotime($b['created_at'])) ?></div>
  </div>
  <?php endwhile; ?>

  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
