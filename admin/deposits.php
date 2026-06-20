<?php
require_once 'auth.php';
$pageTitle = 'Deposits';

$type_filter = isset($_GET['type']) ? sanitize($conn, $_GET['type']) : '';
$search      = isset($_GET['q'])    ? sanitize($conn, $_GET['q'])    : '';
$date_filter = isset($_GET['date']) ? sanitize($conn, $_GET['date']) : '';

$where = "WHERE t.type IN ('deposit','withdraw')";
if ($type_filter)  $where .= " AND t.type='$type_filter'";
if ($search)       $where .= " AND (u.name LIKE '%$search%' OR u.mobile LIKE '%$search%')";
if ($date_filter)  $where .= " AND DATE(t.created_at)='$date_filter'";

$transactions = $conn->query("SELECT t.*,u.name,u.mobile,u.balance FROM transactions t JOIN users u ON t.user_id=u.id $where ORDER BY t.created_at DESC LIMIT 200");

$stats = $conn->query("SELECT
    COALESCE(SUM(CASE WHEN type='deposit'  THEN amount ELSE 0 END),0) as total_dep,
    COALESCE(SUM(CASE WHEN type='withdraw' THEN amount ELSE 0 END),0) as total_with,
    COALESCE(SUM(CASE WHEN type='deposit'  AND DATE(created_at)=CURDATE() THEN amount ELSE 0 END),0) as today_dep,
    COUNT(CASE WHEN type='deposit'  THEN 1 END) as dep_count,
    COUNT(CASE WHEN type='withdraw' THEN 1 END) as with_count
    FROM transactions WHERE type IN ('deposit','withdraw')")->fetch_assoc();

include 'header.php';
?>

<style>
.dep-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:16px;margin-bottom:22px;}
.dstat{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 2px 10px rgba(0,0,0,.06);display:flex;align-items:center;gap:14px;border-left:4px solid var(--dc);}
.dstat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;background:rgba(var(--dc-rgb),.1);color:var(--dc);flex-shrink:0;}
.dstat h3{font-size:22px;font-weight:800;color:#1e1035;line-height:1;}
.dstat p{font-size:11px;color:#888;font-weight:600;margin-top:3px;}

.filter-card{background:#fff;border-radius:14px;padding:16px 20px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:20px;}
.filter-card form{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;}
.fg{display:flex;flex-direction:column;gap:5px;min-width:140px;flex:1;}
.fg label{font-size:11px;font-weight:800;color:#888;text-transform:uppercase;letter-spacing:.6px;}
.fg input,.fg select{padding:10px 13px;border:2px solid #ece8f5;border-radius:10px;font-size:13px;font-weight:700;color:#1e1035;outline:none;transition:.2s;background:#fff;}
.fg input:focus,.fg select:focus{border-color:#6c2d7e;}
.filter-btn{padding:10px 20px;background:linear-gradient(135deg,#6c2d7e,#9b4db5);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:7px;white-space:nowrap;transition:.2s;}
.filter-btn:hover{opacity:.9;}
.reset-btn{padding:10px 14px;background:#f5f5f5;color:#888;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:6px;}
.reset-btn:hover{background:#eee;color:#555;}

.card{background:#fff;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,.06);}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px;}
.card-header h3{font-size:15px;font-weight:800;color:#1e1035;display:flex;align-items:center;gap:8px;}

table{width:100%;border-collapse:collapse;}
th{background:#f8f5ff;color:#6c2d7e;font-size:11px;font-weight:800;padding:11px 13px;text-align:left;text-transform:uppercase;letter-spacing:.5px;}
td{padding:12px 13px;font-size:13px;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fdfbff;}

.user-av{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:800;flex-shrink:0;}
.badge{padding:5px 12px;border-radius:20px;font-size:11px;font-weight:800;display:inline-flex;align-items:center;gap:5px;}
.badge-deposit{background:#d4f5e9;color:#00875a;}
.badge-withdraw{background:#fde8ef;color:#c0003c;}
.note-chip{background:#f8f5ff;color:#6c2d7e;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;}

.empty-state{text-align:center;padding:50px 20px;color:#bbb;}
.empty-state i{font-size:40px;display:block;margin-bottom:12px;color:#eee;}
.empty-state p{font-size:14px;font-weight:700;}

.type-tabs{display:flex;gap:8px;flex-wrap:wrap;}
.ttab{padding:7px 16px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:2px solid #ece8f5;background:#fff;color:#888;text-decoration:none;transition:.2s;}
.ttab:hover{border-color:#6c2d7e;color:#6c2d7e;}
.ttab.tall{background:#6c2d7e;color:#fff;border-color:#6c2d7e;}
.ttab.tgreen{background:#00b894;color:#fff;border-color:#00b894;}
.ttab.tred{background:#e84393;color:#fff;border-color:#e84393;}
</style>

<!-- STATS -->
<div class="dep-stats">
  <div class="dstat" style="--dc:#00b894;--dc-rgb:0,184,148;">
    <div class="dstat-icon"><i class="fas fa-arrow-down"></i></div>
    <div><h3>₹<?= number_format($stats['total_dep'],0) ?></h3><p>Total Deposits</p></div>
  </div>
  <div class="dstat" style="--dc:#e84393;--dc-rgb:232,67,147;">
    <div class="dstat-icon"><i class="fas fa-arrow-up"></i></div>
    <div><h3>₹<?= number_format($stats['total_with'],0) ?></h3><p>Total Withdrawals</p></div>
  </div>
  <div class="dstat" style="--dc:#0984e3;--dc-rgb:9,132,227;">
    <div class="dstat-icon"><i class="fas fa-calendar-day"></i></div>
    <div><h3>₹<?= number_format($stats['today_dep'],0) ?></h3><p>Today's Deposits</p></div>
  </div>
  <div class="dstat" style="--dc:#6c2d7e;--dc-rgb:108,45,126;">
    <div class="dstat-icon"><i class="fas fa-receipt"></i></div>
    <div><h3><?= number_format($stats['dep_count'] + $stats['with_count']) ?></h3><p>Total Transactions</p></div>
  </div>
</div>

<!-- FILTERS -->
<div class="filter-card">
  <form method="GET">
    <div class="fg">
      <label><i class="fas fa-search"></i> Search User</label>
      <input type="text" name="q" placeholder="Name ya mobile..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="fg">
      <label><i class="fas fa-filter"></i> Type</label>
      <select name="type">
        <option value="">All Types</option>
        <option value="deposit"  <?= $type_filter==='deposit' ?'selected':'' ?>>⬇ Deposit</option>
        <option value="withdraw" <?= $type_filter==='withdraw'?'selected':'' ?>>⬆ Withdraw</option>
      </select>
    </div>
    <div class="fg">
      <label><i class="fas fa-calendar"></i> Date</label>
      <input type="date" name="date" value="<?= $date_filter ?>">
    </div>
    <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Filter</button>
    <a href="deposits.php" class="reset-btn"><i class="fas fa-redo"></i> Reset</a>
  </form>
</div>

<!-- TABLE -->
<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-wallet" style="color:#6c2d7e;"></i> Transaction History</h3>
    <div style="display:flex;align-items:center;gap:10px;">
      <?php if($date_filter): ?>
        <span style="background:#f0e8ff;color:#6c2d7e;padding:5px 13px;border-radius:20px;font-size:12px;font-weight:800;">
          <i class="fas fa-calendar-day"></i> <?= date('d M Y', strtotime($date_filter)) ?>
        </span>
      <?php endif; ?>
      <span style="font-size:12px;color:#aaa;"><?= $transactions->num_rows ?> records</span>
    </div>
  </div>
  <div style="overflow-x:auto;">
  <table>
    <thead>
      <tr><th>#</th><th>User</th><th>Type</th><th>Amount</th><th>User Balance</th><th>Note</th><th>Date & Time</th></tr>
    </thead>
    <tbody>
    <?php
    $colors = ['#6c2d7e','#0984e3','#00b894','#e67e22','#e84393','#f39c12','#00cec9'];
    $count = 0;
    while($t = $transactions->fetch_assoc()):
      $color = $colors[$t['user_id'] % count($colors)];
      $count++;
    ?>
    <tr>
      <td style="color:#aaa;font-size:12px;font-weight:700;"><?= $t['id'] ?></td>
      <td>
        <div style="display:flex;align-items:center;gap:9px;">
          <div class="user-av" style="background:<?= $color ?>"><?= strtoupper(substr($t['name'],0,1)) ?></div>
          <div>
            <div style="font-weight:800;font-size:12px;"><?= htmlspecialchars($t['name']) ?></div>
            <div style="font-size:11px;color:#aaa;"><?= $t['mobile'] ?></div>
          </div>
        </div>
      </td>
      <td>
        <span class="badge badge-<?= $t['type'] ?>">
          <?php if($t['type']==='deposit'): ?>
            <i class="fas fa-arrow-down"></i> DEPOSIT
          <?php else: ?>
            <i class="fas fa-arrow-up"></i> WITHDRAW
          <?php endif; ?>
        </span>
      </td>
      <td>
        <strong style="font-size:15px;color:<?= $t['type']==='deposit'?'#00875a':'#c0003c' ?>;">
          <?= $t['type']==='deposit' ? '+' : '-' ?>₹<?= number_format($t['amount'],2) ?>
        </strong>
      </td>
      <td>
        <span style="font-size:13px;font-weight:700;color:#1e1035;">₹<?= number_format($t['balance'],2) ?></span>
      </td>
      <td>
        <?php if($t['note']): ?>
          <span class="note-chip"><?= htmlspecialchars($t['note']) ?></span>
        <?php else: ?>
          <span style="color:#ddd;">—</span>
        <?php endif; ?>
      </td>
      <td>
        <div style="font-size:12px;font-weight:700;color:#1e1035;"><?= date('d M Y', strtotime($t['created_at'])) ?></div>
        <div style="font-size:11px;color:#aaa;"><?= date('h:i A', strtotime($t['created_at'])) ?></div>
      </td>
    </tr>
    <?php endwhile; ?>
    <?php if($count === 0): ?>
    <tr><td colspan="7">
      <div class="empty-state">
        <i class="fas fa-wallet"></i>
        <p>Koi transaction nahi mila</p>
      </div>
    </td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php include 'footer.php'; ?>
