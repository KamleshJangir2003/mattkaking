<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?? 'Admin' ?> - Crazy King</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:#f5f5f5;font-family:Helvetica,sans-serif;}
.topbar{background:#1a0030;color:#fff;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;}
.topbar h1{font-size:18px;color:#ffcc00;}
.topbar a{color:#ffcc00;text-decoration:none;font-size:13px;margin-left:8px;}
.topbar a:hover{color:#fff;}
.navbar{background:#7c4066;display:flex;flex-wrap:wrap;gap:2px;padding:5px 10px;}
.navbar a{color:#fff;text-decoration:none;padding:7px 14px;border-radius:6px;font-size:13px;font-weight:700;}
.navbar a:hover,.navbar a.active{background:#ff0016;}
.container{padding:20px;max-width:1100px;margin:0 auto;}
.card{background:#fff;border-radius:10px;padding:20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.1);}
.card h3{color:#7c4066;margin-bottom:15px;font-size:18px;border-bottom:2px solid #f0f0f0;padding-bottom:8px;}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:20px;}
.stat-box{background:#fff;border-radius:10px;padding:18px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.1);border-top:4px solid #7c4066;}
.stat-box h2{font-size:28px;color:#1a0030;}
.stat-box p{font-size:13px;color:#666;margin-top:4px;}
.stat-box.green{border-color:#28a745;}.stat-box.red{border-color:#ff0016;}.stat-box.blue{border-color:#007bff;}.stat-box.orange{border-color:#ff9800;}
table{width:100%;border-collapse:collapse;}
th,td{padding:10px 12px;text-align:left;font-size:13px;border-bottom:1px solid #eee;}
th{background:#f0e8f4;color:#7c4066;font-weight:700;}
tr:hover{background:#fafafa;}
.btn{padding:7px 14px;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:700;text-decoration:none;display:inline-block;}
.btn-primary{background:#7c4066;color:#fff;}.btn-primary:hover{background:#5a2e4a;}
.btn-success{background:#28a745;color:#fff;}.btn-danger{background:#ff0016;color:#fff;}
.btn-warning{background:#ff9800;color:#fff;}.btn-info{background:#007bff;color:#fff;}
.badge{padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;}
.badge-active,.badge-won{background:#d4edda;color:#155724;}
.badge-blocked,.badge-lost{background:#f8d7da;color:#721c24;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-inactive{background:#e2e3e5;color:#383d41;}
.form-group{margin-bottom:15px;}
.form-group label{display:block;font-size:13px;font-weight:700;color:#333;margin-bottom:5px;}
.form-group input,.form-group select{width:100%;padding:9px 12px;border:2px solid #ddd;border-radius:8px;font-size:14px;}
.form-group input:focus,.form-group select:focus{border-color:#7c4066;outline:none;}
.alert{padding:12px 15px;border-radius:8px;margin-bottom:15px;font-size:14px;}
.alert-success{background:#d4edda;color:#155724;}.alert-danger{background:#f8d7da;color:#721c24;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:15px;}
@media(max-width:600px){.form-row{grid-template-columns:1fr;}.navbar a{padding:5px 8px;font-size:12px;}}
</style>
</head>
<body>
<div class="topbar">
  <h1>👑 CRAZY KING ADMIN</h1>
  <div>
    <span style="font-size:13px;">Hello, <?= htmlspecialchars($_SESSION['admin_user']) ?></span>
    <a href="logout.php">Logout</a>
  </div>
</div>
<div class="navbar">
  <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'class="active"':'' ?>>Dashboard</a>
  <a href="markets.php" <?= basename($_SERVER['PHP_SELF'])=='markets.php'?'class="active"':'' ?>>Markets</a>
  <a href="results.php" <?= basename($_SERVER['PHP_SELF'])=='results.php'?'class="active"':'' ?>>Results</a>
  <a href="bets.php" <?= basename($_SERVER['PHP_SELF'])=='bets.php'?'class="active"':'' ?>>Bets</a>
  <a href="users.php" <?= basename($_SERVER['PHP_SELF'])=='users.php'?'class="active"':'' ?>>Users</a>
  <a href="deposits.php" <?= basename($_SERVER['PHP_SELF'])=='deposits.php'?'class="active"':'' ?>>Deposits</a>
  <a href="win_ratios.php" <?= basename($_SERVER['PHP_SELF'])=='win_ratios.php'?'class="active"':'' ?>>Win Ratios</a>
  <a href="../index.html" target="_blank">View Site</a>
</div>
<div class="container">
