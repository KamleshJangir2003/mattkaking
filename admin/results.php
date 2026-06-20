<?php
require_once 'auth.php';
$pageTitle = 'Results';
$msg = ''; $msg_type = 'success';

if (isset($_POST['declare'])) {
    $market_id = (int)$_POST['market_id'];
    $result    = sanitize($conn, $_POST['result']);

    $conn->query("UPDATE markets SET result='$result' WHERE id=$market_id");

    $parts       = explode('-', str_replace(' ', '', $result));
    $open_panna  = $parts[0] ?? '';
    $jodi        = $parts[1] ?? '';
    $close_panna = $parts[2] ?? '';
    $open_ank    = strlen($jodi) >= 1 ? $jodi[0] : '';
    $close_ank   = strlen($jodi) >= 2 ? $jodi[1] : '';

    $bets = $conn->query("SELECT * FROM bets WHERE market_id=$market_id AND status='pending' AND bet_date=CURDATE()");
    $won_count = 0; $settled = 0;
    while ($bet = $bets->fetch_assoc()) {
        $won   = false;
        $ratio = getWinRatio($conn, $bet['bet_type']);
        switch ($bet['bet_type']) {
            case 'single':
                $won = ($bet['session']=='open' && $bet['number']==$open_ank) || ($bet['session']=='close' && $bet['number']==$close_ank);
                break;
            case 'jodi':
                $won = ($bet['number'] == $jodi);
                break;
            case 'sp': case 'dp': case 'tp':
                $won = ($bet['session']=='open' && $bet['number']==$open_panna) || ($bet['session']=='close' && $bet['number']==$close_panna);
                break;
            case 'half_sangam':
                $won = ($open_panna.'-'.$close_ank == $bet['number']) || ($open_ank.'-'.$close_panna == $bet['number']);
                break;
            case 'full_sangam':
                $won = ($open_panna.'-'.$jodi.'-'.$close_panna == $bet['number']);
                break;
        }
        $bid = $bet['id']; $uid = $bet['user_id'];
        if ($won) {
            $win_amt = $bet['amount'] * $ratio;
            $conn->query("UPDATE bets SET status='won',win_amount=$win_amt WHERE id=$bid");
            updateBalance($conn, $uid, $win_amt, 'add');
            addTransaction($conn, $uid, 'win', $win_amt, "Won bet #$bid");
            $won_count++;
        } else {
            $conn->query("UPDATE bets SET status='lost' WHERE id=$bid");
        }
        $settled++;
    }
    $mkt_name = $conn->query("SELECT name FROM markets WHERE id=$market_id")->fetch_assoc()['name'];
    $msg = "✅ <strong>$mkt_name</strong> — Result <strong>$result</strong> declare ho gaya! $settled bets settle hue, $won_count winners.";
}

$markets        = $conn->query("SELECT * FROM markets WHERE status='active' ORDER BY id");
$recent_results = $conn->query("SELECT m.*,
    COALESCE(b.cnt,0) as bet_count,
    COALESCE(b.total,0) as bet_total,
    COALESCE(b.won_cnt,0) as won_cnt,
    COALESCE(b.win_total,0) as win_total
    FROM markets m
    LEFT JOIN (
        SELECT market_id,
            COUNT(*) as cnt,
            SUM(amount) as total,
            SUM(CASE WHEN status='won' THEN 1 ELSE 0 END) as won_cnt,
            SUM(CASE WHEN status='won' THEN win_amount ELSE 0 END) as win_total
        FROM bets WHERE bet_date=CURDATE() GROUP BY market_id
    ) b ON m.id=b.market_id
    ORDER BY m.id");

include 'header.php';
?>

<style>
.res-grid{display:grid;grid-template-columns:1fr 1.6fr;gap:22px;margin-bottom:22px;}
@media(max-width:900px){.res-grid{grid-template-columns:1fr;}}

.card{background:#fff;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,.06);}
.card-header{display:flex;align-items:center;gap:10px;margin-bottom:20px;}
.card-header h3{font-size:15px;font-weight:800;color:#1e1035;}
.card-header i{color:#6c2d7e;}

.declare-box{background:linear-gradient(135deg,#1e1035,#3d1a6e);border-radius:16px;padding:24px;box-shadow:0 4px 20px rgba(30,16,53,.3);}
.declare-box h3{color:#ffcc00;font-size:16px;font-weight:800;margin-bottom:5px;display:flex;align-items:center;gap:8px;}
.declare-box p{color:rgba(255,255,255,.5);font-size:12px;margin-bottom:20px;}
.dfg{display:flex;flex-direction:column;gap:5px;margin-bottom:14px;}
.dfg label{font-size:11px;font-weight:800;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.6px;}
.dfg select,.dfg input{padding:11px 14px;border:2px solid rgba(255,255,255,.1);border-radius:10px;font-size:13px;font-weight:700;color:#fff;background:rgba(255,255,255,.08);outline:none;transition:.2s;}
.dfg select:focus,.dfg input:focus{border-color:#ffcc00;}
.dfg select option{background:#1e1035;color:#fff;}
.dfg small{color:rgba(255,255,255,.35);font-size:11px;margin-top:3px;}
.format-hint{background:rgba(255,204,0,.08);border:1px solid rgba(255,204,0,.2);border-radius:10px;padding:12px 14px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px;}
.format-hint i{color:#ffcc00;margin-top:1px;flex-shrink:0;}
.format-hint p{color:rgba(255,255,255,.6);font-size:12px;line-height:1.6;}
.format-hint strong{color:#ffcc00;}
.declare-btn{width:100%;padding:13px;background:linear-gradient(135deg,#ffcc00,#ff9800);color:#1e1035;border:none;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s;}
.declare-btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(255,204,0,.35);}

.alert-toast{display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:13px;font-weight:600;background:#e8faf4;color:#00875a;border:1.5px solid #b2dfdb;}
.alert-toast i{font-size:18px;flex-shrink:0;}

table{width:100%;border-collapse:collapse;}
th{background:#f8f5ff;color:#6c2d7e;font-size:11px;font-weight:800;padding:11px 13px;text-align:left;text-transform:uppercase;letter-spacing:.5px;}
td{padding:11px 13px;font-size:13px;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fdfbff;}

.badge{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;display:inline-block;}
.result-pill{background:#1e1035;color:#ffcc00;padding:5px 14px;border-radius:8px;font-size:13px;font-weight:800;letter-spacing:1.5px;font-family:monospace;}
.pending-pill{background:#f5f5f5;color:#bbb;padding:5px 14px;border-radius:8px;font-size:12px;font-weight:700;}
.market-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px;}
.dot-active{background:#00b894;}
.dot-inactive{background:#ddd;}
.won-badge{background:#d4f5e9;color:#00875a;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700;}
</style>

<?php if($msg): ?>
<div class="alert-toast"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div>
<?php endif; ?>

<div class="res-grid">

  <!-- DECLARE FORM -->
  <div class="declare-box">
    <h3><i class="fas fa-trophy"></i> Result Declare Karein</h3>
    <p>Market ka result enter karke bets auto-settle karein</p>

    <div class="format-hint">
      <i class="fas fa-info-circle"></i>
      <p>Format: <strong>OpenPanna - Jodi - ClosePanna</strong><br>
      Example: <strong>559-96-169</strong><br>
      Open Ank = 9, Close Ank = 6 (Jodi ke digits)</p>
    </div>

    <form method="POST">
      <div class="dfg">
        <label><i class="fas fa-store"></i> Market Select Karein</label>
        <select name="market_id" required>
          <option value="">-- Market Choose Karein --</option>
          <?php $markets->data_seek(0); while($m = $markets->fetch_assoc()): ?>
          <option value="<?= $m['id'] ?>">
            <?= htmlspecialchars($m['name']) ?>
            <?= $m['result'] ? ' ✓ ('.$m['result'].')' : ' — Pending' ?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="dfg">
        <label><i class="fas fa-hashtag"></i> Result Enter Karein</label>
        <input type="text" name="result" required placeholder="e.g. 559-96-169" pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}">
        <small>Format: 3digit - 2digit - 3digit</small>
      </div>
      <button type="submit" name="declare" class="declare-btn">
        <i class="fas fa-flag-checkered"></i> Declare & Settle Bets
      </button>
    </form>
  </div>

  <!-- TODAY MARKETS TABLE -->
  <div class="card">
    <div class="card-header">
      <i class="fas fa-calendar-day"></i>
      <h3>Aaj ke Markets — <?= date('d M Y') ?></h3>
    </div>
    <div style="overflow-x:auto;">
    <table>
      <thead>
        <tr><th>Market</th><th>Result</th><th>Bets</th><th>Amount</th><th>Winners</th><th>Win Paid</th></tr>
      </thead>
      <tbody>
      <?php while($r = $recent_results->fetch_assoc()): ?>
      <tr>
        <td>
          <span class="market-dot dot-<?= $r['status'] ?>"></span>
          <strong style="font-size:12px;"><?= htmlspecialchars($r['name']) ?></strong>
          <div style="font-size:10px;color:#aaa;margin-top:2px;"><?= $r['open_time'] ?> – <?= $r['close_time'] ?></div>
        </td>
        <td>
          <?php if($r['result']): ?>
            <span class="result-pill"><?= $r['result'] ?></span>
          <?php else: ?>
            <span class="pending-pill">⏳ Pending</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if($r['bet_count'] > 0): ?>
            <strong><?= $r['bet_count'] ?></strong>
          <?php else: ?>
            <span style="color:#ddd;">—</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if($r['bet_total'] > 0): ?>
            <strong style="color:#1e1035;">₹<?= number_format($r['bet_total'],0) ?></strong>
          <?php else: ?>
            <span style="color:#ddd;">—</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if($r['won_cnt'] > 0): ?>
            <span class="won-badge">🏆 <?= $r['won_cnt'] ?></span>
          <?php else: ?>
            <span style="color:#ddd;">—</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if($r['win_total'] > 0): ?>
            <strong style="color:#e84393;">₹<?= number_format($r['win_total'],0) ?></strong>
          <?php else: ?>
            <span style="color:#ddd;">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
  </div>

</div>

<?php include 'footer.php'; ?>
