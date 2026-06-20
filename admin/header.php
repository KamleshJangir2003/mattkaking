<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?? 'Admin' ?> - Crazy King Admin</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root{
  --primary:#6c2d7e;--primary-dark:#4a1d57;--primary-light:#9b4db5;
  --accent:#ff6b35;--accent2:#ffcc00;
  --bg:#f0f2f8;--sidebar-bg:#1e1035;
  --card:#fff;--text:#2d2d2d;--muted:#888;
  --green:#00b894;--red:#e84393;--blue:#0984e3;--orange:#e67e22;
  --sidebar-width:250px;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{background:var(--bg);font-family:'Segoe UI',Helvetica,sans-serif;color:var(--text);display:flex;min-height:100vh;}

/* SIDEBAR */
.sidebar{width:var(--sidebar-width);background:var(--sidebar-bg);min-height:100vh;position:fixed;top:0;left:0;z-index:100;display:flex;flex-direction:column;transition:.3s;}
.sidebar-logo{padding:22px 20px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:12px;}
.sidebar-logo .logo-icon{width:42px;height:42px;background:linear-gradient(135deg,var(--accent),var(--primary-light));border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;}
.sidebar-logo h2{color:#fff;font-size:16px;font-weight:800;letter-spacing:.5px;}
.sidebar-logo span{color:var(--accent2);font-size:11px;display:block;font-weight:400;}
.sidebar-menu{flex:1;padding:15px 0;overflow-y:auto;}
.menu-label{color:rgba(255,255,255,.3);font-size:10px;font-weight:700;letter-spacing:1.5px;padding:12px 20px 6px;text-transform:uppercase;}
.sidebar-menu a{display:flex;align-items:center;gap:12px;padding:11px 20px;color:rgba(255,255,255,.7);text-decoration:none;font-size:13.5px;font-weight:600;transition:.2s;border-left:3px solid transparent;margin:1px 0;}
.sidebar-menu a:hover{background:rgba(255,255,255,.07);color:#fff;}
.sidebar-menu a.active{background:linear-gradient(90deg,rgba(108,45,126,.5),rgba(108,45,126,.1));color:#fff;border-left:3px solid var(--accent2);}
.sidebar-menu a i{width:18px;text-align:center;font-size:14px;}
.sidebar-footer{padding:15px 20px;border-top:1px solid rgba(255,255,255,.08);}
.sidebar-footer a{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.6);text-decoration:none;font-size:13px;padding:8px;border-radius:8px;transition:.2s;}
.sidebar-footer a:hover{background:rgba(255,0,0,.15);color:#ff6b6b;}

/* MAIN */
.main-wrap{margin-left:var(--sidebar-width);flex:1;display:flex;flex-direction:column;min-height:100vh;}
.topbar{background:#fff;padding:14px 25px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 10px rgba(0,0,0,.06);position:sticky;top:0;z-index:50;}
.topbar-left h3{font-size:18px;font-weight:800;color:var(--primary-dark);}
.topbar-left p{font-size:12px;color:var(--muted);margin-top:2px;}
.topbar-right{display:flex;align-items:center;gap:15px;}
.admin-badge{display:flex;align-items:center;gap:10px;background:var(--bg);padding:8px 14px;border-radius:30px;}
.admin-badge .av{width:32px;height:32px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;}
.admin-badge span{font-size:13px;font-weight:700;color:var(--primary-dark);}
.topbar-btn{padding:8px 16px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;border:none;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:6px;}
.topbar-btn:hover{opacity:.9;}
.main-content{padding:25px;flex:1;}

/* MOBILE */
.menu-toggle{display:none;background:none;border:none;font-size:22px;color:var(--primary);cursor:pointer;}
@media(max-width:768px){
  .sidebar{left:-var(--sidebar-width);transform:translateX(-100%);}
  .sidebar.open{transform:translateX(0);}
  .main-wrap{margin-left:0;}
  .menu-toggle{display:block;}
  .main-content{padding:15px;}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">👑</div>
    <div>
      <h2>CRAZY KING</h2>
      <span>Admin Panel</span>
    </div>
  </div>
  <nav class="sidebar-menu">
    <div class="menu-label">Main</div>
    <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'class="active"':'' ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <div class="menu-label">Management</div>
    <a href="markets.php" <?= basename($_SERVER['PHP_SELF'])=='markets.php'?'class="active"':'' ?>><i class="fas fa-store"></i> Markets</a>
    <a href="results.php" <?= basename($_SERVER['PHP_SELF'])=='results.php'?'class="active"':'' ?>><i class="fas fa-trophy"></i> Results</a>
    <a href="bets.php" <?= basename($_SERVER['PHP_SELF'])=='bets.php'?'class="active"':'' ?>><i class="fas fa-dice"></i> Bets</a>
    <a href="users.php" <?= basename($_SERVER['PHP_SELF'])=='users.php'?'class="active"':'' ?>><i class="fas fa-users"></i> Users</a>
    <div class="menu-label">Finance</div>
    <a href="deposits.php" <?= basename($_SERVER['PHP_SELF'])=='deposits.php'?'class="active"':'' ?>><i class="fas fa-wallet"></i> Deposits</a>
    <a href="win_ratios.php" <?= basename($_SERVER['PHP_SELF'])=='win_ratios.php'?'class="active"':'' ?>><i class="fas fa-percent"></i> Win Ratios</a>
    <div class="menu-label">Site</div>
    <a href="../index.html" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
  </nav>
  <div class="sidebar-footer">
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</aside>

<!-- MAIN WRAPPER -->
<div class="main-wrap">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>
      <div class="topbar-left">
        <h3><?= $pageTitle ?? 'Dashboard' ?></h3>
        <p><?= date('l, d F Y') ?></p>
      </div>
    </div>
    <div class="topbar-right">
      <div class="admin-badge">
        <div class="av"><?= strtoupper(substr($_SESSION['admin_user'],0,1)) ?></div>
        <span><?= htmlspecialchars($_SESSION['admin_user']) ?></span>
      </div>
      <a href="logout.php" class="topbar-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
  <div class="main-content">
