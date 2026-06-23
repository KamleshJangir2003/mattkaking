<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?? 'My Account' ?> - Crazy King</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:#ffaa33;font-family:Helvetica,sans-serif;font-weight:700;font-style:italic;min-height:100vh;padding-bottom:70px;}

/* TOPBAR */
.topbar{background:linear-gradient(135deg,#1a0030,#3a0060);color:#fff;padding:10px 15px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 10px rgba(0,0,0,.4);}
.topbar .logo{color:#ffcc00;font-size:17px;letter-spacing:1px;}
.topbar a{color:#ffcc00;text-decoration:none;font-size:12px;background:rgba(255,204,0,.15);padding:5px 10px;border-radius:20px;border:1px solid #ffcc00;}

/* BALANCE BAR */
.balance-bar{background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;text-align:center;padding:10px 15px;font-size:14px;display:flex;align-items:center;justify-content:space-between;}
.balance-bar .bal-amount{color:#ffcc00;font-size:20px;font-weight:900;}
.balance-bar .bal-label{font-size:11px;opacity:.8;font-style:normal;}
.balance-bar .add-btn{background:#ffcc00;color:#1a0030;padding:5px 12px;border-radius:20px;font-size:12px;text-decoration:none;border:none;cursor:pointer;}

/* NAVBAR */
.navbar{background:#1a0030;display:flex;overflow-x:auto;padding:6px 8px;gap:4px;scrollbar-width:none;}
.navbar::-webkit-scrollbar{display:none;}
.navbar a{color:#ccc;text-decoration:none;padding:7px 14px;border-radius:20px;font-size:12px;font-weight:700;white-space:nowrap;font-style:normal;transition:all .2s;}
.navbar a:hover{color:#fff;background:#7c4066;}
.navbar a.active{color:#ffcc00;background:#7c4066;}

/* CONTAINER */
.container{padding:12px;}

/* CARDS */
.card{background:#fff;border:2px solid #ff182c;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 4px 15px rgba(0,0,0,.15);}
.card-title{color:#7c4066;font-size:16px;border-bottom:2px solid #f5e6f0;padding-bottom:10px;margin-bottom:14px;display:flex;align-items:center;gap:8px;}

/* FORM */
.form-group{margin-bottom:14px;}
.form-group label{display:block;font-size:12px;color:#555;margin-bottom:5px;font-style:normal;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.form-group input,.form-group select{width:100%;padding:11px 13px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;font-family:inherit;font-style:normal;transition:border .2s;background:#fafafa;}
.form-group input:focus,.form-group select:focus{border-color:#7c4066;outline:none;background:#fff;box-shadow:0 0 0 3px rgba(124,64,102,.1);}
.form-group input.error-field{border-color:#ff0016;}

/* BUTTONS */
.btn{padding:12px 20px;border:none;border-radius:10px;cursor:pointer;font-size:14px;font-weight:700;font-family:inherit;font-style:normal;text-decoration:none;display:inline-block;text-align:center;transition:all .2s;letter-spacing:.5px;}
.btn-primary{background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;width:100%;}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 15px rgba(124,64,102,.4);}
.btn-primary:active{transform:translateY(0);}
.btn-success{background:linear-gradient(135deg,#28a745,#1e7e34);color:#fff;}
.btn-danger{background:linear-gradient(135deg,#ff0016,#c0001e);color:#fff;}
.btn-yellow{background:linear-gradient(135deg,#ffcc00,#ff9900);color:#1a0030;}
.btn-sm{padding:6px 12px;font-size:12px;border-radius:8px;}

/* BADGES */
.badge{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;font-style:normal;display:inline-block;}
.badge-won{background:#d4edda;color:#155724;}
.badge-lost{background:#f8d7da;color:#721c24;}
.badge-pending{background:#fff3cd;color:#856404;}
.badge-cancelled{background:#e9ecef;color:#6c757d;}
.badge-active{background:#d4edda;color:#155724;}
.badge-blocked{background:#f8d7da;color:#721c24;}

/* TABLES */
table{width:100%;border-collapse:collapse;font-size:12px;font-style:normal;}
th,td{padding:9px 8px;text-align:left;border-bottom:1px solid #f0e0f0;}
th{background:linear-gradient(135deg,#f0e8f4,#e8d8f0);color:#7c4066;font-size:11px;text-transform:uppercase;letter-spacing:.5px;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fdf5fb;}

/* MARKET CARD */
.market-item{border:2px solid #ff182c;border-radius:12px;padding:12px 14px;margin-bottom:10px;background:#fff;display:flex;align-items:center;gap:10px;flex-wrap:wrap;transition:all .2s;}
.market-item:hover{box-shadow:0 4px 15px rgba(255,24,44,.2);transform:translateY(-1px);}
.market-item.highlight{background:#fffde7;border-color:#ff9800;}
.market-item .mkt-name{color:#00094d;font-size:14px;flex:1;}
.market-item .mkt-time{font-size:10px;color:#888;font-style:normal;font-weight:normal;margin-top:2px;}
.market-item .mkt-result{font-size:17px;color:#670009;font-weight:900;min-width:100px;text-align:center;}
.market-item .mkt-result.pending{color:#aaa;font-size:13px;font-style:normal;font-weight:normal;}
.bet-now-btn{background:linear-gradient(135deg,#522f92,#7c4066);color:#fff;padding:8px 14px;border-radius:8px;font-size:12px;text-decoration:none;font-style:normal;white-space:nowrap;transition:all .2s;}
.bet-now-btn:hover{transform:scale(1.05);box-shadow:0 3px 10px rgba(82,47,146,.4);}

/* STAT BOXES */
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:12px;}
.stat-box{background:#fff;border:2px solid #ff182c;border-radius:12px;padding:12px 8px;text-align:center;}
.stat-box .val{font-size:20px;color:#1a0030;line-height:1;}
.stat-box .lbl{font-size:10px;color:#888;font-style:normal;font-weight:normal;margin-top:4px;}
.stat-box.green .val{color:#28a745;}
.stat-box.red .val{color:#ff0016;}
.stat-box.purple .val{color:#7c4066;}

/* POPUP / MODAL */
.popup-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;display:none;align-items:center;justify-content:center;padding:15px;backdrop-filter:blur(3px);}
.popup-overlay.show{display:flex;}
.popup-box{background:#fff;border-radius:16px;width:100%;max-width:360px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.4);animation:popIn .25s ease;}
@keyframes popIn{from{transform:scale(.8);opacity:0}to{transform:scale(1);opacity:1}}
.popup-header{padding:16px 20px;display:flex;align-items:center;gap:10px;}
.popup-header.error{background:linear-gradient(135deg,#ff0016,#c0001e);}
.popup-header.success{background:linear-gradient(135deg,#28a745,#1e7e34);}
.popup-header.info{background:linear-gradient(135deg,#7c4066,#a0005a);}
.popup-header.warning{background:linear-gradient(135deg,#ff9800,#e65c00);}
.popup-header h3{color:#fff;font-size:16px;flex:1;}
.popup-header .popup-icon{font-size:22px;}
.popup-body{padding:18px 20px;}
.popup-body p{font-size:14px;color:#333;line-height:1.6;font-style:normal;font-weight:normal;}
.popup-body .detail-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0;font-size:13px;}
.popup-body .detail-row:last-child{border:none;}
.popup-body .detail-row .key{color:#777;font-style:normal;font-weight:normal;}
.popup-body .detail-row .val{color:#1a0030;font-style:normal;}
.popup-footer{padding:14px 20px;background:#f9f9f9;display:flex;gap:10px;}
.popup-footer button,.popup-footer a{flex:1;padding:11px;border-radius:10px;font-size:14px;font-weight:700;font-family:inherit;cursor:pointer;border:none;text-align:center;text-decoration:none;font-style:normal;}
.popup-footer .ok-btn{background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;}
.popup-footer .cancel-btn{background:#eee;color:#333;}

/* TOAST */
.toast-container{position:fixed;top:15px;right:15px;z-index:99999;display:flex;flex-direction:column;gap:8px;}
.toast{background:#1a0030;color:#fff;padding:12px 16px;border-radius:10px;font-size:13px;font-style:normal;max-width:280px;box-shadow:0 4px 20px rgba(0,0,0,.3);display:flex;align-items:center;gap:10px;animation:slideIn .3s ease;border-left:4px solid #7c4066;}
.toast.toast-success{border-color:#28a745;}
.toast.toast-error{border-color:#ff0016;}
.toast.toast-warning{border-color:#ff9800;}
@keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
@keyframes slideOut{to{transform:translateX(110%);opacity:0}}

/* BOTTOM NAV */
.bottom-nav{position:fixed;bottom:0;left:0;right:0;background:linear-gradient(135deg,#1a0030,#3a0060);display:flex;z-index:100;box-shadow:0 -3px 15px rgba(0,0,0,.3);}
.bottom-nav a{flex:1;display:flex;flex-direction:column;align-items:center;padding:8px 4px;color:#aaa;text-decoration:none;font-size:10px;font-style:normal;font-weight:700;gap:3px;transition:all .2s;}
.bottom-nav a .bnav-icon{font-size:20px;line-height:1;}
.bottom-nav a.active,.bottom-nav a:hover{color:#ffcc00;}

/* BET TYPE CARDS */
.bet-type-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:14px;}
.bet-type-card{border:2px solid #e0e0e0;border-radius:10px;padding:10px;cursor:pointer;text-align:center;transition:all .2s;background:#fafafa;font-style:normal;}
.bet-type-card:hover{border-color:#7c4066;background:#f5e6f0;}
.bet-type-card.selected{border-color:#7c4066;background:linear-gradient(135deg,#f0e8f4,#e8d0e8);}
.bet-type-card .bt-name{font-size:13px;font-weight:700;color:#1a0030;}
.bet-type-card .bt-ratio{font-size:11px;color:#7c4066;margin-top:3px;}
.bet-type-card .bt-example{font-size:10px;color:#888;margin-top:2px;}

/* SESSION TOGGLE */
.session-toggle{display:flex;border:2px solid #e0e0e0;border-radius:10px;overflow:hidden;margin-bottom:14px;}
.session-toggle label{flex:1;text-align:center;padding:10px;cursor:pointer;font-size:13px;font-style:normal;transition:all .2s;color:#777;}
.session-toggle input{display:none;}
.session-toggle input:checked + span{background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;}
.session-toggle label:has(input:checked){background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;}

/* WIN PREVIEW BOX */
.win-preview{background:linear-gradient(135deg,#d4edda,#b8dfc2);border:2px solid #28a745;border-radius:10px;padding:12px;margin-bottom:14px;display:none;font-style:normal;}
.win-preview .wp-label{font-size:11px;color:#155724;text-transform:uppercase;letter-spacing:.5px;}
.win-preview .wp-amount{font-size:24px;color:#155724;font-weight:900;}

/* HINT BOX */
.hint-box{background:linear-gradient(135deg,#e8f4ff,#d0e8ff);border:1px solid #90c8ff;border-radius:10px;padding:10px 12px;margin-bottom:14px;display:none;font-style:normal;}
.hint-box p{font-size:12px;color:#004085;line-height:1.5;}
.hint-box .hint-examples span{display:inline-block;background:#fff;border:1px solid #90c8ff;border-radius:5px;padding:2px 8px;font-size:11px;margin:3px 2px;color:#004085;font-weight:700;}

/* NUMBER INPUT GROUP */
.number-input-wrap{position:relative;}
.number-input-wrap input{padding-right:45px;}
.number-input-wrap .num-clear{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#bbb;font-size:18px;cursor:pointer;padding:0;}
.number-input-wrap .num-clear:hover{color:#ff0016;}

@media(max-width:400px){
  .bet-type-grid{grid-template-columns:1fr 1fr;}
  .stat-row{grid-template-columns:repeat(3,1fr);}
}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="logo">👑 CRAZY KING</div>
  <a href="logout.php">🚪 Logout</a>
</div>

<!-- BALANCE BAR -->
<div class="balance-bar">
  <div>
    <div class="bal-label">Available Balance</div>
    <div class="bal-amount">₹<?= number_format(getUserBalance($conn, $_SESSION['user_id']), 2) ?></div>
  </div>
  <div style="text-align:right;">
    <div class="bal-label">Hello, <?= htmlspecialchars($_SESSION['user_name']) ?></div>
    <a href="wallet.php" class="add-btn" style="display:inline-block;margin-top:4px;">+ Add Money</a>
  </div>
</div>

<!-- BOTTOM NAV -->
<div class="bottom-nav">
  <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'class="active"':'' ?>>
    <span class="bnav-icon">🏠</span>Home
  </a>
  <a href="play.php" <?= basename($_SERVER['PHP_SELF'])=='play.php'?'class="active"':'' ?>>
    <span class="bnav-icon">🎰</span>Play
  </a>
  <a href="my_bets.php" <?= basename($_SERVER['PHP_SELF'])=='my_bets.php'?'class="active"':'' ?>>
    <span class="bnav-icon">📋</span>My Bets
  </a>
  <a href="wallet.php" <?= basename($_SERVER['PHP_SELF'])=='wallet.php'?'class="active"':'' ?>>
    <span class="bnav-icon">💰</span>Wallet
  </a>
  <a href="../index.html">
    <span class="bnav-icon">🌐</span>Site
  </a>
</div>

<!-- TOAST CONTAINER -->
<div class="toast-container" id="toastContainer"></div>

<!-- POPUP -->
<div class="popup-overlay" id="globalPopup">
  <div class="popup-box">
    <div class="popup-header" id="popupHeader">
      <span class="popup-icon" id="popupIcon"></span>
      <h3 id="popupTitle"></h3>
    </div>
    <div class="popup-body" id="popupBody"></div>
    <div class="popup-footer">
      <button class="ok-btn" onclick="closePopup()" id="popupOkBtn">OK</button>
    </div>
  </div>
</div>

<div class="container">

<script>
// POPUP SYSTEM
function showPopup(type, title, body, onOk) {
  var icons = {error:'❌', success:'✅', info:'ℹ️', warning:'⚠️'};
  document.getElementById('popupIcon').textContent = icons[type] || 'ℹ️';
  document.getElementById('popupTitle').textContent = title;
  document.getElementById('popupHeader').className = 'popup-header ' + type;
  document.getElementById('popupBody').innerHTML = body;
  document.getElementById('globalPopup').classList.add('show');
  if (onOk) document.getElementById('popupOkBtn').onclick = function(){ closePopup(); onOk(); };
  else document.getElementById('popupOkBtn').onclick = closePopup;
}
function closePopup() {
  document.getElementById('globalPopup').classList.remove('show');
}
function showConfirmPopup(title, body, onConfirm) {
  document.getElementById('popupIcon').textContent = '❓';
  document.getElementById('popupTitle').textContent = title;
  document.getElementById('popupHeader').className = 'popup-header info';
  document.getElementById('popupBody').innerHTML = body;
  document.getElementById('globalPopup').classList.add('show');
  var footer = document.querySelector('.popup-footer');
  footer.innerHTML = '<button class="cancel-btn" onclick="closePopup()">Cancel</button><button class="ok-btn" id="confirmYesBtn">Confirm</button>';
  document.getElementById('confirmYesBtn').onclick = function(){ closePopup(); onConfirm(); };
}
function showToast(msg, type) {
  type = type || 'info';
  var icons = {success:'✅', error:'❌', warning:'⚠️', info:'ℹ️'};
  var tc = document.getElementById('toastContainer');
  var t = document.createElement('div');
  t.className = 'toast toast-' + type;
  t.innerHTML = '<span>' + icons[type] + '</span><span>' + msg + '</span>';
  tc.appendChild(t);
  setTimeout(function(){ t.style.animation = 'slideOut .3s ease forwards'; setTimeout(function(){ t.remove(); }, 300); }, 3000);
}
document.getElementById('globalPopup').addEventListener('click', function(e){ if(e.target===this) closePopup(); });
</script>
