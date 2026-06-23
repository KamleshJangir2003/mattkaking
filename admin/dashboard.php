<?php
require_once 'auth.php';
$pageTitle = 'Dashboard';

$total_users       = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$active_users      = $conn->query("SELECT COUNT(*) as c FROM users WHERE status='active'")->fetch_assoc()['c'];
$total_bets        = $conn->query("SELECT COUNT(*) as c FROM bets WHERE bet_date=CURDATE()")->fetch_assoc()['c'];
$total_bet_amount  = $conn->query("SELECT COALESCE(SUM(amount),0) as c FROM bets WHERE bet_date=CURDATE()")->fetch_assoc()['c'];
$total_win_amount  = $conn->query("SELECT COALESCE(SUM(win_amount),0) as c FROM bets WHERE status='won' AND bet_date=CURDATE()")->fetch_assoc()['c'];
$pending_bets      = $conn->query("SELECT COUNT(*) as c FROM bets WHERE status='pending'")->fetch_assoc()['c'];
$total_deposits    = $conn->query("SELECT COALESCE(SUM(amount),0) as c FROM transactions WHERE type='deposit' AND DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$total_markets     = $conn->query("SELECT COUNT(*) as c FROM markets WHERE status='active'")->fetch_assoc()['c'];
$total_withdrawals  = $conn->query("SELECT COALESCE(SUM(amount),0) as c FROM transactions WHERE type='withdraw' AND DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$total_wallet      = $conn->query("SELECT COALESCE(SUM(balance),0) as c FROM users")->fetch_assoc()['c'];
$profit            = $total_bet_amount - $total_win_amount;

$recent_bets = $conn->query("SELECT b.*,u.name,u.mobile,m.name as market_name FROM bets b JOIN users u ON b.user_id=u.id JOIN markets m ON b.market_id=m.id ORDER BY b.created_at DESC LIMIT 8");
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

include 'header.php';
?>

<style>
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px;margin-bottom:25px;}
.stat-card{background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:center;gap:16px;transition:.2s;}
.stat-card:hover{transform:translateY(-3px);box-shadow:0 8px 25px rgba(0,0,0,.1);}
.stat-icon{width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;}
.stat-info h2{font-size:26px;font-weight:800;color:#1e1035;line-height:1;}
.stat-info p{font-size:12px;color:#888;margin-top:4px;font-weight:600;}
.stat-info .trend{font-size:11px;margin-top:5px;font-weight:700;}
.trend.up{color:#00b894;} .trend.down{color:#e84393;}

.si-blue{background:#e8f4fd;color:#0984e3;}
.si-green{background:#e8faf4;color:#00b894;}
.si-red{background:#fde8f0;color:#e84393;}
.si-orange{background:#fef3e8;color:#e67e22;}
.si-purple{background:#f0e8ff;color:#6c2d7e;}
.si-yellow{background:#fffbe8;color:#f39c12;}
.si-teal{background:#e8fff9;color:#00cec9;}
.si-pink{background:#ffe8f5;color:#fd79a8;}

.dash-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;}
@media(max-width:900px){.dash-grid{grid-template-columns:1fr;}}

.card{background:#fff;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,.06);}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.card-header h3{font-size:15px;font-weight:800;color:#1e1035;display:flex;align-items:center;gap:8px;}
.card-header h3 i{color:var(--primary-light,#9b4db5);}
.view-all{font-size:12px;color:#6c2d7e;text-decoration:none;font-weight:700;padding:5px 12px;background:#f0e8ff;border-radius:20px;}
.view-all:hover{background:#6c2d7e;color:#fff;}

table{width:100%;border-collapse:collapse;}
th{background:#f8f5ff;color:#6c2d7e;font-size:11.5px;font-weight:800;padding:10px 12px;text-align:left;text-transform:uppercase;letter-spacing:.5px;}
td{padding:11px 12px;font-size:13px;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafafa;}

.badge{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;display:inline-block;}
.badge-won,.badge-active{background:#d4f5e9;color:#00875a;}
.badge-lost,.badge-blocked{background:#fde8ef;color:#c0003c;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-inactive{background:#e9ecef;color:#6c757d;}
.badge-open{background:#e3f2fd;color:#0277bd;}
.badge-close{background:#fce4ec;color:#880e4f;}

.user-row{display:flex;align-items:center;gap:8px;}
.user-av{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6c2d7e,#ff6b35);display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;flex-shrink:0;}

.profit-box{background:linear-gradient(135deg,#1e1035,#3d1a6e);border-radius:16px;padding:22px;color:#fff;margin-bottom:20px;}
.profit-box h4{font-size:12px;opacity:.7;text-transform:uppercase;letter-spacing:1px;}
.profit-box .amount{font-size:36px;font-weight:800;color:#ffcc00;margin:8px 0;}
.profit-box p{font-size:12px;opacity:.6;}
.profit-meta{display:flex;gap:20px;margin-top:15px;}
.profit-meta div{flex:1;background:rgba(255,255,255,.08);border-radius:10px;padding:12px;}
.profit-meta h5{font-size:11px;opacity:.6;margin-bottom:4px;}
.profit-meta span{font-size:16px;font-weight:800;}

.market-status{background:#fff;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,.06);}
.quick-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;}
.qa-btn{display:flex;align-items:center;gap:10px;padding:14px;background:#fff;border-radius:12px;text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,.06);transition:.2s;font-size:13px;font-weight:700;color:#1e1035;}
.qa-btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.1);}
.qa-btn i{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;}
.qa-btn.blue i{background:#e8f4fd;color:#0984e3;}
.qa-btn.green i{background:#e8faf4;color:#00b894;}
.qa-btn.orange i{background:#fef3e8;color:#e67e22;}
.qa-btn.purple i{background:#f0e8ff;color:#6c2d7e;}
</style>

<!-- STATS GRID -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon si-blue"><i class="fas fa-users"></i></div>
    <div class="stat-info">
      <h2><?= $total_users ?></h2>
      <p>Total Users</p>
      <div class="trend up"><i class="fas fa-circle" style="font-size:7px"></i> <?= $active_users ?> Active</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-orange"><i class="fas fa-dice"></i></div>
    <div class="stat-info">
      <h2><?= $total_bets ?></h2>
      <p>Today's Bets</p>
      <div class="trend <?= $pending_bets>0?'down':'up' ?>"><i class="fas fa-clock" style="font-size:9px"></i> <?= $pending_bets ?> Pending</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-purple"><i class="fas fa-rupee-sign"></i></div>
    <div class="stat-info">
      <h2>₹<?= number_format($total_bet_amount,0) ?></h2>
      <p>Today's Bet Amount</p>
      <div class="trend up"><i class="fas fa-arrow-up" style="font-size:9px"></i> Total collected</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-red"><i class="fas fa-gift"></i></div>
    <div class="stat-info">
      <h2>₹<?= number_format($total_win_amount,0) ?></h2>
      <p>Today's Win Amount</p>
      <div class="trend down"><i class="fas fa-arrow-down" style="font-size:9px"></i> Paid out</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-green"><i class="fas fa-chart-line"></i></div>
    <div class="stat-info">
      <h2>₹<?= number_format($profit,0) ?></h2>
      <p>Today's Profit</p>
      <div class="trend <?= $profit>=0?'up':'down' ?>"><?= $profit>=0?'▲ Profit':'▼ Loss' ?></div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-teal"><i class="fas fa-wallet"></i></div>
    <div class="stat-info">
      <h2>₹<?= number_format($total_deposits,0) ?></h2>
      <p>Today's Deposits</p>
      <div class="trend up"><i class="fas fa-arrow-up" style="font-size:9px"></i> Today</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-yellow"><i class="fas fa-store"></i></div>
    <div class="stat-info">
      <h2><?= $total_markets ?></h2>
      <p>Active Markets</p>
      <div class="trend up"><i class="fas fa-circle" style="font-size:7px"></i> Running</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-pink"><i class="fas fa-money-bill-wave"></i></div>
    <div class="stat-info">
      <h2>&#8377;<?= number_format($total_withdrawals,0) ?></h2>
      <p>Today's Withdrawals</p>
      <div class="trend down"><i class="fas fa-arrow-down" style="font-size:9px"></i> Paid today</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-teal" style="background:#e0f7f7;color:#00838f;"><i class="fas fa-piggy-bank"></i></div>
    <div class="stat-info">
      <h2>&#8377;<?= number_format($total_wallet,0) ?></h2>
      <p>Total Wallet Balance</p>
      <div class="trend up"><i class="fas fa-users" style="font-size:9px"></i> All users</div>
    </div>
  </div>
</div>

<!-- QUICK ACTIONS -->
<div class="quick-actions">
  <a href="markets.php" class="qa-btn blue"><i class="fas fa-store"></i> Manage Markets</a>
  <a href="results.php" class="qa-btn green"><i class="fas fa-trophy"></i> Post Results</a>
  <a href="deposits.php" class="qa-btn orange"><i class="fas fa-wallet"></i> Deposits</a>
  <a href="users.php" class="qa-btn purple"><i class="fas fa-users"></i> Manage Users</a>
</div>

<!-- MAIN GRID -->
<div class="dash-grid">

  <!-- RECENT BETS TABLE -->
  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-dice"></i> Recent Bets</h3>
      <a href="bets.php" class="view-all">View All →</a>
    </div>
    <div style="overflow-x:auto;">
    <table>
      <thead><tr><th>User</th><th>Market</th><th>Type</th><th>No.</th><th>Amount</th><th>Session</th><th>Status</th></tr></thead>
      <tbody>
      <?php while($b = $recent_bets->fetch_assoc()): ?>
      <tr>
        <td>
          <div class="user-row">
            <div class="user-av"><?= strtoupper(substr($b['name'],0,1)) ?></div>
            <div>
              <div style="font-weight:700;font-size:12px;"><?= htmlspecialchars($b['name']) ?></div>
              <div style="color:#aaa;font-size:11px;"><?= $b['mobile'] ?></div>
            </div>
          </div>
        </td>
        <td style="font-size:12px;font-weight:600;"><?= htmlspecialchars($b['market_name']) ?></td>
        <td><span class="badge" style="background:#f0e8ff;color:#6c2d7e;"><?= strtoupper($b['bet_type']) ?></span></td>
        <td><strong style="font-size:15px;"><?= $b['number'] ?></strong></td>
        <td><strong style="color:#1e1035;">₹<?= number_format($b['amount'],0) ?></strong></td>
        <td><span class="badge badge-<?= $b['session'] ?>"><?= strtoupper($b['session']) ?></span></td>
        <td><span class="badge badge-<?= $b['status'] ?>"><?= strtoupper($b['status']) ?></span></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
  </div>

  <!-- RIGHT COLUMN -->
  <div>
    <!-- PROFIT BOX -->
    <div class="profit-box" style="margin-bottom:20px;">
      <h4>Today's Net Profit</h4>
      <div class="amount">₹<?= number_format($profit,2) ?></div>
      <p><?= date('l, d M Y') ?></p>
      <div class="profit-meta">
        <div>
          <h5>Collected</h5>
          <span style="color:#00b894;">₹<?= number_format($total_bet_amount,0) ?></span>
        </div>
        <div>
          <h5>Paid Out</h5>
          <span style="color:#ff6b6b;">₹<?= number_format($total_win_amount,0) ?></span>
        </div>
      </div>
    </div>

    <!-- RECENT USERS -->
    <div class="card">
      <div class="card-header">
        <h3><i class="fas fa-user-plus"></i> New Users</h3>
        <a href="users.php" class="view-all">View All →</a>
      </div>
      <?php while($u = $recent_users->fetch_assoc()): ?>
      <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f5f5f5;">
        <div class="user-av" style="width:36px;height:36px;font-size:13px;"><?= strtoupper(substr($u['name'],0,1)) ?></div>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:700;"><?= htmlspecialchars($u['name']) ?></div>
          <div style="font-size:11px;color:#aaa;"><?= $u['mobile'] ?></div>
        </div>
        <span class="badge badge-<?= $u['status'] ?>"><?= $u['status'] ?></span>
      </div>
      <?php endwhile; ?>
    </div>
  </div>

</div>

<?php include 'footer.php'; ?>
