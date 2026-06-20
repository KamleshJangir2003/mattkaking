<?php
require_once 'auth.php';
$pageTitle = 'Users';
$msg = '';
$msg_type = 'success';

if (isset($_POST['update_balance'])) {
    $uid = (int)$_POST['user_id'];
    $amount = (float)$_POST['amount'];
    $type = sanitize($conn, $_POST['type']);
    if ($type === 'add') {
        updateBalance($conn, $uid, $amount, 'add');
        addTransaction($conn, $uid, 'deposit', $amount, 'Admin credit');
        $msg = 'Balance successfully add ho gaya!';
    } else {
        $bal = getUserBalance($conn, $uid);
        if ($bal >= $amount) {
            updateBalance($conn, $uid, $amount, 'subtract');
            addTransaction($conn, $uid, 'withdraw', $amount, 'Admin debit');
            $msg = 'Balance successfully deduct ho gaya!';
        } else {
            $msg = 'Insufficient balance! User ke paas itna balance nahi hai.';
            $msg_type = 'error';
        }
    }
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE users SET status=IF(status='active','blocked','active') WHERE id=$id");
    header("Location: users.php"); exit;
}

$search = isset($_GET['q']) ? sanitize($conn, $_GET['q']) : '';
$filter = isset($_GET['f']) ? sanitize($conn, $_GET['f']) : 'all';

$where = "WHERE 1=1";
if ($search) $where .= " AND (name LIKE '%$search%' OR mobile LIKE '%$search%')";
if ($filter === 'active') $where .= " AND status='active'";
if ($filter === 'blocked') $where .= " AND status='blocked'";

$users = $conn->query("SELECT * FROM users $where ORDER BY created_at DESC");
$total_all     = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_active  = $conn->query("SELECT COUNT(*) as c FROM users WHERE status='active'")->fetch_assoc()['c'];
$total_blocked = $conn->query("SELECT COUNT(*) as c FROM users WHERE status='blocked'")->fetch_assoc()['c'];
$total_bal     = $conn->query("SELECT COALESCE(SUM(balance),0) as c FROM users")->fetch_assoc()['c'];

include 'header.php';
?>

<style>
.users-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px;}
.ustat{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 2px 10px rgba(0,0,0,.06);display:flex;align-items:center;gap:14px;border-left:4px solid var(--uc);}
.ustat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;background:rgba(var(--uc-rgb),.1);color:var(--uc);flex-shrink:0;}
.ustat h3{font-size:24px;font-weight:800;color:#1e1035;}
.ustat p{font-size:12px;color:#888;margin-top:2px;font-weight:600;}

.toolbar{background:#fff;border-radius:14px;padding:16px 20px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.search-box{flex:1;min-width:200px;position:relative;}
.search-box i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;}
.search-box input{width:100%;padding:10px 12px 10px 36px;border:2px solid #ece8f5;border-radius:10px;font-size:13px;outline:none;transition:.2s;}
.search-box input:focus{border-color:#6c2d7e;}
.filter-tabs{display:flex;gap:6px;flex-wrap:wrap;}
.ftab{padding:8px 16px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:2px solid #ece8f5;background:#fff;color:#888;text-decoration:none;transition:.2s;}
.ftab:hover{border-color:#6c2d7e;color:#6c2d7e;}
.ftab.active{background:#6c2d7e;color:#fff;border-color:#6c2d7e;}
.ftab.green.active{background:#00b894;border-color:#00b894;}
.ftab.red.active{background:#e84393;border-color:#e84393;}

.card{background:#fff;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,.06);}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.card-header h3{font-size:15px;font-weight:800;color:#1e1035;display:flex;align-items:center;gap:8px;}

table{width:100%;border-collapse:collapse;}
th{background:#f8f5ff;color:#6c2d7e;font-size:11px;font-weight:800;padding:11px 14px;text-align:left;text-transform:uppercase;letter-spacing:.6px;}
td{padding:12px 14px;font-size:13px;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fdfbff;}

.user-av{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:800;flex-shrink:0;}
.badge{padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700;display:inline-block;}
.badge-active{background:#d4f5e9;color:#00875a;}
.badge-blocked{background:#fde8ef;color:#c0003c;}

.action-btn{padding:6px 13px;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:.15s;}
.action-btn:hover{opacity:.85;transform:translateY(-1px);}
.btn-bal{background:#e8f4fd;color:#0984e3;}
.btn-block{background:#fde8ef;color:#c0003c;}
.btn-unblock{background:#d4f5e9;color:#00875a;}
.btn-bets{background:#f0e8ff;color:#6c2d7e;}

.alert-toast{display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:13px;font-weight:700;}
.alert-toast.success{background:#e8faf4;color:#00875a;border:1.5px solid #b2dfdb;}
.alert-toast.error{background:#fde8ef;color:#c0003c;border:1.5px solid #f5c6cb;}

/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(10,5,30,.6);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(3px);}
.modal-overlay.show{display:flex;}
.modal-box{background:#fff;border-radius:20px;width:380px;max-width:95%;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.2);}
.modal-head{background:linear-gradient(135deg,#1e1035,#3d1a6e);padding:22px 24px;display:flex;align-items:center;gap:14px;}
.modal-head-icon{width:44px;height:44px;background:rgba(255,204,0,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;}
.modal-head h3{color:#fff;font-size:16px;font-weight:800;}
.modal-head p{color:rgba(255,255,255,.5);font-size:12px;margin-top:2px;}
.modal-body{padding:24px;}
.modal-user-info{background:#f8f5ff;border-radius:12px;padding:14px;margin-bottom:18px;display:flex;align-items:center;gap:12px;}
.modal-user-info h4{font-size:14px;font-weight:800;color:#1e1035;}
.modal-user-info span{font-size:12px;color:#888;}
.modal-balance{font-size:20px;font-weight:800;color:#00b894;margin-top:4px;}
.mform-group{margin-bottom:14px;}
.mform-group label{display:block;font-size:11px;font-weight:800;color:#888;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px;}
.mform-group select,.mform-group input{width:100%;padding:11px 14px;border:2px solid #ece8f5;border-radius:10px;font-size:14px;font-weight:700;color:#1e1035;outline:none;transition:.2s;}
.mform-group select:focus,.mform-group input:focus{border-color:#6c2d7e;}
.type-tabs{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;}
.type-tab{padding:11px;border-radius:10px;border:2px solid #ece8f5;background:#fff;cursor:pointer;text-align:center;font-size:12px;font-weight:700;color:#888;transition:.2s;}
.type-tab.selected-add{border-color:#00b894;background:#e8faf4;color:#00875a;}
.type-tab.selected-deduct{border-color:#e84393;background:#fde8ef;color:#c0003c;}
.modal-footer{display:flex;gap:10px;padding:0 24px 24px;}
.mfooter-btn{flex:1;padding:12px;border-radius:12px;font-size:14px;font-weight:800;border:none;cursor:pointer;transition:.2s;}
.mfooter-btn:hover{opacity:.9;transform:translateY(-1px);}
.mbtn-save{background:linear-gradient(135deg,#6c2d7e,#9b4db5);color:#fff;}
.mbtn-cancel{background:#f5f5f5;color:#888;}
</style>

<?php if($msg): ?>
<div class="alert-toast <?= $msg_type ?>">
  <i class="fas fa-<?= $msg_type==='success'?'check-circle':'exclamation-circle' ?>"></i>
  <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<!-- STATS -->
<div class="users-stats">
  <div class="ustat" style="--uc:#0984e3;--uc-rgb:9,132,227;">
    <div class="ustat-icon"><i class="fas fa-users"></i></div>
    <div><h3><?= $total_all ?></h3><p>Total Users</p></div>
  </div>
  <div class="ustat" style="--uc:#00b894;--uc-rgb:0,184,148;">
    <div class="ustat-icon"><i class="fas fa-user-check"></i></div>
    <div><h3><?= $total_active ?></h3><p>Active Users</p></div>
  </div>
  <div class="ustat" style="--uc:#e84393;--uc-rgb:232,67,147;">
    <div class="ustat-icon"><i class="fas fa-user-slash"></i></div>
    <div><h3><?= $total_blocked ?></h3><p>Blocked Users</p></div>
  </div>
  <div class="ustat" style="--uc:#6c2d7e;--uc-rgb:108,45,126;">
    <div class="ustat-icon"><i class="fas fa-wallet"></i></div>
    <div><h3>₹<?= number_format($total_bal,0) ?></h3><p>Total Balance</p></div>
  </div>
</div>

<!-- TOOLBAR -->
<form method="GET">
<div class="toolbar">
  <div class="search-box">
    <i class="fas fa-search"></i>
    <input type="text" name="q" placeholder="Name ya mobile se search karein..." value="<?= htmlspecialchars($search) ?>">
  </div>
  <div class="filter-tabs">
    <a href="users.php" class="ftab <?= $filter==='all'?'active':'' ?>">All (<?= $total_all ?>)</a>
    <a href="users.php?f=active<?= $search?"&q=$search":'' ?>" class="ftab green <?= $filter==='active'?'active':'' ?>">Active (<?= $total_active ?>)</a>
    <a href="users.php?f=blocked<?= $search?"&q=$search":'' ?>" class="ftab red <?= $filter==='blocked'?'active':'' ?>">Blocked (<?= $total_blocked ?>)</a>
  </div>
  <button type="submit" style="padding:10px 18px;background:#6c2d7e;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;"><i class="fas fa-search"></i></button>
</div>
</form>

<!-- TABLE -->
<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-users" style="color:#6c2d7e;"></i> Users List</h3>
    <span style="font-size:12px;color:#aaa;"><?= $users->num_rows ?> users found</span>
  </div>
  <div style="overflow-x:auto;">
  <table>
    <thead>
      <tr><th>#</th><th>User</th><th>Mobile</th><th>Balance</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php
    $colors = ['#6c2d7e','#0984e3','#00b894','#e67e22','#e84393','#f39c12','#00cec9'];
    $i = 0;
    while($u = $users->fetch_assoc()):
      $color = $colors[$u['id'] % count($colors)];
    ?>
    <tr>
      <td style="color:#aaa;font-size:12px;font-weight:700;">#<?= $u['id'] ?></td>
      <td>
        <div style="display:flex;align-items:center;gap:10px;">
          <div class="user-av" style="background:<?= $color ?>"><?= strtoupper(substr($u['name'],0,1)) ?></div>
          <div>
            <div style="font-weight:800;font-size:13px;"><?= htmlspecialchars($u['name']) ?></div>
            <div style="font-size:11px;color:#aaa;">ID: <?= $u['id'] ?></div>
          </div>
        </div>
      </td>
      <td style="font-weight:700;letter-spacing:.3px;"><?= $u['mobile'] ?></td>
      <td><strong style="font-size:14px;color:#1e1035;">₹<?= number_format($u['balance'],2) ?></strong></td>
      <td><span class="badge badge-<?= $u['status'] ?>"><?= $u['status']==='active'?'✓ Active':'✕ Blocked' ?></span></td>
      <td style="font-size:12px;color:#888;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
      <td>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
          <button class="action-btn btn-bal" onclick="openModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name'],ENT_QUOTES) ?>', <?= $u['balance'] ?>)">
            <i class="fas fa-wallet"></i> Balance
          </button>
          <a href="?toggle=<?= $u['id'] ?><?= $search?"&q=$search":'' ?><?= $filter!='all'?"&f=$filter":'' ?>" class="action-btn <?= $u['status']==='active'?'btn-block':'btn-unblock' ?>">
            <i class="fas fa-<?= $u['status']==='active'?'ban':'check' ?>"></i>
            <?= $u['status']==='active'?'Block':'Unblock' ?>
          </a>
          <a href="user_bets.php?user_id=<?= $u['id'] ?>" class="action-btn btn-bets">
            <i class="fas fa-dice"></i> Bets
          </a>
        </div>
      </td>
    </tr>
    <?php $i++; endwhile; ?>
    <?php if($i === 0): ?>
    <tr><td colspan="7" style="text-align:center;padding:40px;color:#aaa;"><i class="fas fa-search" style="font-size:30px;display:block;margin-bottom:10px;"></i>Koi user nahi mila</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- BALANCE MODAL -->
<div class="modal-overlay" id="balanceModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-head-icon">💰</div>
      <div>
        <h3>Balance Update</h3>
        <p>User ka balance add ya deduct karein</p>
      </div>
    </div>
    <form method="POST">
      <input type="hidden" name="user_id" id="modal_uid">
      <div class="modal-body">
        <div class="modal-user-info">
          <div class="user-av" id="modal_av" style="background:#6c2d7e;width:40px;height:40px;font-size:15px;"></div>
          <div>
            <h4 id="modal_name">-</h4>
            <span>Current Balance</span>
            <div class="modal-balance" id="modal_bal">₹0.00</div>
          </div>
        </div>
        <div class="type-tabs">
          <div class="type-tab" id="tab_add" onclick="selectType('add')"><i class="fas fa-plus-circle"></i><br>Add Balance</div>
          <div class="type-tab" id="tab_deduct" onclick="selectType('deduct')"><i class="fas fa-minus-circle"></i><br>Deduct Balance</div>
        </div>
        <input type="hidden" name="type" id="modal_type" value="add">
        <div class="mform-group">
          <label>Amount (₹)</label>
          <input type="number" name="amount" min="1" required placeholder="e.g. 500">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="mfooter-btn mbtn-cancel" onclick="closeModal()">Cancel</button>
        <button type="submit" name="update_balance" class="mfooter-btn mbtn-save"><i class="fas fa-save"></i> Update</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id, name, bal) {
  document.getElementById('modal_uid').value = id;
  document.getElementById('modal_name').textContent = name;
  document.getElementById('modal_bal').textContent = '₹' + parseFloat(bal).toLocaleString('en-IN', {minimumFractionDigits:2});
  document.getElementById('modal_av').textContent = name.charAt(0).toUpperCase();
  selectType('add');
  document.getElementById('balanceModal').classList.add('show');
}
function closeModal() {
  document.getElementById('balanceModal').classList.remove('show');
}
function selectType(t) {
  document.getElementById('modal_type').value = t;
  document.getElementById('tab_add').className = 'type-tab' + (t==='add'?' selected-add':'');
  document.getElementById('tab_deduct').className = 'type-tab' + (t==='deduct'?' selected-deduct':'');
}
document.getElementById('balanceModal').addEventListener('click', function(e){
  if(e.target === this) closeModal();
});
selectType('add');
</script>

<?php include 'footer.php'; ?>
