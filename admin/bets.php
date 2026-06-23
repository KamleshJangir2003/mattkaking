<?php
require_once 'auth.php';
$pageTitle = 'Bets';

$date          = isset($_GET['date'])   ? sanitize($conn, $_GET['date'])   : date('Y-m-d');
$market_filter = isset($_GET['market']) ? (int)$_GET['market']             : 0;
$status_filter = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : '';

$where = "WHERE b.bet_date='$date'";
if ($market_filter) $where .= " AND b.market_id=$market_filter";
if ($status_filter) $where .= " AND b.status='$status_filter'";

if (isset($_GET['approve'])) {
    $bid = (int)$_GET['approve'];
    $bet = $conn->query("SELECT * FROM bets WHERE id=$bid")->fetch_assoc();
    if ($bet && $bet['status'] === 'pending') {
        $ratio   = getWinRatio($conn, $bet['bet_type']);
        $win_amt = $bet['amount'] * $ratio;
        $conn->query("UPDATE bets SET status='won', win_amount=$win_amt WHERE id=$bid");
        updateBalance($conn, $bet['user_id'], $win_amt, 'add');
        addTransaction($conn, $bet['user_id'], 'win', $win_amt, "Won bet #$bid (manual approve)");
    }
    header("Location: bets.php?date=$date&market=$market_filter&status=$status_filter"); exit;
}
if (isset($_GET['reject'])) {
    $bid = (int)$_GET['reject'];
    $conn->query("UPDATE bets SET status='lost' WHERE id=$bid AND status='pending'");
    header("Location: bets.php?date=$date&market=$market_filter&status=$status_filter"); exit;
}

$bets    = $conn->query("SELECT b.*,u.name,u.mobile,m.name as market_name FROM bets b JOIN users u ON b.user_id=u.id JOIN markets m ON b.market_id=m.id $where ORDER BY b.created_at DESC");
$markets = $conn->query("SELECT id,name FROM markets ORDER BY name");
$stats   = $conn->query("SELECT COUNT(*) as total, COALESCE(SUM(amount),0) as total_amt, COALESCE(SUM(CASE WHEN status='won' THEN win_amount ELSE 0 END),0) as win_amt, COALESCE(SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END),0) as pending_cnt FROM bets b $where")->fetch_assoc();

$profit = $stats['total_amt'] - $stats['win_amt'];

include 'header.php';
?>

<style>
.bets-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:16px;margin-bottom:22px;}
.bstat{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 2px 10px rgba(0,0,0,.06);display:flex;align-items:center;gap:14px;border-left:4px solid var(--bc);}
.bstat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;background:rgba(var(--bc-rgb),.1);color:var(--bc);flex-shrink:0;}
.bstat h3{font-size:22px;font-weight:800;color:#1e1035;line-height:1;}
.bstat p{font-size:11px;color:#888;margin-top:3px;font-weight:600;}

.filter-card{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:20px;}
.filter-card form{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;}
.fg{display:flex;flex-direction:column;gap:5px;min-width:150px;flex:1;}
.fg label{font-size:11px;font-weight:800;color:#888;text-transform:uppercase;letter-spacing:.6px;}
.fg input,.fg select{padding:10px 13px;border:2px solid #ece8f5;border-radius:10px;font-size:13px;font-weight:700;color:#1e1035;outline:none;transition:.2s;background:#fff;}
.fg input:focus,.fg select:focus{border-color:#6c2d7e;}
.filter-btn{padding:10px 22px;background:linear-gradient(135deg,#6c2d7e,#9b4db5);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:7px;transition:.2s;white-space:nowrap;}
.filter-btn:hover{opacity:.9;}
.reset-btn{padding:10px 16px;background:#f5f5f5;color:#888;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:6px;}
.reset-btn:hover{background:#eee;}

.card{background:#fff;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,.06);}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px;}
.card-header h3{font-size:15px;font-weight:800;color:#1e1035;display:flex;align-items:center;gap:8px;}
.date-pill{background:#f0e8ff;color:#6c2d7e;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:800;}

table{width:100%;border-collapse:collapse;}
th{background:#f8f5ff;color:#6c2d7e;font-size:11px;font-weight:800;padding:11px 13px;text-align:left;text-transform:uppercase;letter-spacing:.5px;}
td{padding:12px 13px;font-size:13px;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fdfbff;}

.user-av{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:800;flex-shrink:0;}
.badge{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;display:inline-block;}
.badge-won{background:#d4f5e9;color:#00875a;}
.badge-lost{background:#fde8ef;color:#c0003c;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-open{background:#e3f2fd;color:#0277bd;}
.badge-close{background:#fce4ec;color:#880e4f;}
.type-badge{padding:3px 9px;border-radius:6px;font-size:10px;font-weight:800;background:#f0e8ff;color:#6c2d7e;letter-spacing:.3px;}

.empty-state{text-align:center;padding:50px 20px;color:#bbb;}
.empty-state i{font-size:40px;display:block;margin-bottom:12px;color:#ddd;}
.empty-state p{font-size:14px;font-weight:700;}
.action-btn{padding:5px 10px;border-radius:7px;font-size:11px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;transition:.15s;white-space:nowrap;}
.btn-approve{background:#d4f5e9;color:#00875a;}
.btn-approve:hover{background:#00875a;color:#fff;}
.btn-reject{background:#fde8ef;color:#c0003c;}
.btn-reject:hover{background:#c0003c;color:#fff;}
</style>

<!-- STATS -->
<div class="bets-stats">
  <div class="bstat" style="--bc:#0984e3;--bc-rgb:9,132,227;">
    <div class="bstat-icon"><i class="fas fa-dice"></i></div>
    <div><h3><?= number_format($stats['total']) ?></h3><p>Total Bets</p></div>
  </div>
  <div class="bstat" style="--bc:#6c2d7e;--bc-rgb:108,45,126;">
    <div class="bstat-icon"><i class="fas fa-rupee-sign"></i></div>
    <div><h3>₹<?= number_format($stats['total_amt'],0) ?></h3><p>Bet Amount</p></div>
  </div>
  <div class="bstat" style="--bc:#e84393;--bc-rgb:232,67,147;">
    <div class="bstat-icon"><i class="fas fa-gift"></i></div>
    <div><h3>₹<?= number_format($stats['win_amt'],0) ?></h3><p>Win Amount</p></div>
  </div>
  <div class="bstat" style="--bc:#00b894;--bc-rgb:0,184,148;">
    <div class="bstat-icon"><i class="fas fa-chart-line"></i></div>
    <div><h3>₹<?= number_format($profit,0) ?></h3><p>Net Profit</p></div>
  </div>
  <div class="bstat" style="--bc:#f39c12;--bc-rgb:243,156,18;">
    <div class="bstat-icon"><i class="fas fa-clock"></i></div>
    <div><h3><?= number_format($stats['pending_cnt']) ?></h3><p>Pending Bets</p></div>
  </div>
</div>

<!-- FILTERS -->
<div class="filter-card">
  <form method="GET">
    <div class="fg">
      <label><i class="fas fa-calendar"></i> Date</label>
      <input type="date" name="date" value="<?= $date ?>">
    </div>
    <div class="fg">
      <label><i class="fas fa-store"></i> Market</label>
      <select name="market">
        <option value="">All Markets</option>
        <?php
        $markets->data_seek(0);
        while($m = $markets->fetch_assoc()):
        ?>
        <option value="<?= $m['id'] ?>" <?= $market_filter==$m['id']?'selected':'' ?>><?= htmlspecialchars($m['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="fg">
      <label><i class="fas fa-filter"></i> Status</label>
      <select name="status">
        <option value="">All Status</option>
        <option value="pending" <?= $status_filter=='pending'?'selected':'' ?>>⏳ Pending</option>
        <option value="won"     <?= $status_filter=='won'    ?'selected':'' ?>>✅ Won</option>
        <option value="lost"    <?= $status_filter=='lost'   ?'selected':'' ?>>❌ Lost</option>
      </select>
    </div>
    <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Filter</button>
    <a href="bets.php" class="reset-btn"><i class="fas fa-redo"></i> Reset</a>
  </form>
</div>

<!-- TABLE -->
<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-dice" style="color:#6c2d7e;"></i> Bets List</h3>
    <div style="display:flex;align-items:center;gap:10px;">
      <span class="date-pill"><i class="fas fa-calendar-day"></i> <?= date('d M Y', strtotime($date)) ?></span>
      <span style="font-size:12px;color:#aaa;"><?= $bets->num_rows ?> records</span>
    </div>
  </div>
  <div style="overflow-x:auto;">
  <table>
    <thead>
      <tr><th>#</th><th>User</th><th>Market</th><th>Type</th><th>Number</th><th>Amount</th><th>Session</th><th>Status</th><th>Win Amt</th><th>Time</th><th>Action</th></tr>
    </thead>
    <tbody>
    <?php
    $colors = ['#6c2d7e','#0984e3','#00b894','#e67e22','#e84393','#f39c12','#00cec9'];
    $count = 0;
    while($b = $bets->fetch_assoc()):
      $color = $colors[$b['user_id'] % count($colors)];
      $count++;
    ?>
    <tr>
      <td style="color:#aaa;font-size:12px;font-weight:700;"><?= $b['id'] ?></td>
      <td>
        <div style="display:flex;align-items:center;gap:9px;">
          <div class="user-av" style="background:<?= $color ?>"><?= strtoupper(substr($b['name'],0,1)) ?></div>
          <div>
            <div style="font-weight:800;font-size:12px;"><?= htmlspecialchars($b['name']) ?></div>
            <div style="font-size:11px;color:#aaa;"><?= $b['mobile'] ?></div>
          </div>
        </div>
      </td>
      <td style="font-size:11.5px;font-weight:700;max-width:120px;"><?= htmlspecialchars($b['market_name']) ?></td>
      <td><span class="type-badge"><?= strtoupper(str_replace('_',' ',$b['bet_type'])) ?></span></td>
      <td><strong style="font-size:16px;letter-spacing:1px;"><?= $b['number'] ?></strong></td>
      <td><strong style="color:#1e1035;">₹<?= number_format($b['amount'],0) ?></strong></td>
      <td><span class="badge badge-<?= $b['session'] ?>"><?= strtoupper($b['session']) ?></span></td>
      <td><span class="badge badge-<?= $b['status'] ?>">
        <?php
          if($b['status']==='won')     echo '✅ WON';
          elseif($b['status']==='lost') echo '❌ LOST';
          else                          echo '⏳ PENDING';
        ?>
      </span></td>
      <td>
        <?php if($b['win_amount'] > 0): ?>
          <strong style="color:#00b894;">₹<?= number_format($b['win_amount'],0) ?></strong>
        <?php else: ?>
          <span style="color:#ddd;">—</span>
        <?php endif; ?>
      </td>
      <td style="font-size:11px;color:#888;white-space:nowrap;"><?= date('h:i A', strtotime($b['created_at'])) ?></td>
      <td>
        <?php if($b['status']==='pending'): ?>
        <div style="display:flex;gap:5px;">
          <a href="?date=<?= $date ?>&market=<?= $market_filter ?>&status=<?= $status_filter ?>&approve=<?= $b['id'] ?>" class="action-btn btn-approve" onclick="return confirm('Approve karein?')"><i class="fas fa-check"></i> Approve</a>
          <a href="?date=<?= $date ?>&market=<?= $market_filter ?>&status=<?= $status_filter ?>&reject=<?= $b['id'] ?>" class="action-btn btn-reject" onclick="return confirm('Reject karein?')"><i class="fas fa-times"></i> Reject</a>
        </div>
        <?php else: ?>
          <span style="color:#ddd;font-size:12px;">—</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
    <?php if($count === 0): ?>
    <tr><td colspan="10">
      <div class="empty-state">
        <i class="fas fa-dice"></i>
        <p>Is date ke liye koi bet nahi mili</p>
      </div>
    </td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php include 'footer.php'; ?>
