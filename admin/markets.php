<?php
require_once 'auth.php';
$pageTitle = 'Markets';
$msg = ''; $msg_type = 'success';

if (isset($_POST['add'])) {
    $name  = sanitize($conn, trim($_POST['name']));
    $slug  = sanitize($conn, strtolower(str_replace(' ', '-', trim($_POST['name']))));
    $open  = sanitize($conn, $_POST['open_time']);
    $close = sanitize($conn, $_POST['close_time']);
    $conn->query("INSERT INTO markets (name,slug,open_time,close_time) VALUES ('$name','$slug','$open','$close')");
    $msg = "Market <strong>$name</strong> successfully add ho gaya!";
}

if (isset($_POST['edit'])) {
    $id    = (int)$_POST['edit_id'];
    $name  = sanitize($conn, trim($_POST['name']));
    $open  = sanitize($conn, $_POST['open_time']);
    $close = sanitize($conn, $_POST['close_time']);
    $conn->query("UPDATE markets SET name='$name',open_time='$open',close_time='$close' WHERE id=$id");
    $msg = "Market <strong>$name</strong> successfully update ho gaya!";
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE markets SET status=IF(status='active','inactive','active') WHERE id=$id");
    header("Location: markets.php"); exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM markets WHERE id=$id");
    header("Location: markets.php"); exit;
}

$markets       = $conn->query("SELECT * FROM markets ORDER BY id");
$total_active  = $conn->query("SELECT COUNT(*) as c FROM markets WHERE status='active'")->fetch_assoc()['c'];
$total_inactive= $conn->query("SELECT COUNT(*) as c FROM markets WHERE status='inactive'")->fetch_assoc()['c'];
$total_markets = $total_active + $total_inactive;

include 'header.php';
?>

<style>
.mkt-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:22px;}
.mstat{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 2px 10px rgba(0,0,0,.06);display:flex;align-items:center;gap:14px;border-left:4px solid var(--mc);}
.mstat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;background:rgba(var(--mc-rgb),.1);color:var(--mc);flex-shrink:0;}
.mstat h3{font-size:24px;font-weight:800;color:#1e1035;}
.mstat p{font-size:11px;color:#888;font-weight:600;margin-top:2px;}

.page-grid{display:grid;grid-template-columns:340px 1fr;gap:22px;}
@media(max-width:960px){.page-grid{grid-template-columns:1fr;}}

.add-card{background:linear-gradient(160deg,#1e1035,#3d1a6e);border-radius:16px;padding:24px;box-shadow:0 4px 20px rgba(30,16,53,.25);height:fit-content;}
.add-card h3{color:#ffcc00;font-size:15px;font-weight:800;margin-bottom:4px;display:flex;align-items:center;gap:8px;}
.add-card p{color:rgba(255,255,255,.4);font-size:12px;margin-bottom:20px;}
.dfg{display:flex;flex-direction:column;gap:5px;margin-bottom:14px;}
.dfg label{font-size:11px;font-weight:800;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.6px;}
.dfg input{padding:11px 14px;border:2px solid rgba(255,255,255,.1);border-radius:10px;font-size:13px;font-weight:700;color:#fff;background:rgba(255,255,255,.08);outline:none;transition:.2s;}
.dfg input:focus{border-color:#ffcc00;}
.dfg input::placeholder{color:rgba(255,255,255,.25);}
.time-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.add-btn{width:100%;padding:12px;background:linear-gradient(135deg,#ffcc00,#ff9800);color:#1e1035;border:none;border-radius:12px;font-size:13px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s;margin-top:4px;}
.add-btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(255,204,0,.3);}

.card{background:#fff;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,.06);}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.card-header h3{font-size:15px;font-weight:800;color:#1e1035;display:flex;align-items:center;gap:8px;}

table{width:100%;border-collapse:collapse;}
th{background:#f8f5ff;color:#6c2d7e;font-size:11px;font-weight:800;padding:11px 13px;text-align:left;text-transform:uppercase;letter-spacing:.5px;}
td{padding:12px 13px;font-size:13px;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fdfbff;}

.badge{padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700;display:inline-block;}
.badge-active{background:#d4f5e9;color:#00875a;}
.badge-inactive{background:#f5f5f5;color:#aaa;}
.result-pill{background:#1e1035;color:#ffcc00;padding:4px 12px;border-radius:7px;font-size:12px;font-weight:800;letter-spacing:1px;font-family:monospace;}
.no-result{color:#ddd;font-size:12px;}
.time-chip{background:#f0e8ff;color:#6c2d7e;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700;}

.action-btn{padding:6px 12px;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:4px;transition:.15s;}
.action-btn:hover{opacity:.85;transform:translateY(-1px);}
.btn-edit{background:#f0e8ff;color:#6c2d7e;}
.btn-activate{background:#d4f5e9;color:#00875a;}
.btn-deactivate{background:#fff3cd;color:#856404;}
.btn-del{background:#fde8ef;color:#c0003c;}

.alert-toast{display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:13px;font-weight:600;background:#e8faf4;color:#00875a;border:1.5px solid #b2dfdb;}

/* EDIT MODAL */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(10,5,30,.6);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(3px);}
.modal-overlay.show{display:flex;}
.modal-box{background:#fff;border-radius:20px;width:400px;max-width:95%;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.2);}
.modal-head{background:linear-gradient(135deg,#1e1035,#3d1a6e);padding:20px 24px;display:flex;align-items:center;gap:12px;}
.modal-head-icon{width:40px;height:40px;background:rgba(255,204,0,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.modal-head h3{color:#fff;font-size:15px;font-weight:800;}
.modal-body{padding:22px;}
.mfg{display:flex;flex-direction:column;gap:5px;margin-bottom:14px;}
.mfg label{font-size:11px;font-weight:800;color:#888;text-transform:uppercase;letter-spacing:.5px;}
.mfg input{padding:10px 13px;border:2px solid #ece8f5;border-radius:10px;font-size:13px;font-weight:700;color:#1e1035;outline:none;transition:.2s;}
.mfg input:focus{border-color:#6c2d7e;}
.modal-footer{display:flex;gap:10px;padding:0 22px 22px;}
.mfooter-btn{flex:1;padding:12px;border-radius:12px;font-size:13px;font-weight:800;border:none;cursor:pointer;transition:.2s;}
.mbtn-save{background:linear-gradient(135deg,#6c2d7e,#9b4db5);color:#fff;}
.mbtn-cancel{background:#f5f5f5;color:#888;}
.mfooter-btn:hover{opacity:.9;}
</style>

<?php if($msg): ?>
<div class="alert-toast"><i class="fas fa-check-circle"></i><span><?= $msg ?></span></div>
<?php endif; ?>

<!-- STATS -->
<div class="mkt-stats">
  <div class="mstat" style="--mc:#6c2d7e;--mc-rgb:108,45,126;">
    <div class="mstat-icon"><i class="fas fa-store"></i></div>
    <div><h3><?= $total_markets ?></h3><p>Total Markets</p></div>
  </div>
  <div class="mstat" style="--mc:#00b894;--mc-rgb:0,184,148;">
    <div class="mstat-icon"><i class="fas fa-check-circle"></i></div>
    <div><h3><?= $total_active ?></h3><p>Active Markets</p></div>
  </div>
  <div class="mstat" style="--mc:#aaa;--mc-rgb:170,170,170;">
    <div class="mstat-icon"><i class="fas fa-pause-circle"></i></div>
    <div><h3><?= $total_inactive ?></h3><p>Inactive Markets</p></div>
  </div>
</div>

<div class="page-grid">

  <!-- ADD FORM -->
  <div class="add-card">
    <h3><i class="fas fa-plus-circle"></i> Naya Market Add</h3>
    <p>Market ka naam aur timing set karein</p>
    <form method="POST">
      <div class="dfg">
        <label>Market Name</label>
        <input type="text" name="name" required placeholder="e.g. KALYAN NIGHT" style="text-transform:uppercase;">
      </div>
      <div class="time-row">
        <div class="dfg">
          <label>Open Time</label>
          <input type="text" name="open_time" placeholder="e.g. 09:30 PM">
        </div>
        <div class="dfg">
          <label>Close Time</label>
          <input type="text" name="close_time" placeholder="e.g. 11:30 PM">
        </div>
      </div>
      <button type="submit" name="add" class="add-btn"><i class="fas fa-plus"></i> Market Add Karein</button>
    </form>
  </div>

  <!-- MARKETS TABLE -->
  <div class="card">
    <div class="card-header">
      <h3><i class="fas fa-store" style="color:#6c2d7e;"></i> All Markets</h3>
      <span style="font-size:12px;color:#aaa;"><?= $total_markets ?> markets</span>
    </div>
    <div style="overflow-x:auto;">
    <table>
      <thead>
        <tr><th>#</th><th>Market Name</th><th>Open</th><th>Close</th><th>Today's Result</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php while($m = $markets->fetch_assoc()): ?>
      <tr>
        <td style="color:#aaa;font-size:12px;font-weight:700;"><?= $m['id'] ?></td>
        <td>
          <div style="font-weight:800;font-size:13px;"><?= htmlspecialchars($m['name']) ?></div>
          <div style="font-size:10px;color:#bbb;margin-top:2px;"><?= $m['slug'] ?></div>
        </td>
        <td><span class="time-chip"><i class="fas fa-clock" style="font-size:9px;"></i> <?= $m['open_time'] ?: '—' ?></span></td>
        <td><span class="time-chip" style="background:#fde8ef;color:#c0003c;"><i class="fas fa-clock" style="font-size:9px;"></i> <?= $m['close_time'] ?: '—' ?></span></td>
        <td>
          <?php if($m['result']): ?>
            <span class="result-pill"><?= $m['result'] ?></span>
          <?php else: ?>
            <span class="no-result">⏳ Pending</span>
          <?php endif; ?>
        </td>
        <td><span class="badge badge-<?= $m['status'] ?>"><?= $m['status']==='active' ? '✓ Active' : '✕ Inactive' ?></span></td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <button class="action-btn btn-edit" onclick="openEdit(<?= $m['id'] ?>, '<?= htmlspecialchars($m['name'],ENT_QUOTES) ?>', '<?= $m['open_time'] ?>', '<?= $m['close_time'] ?>')">
              <i class="fas fa-edit"></i> Edit
            </button>
            <a href="?toggle=<?= $m['id'] ?>" class="action-btn <?= $m['status']==='active' ? 'btn-deactivate' : 'btn-activate' ?>">
              <i class="fas fa-<?= $m['status']==='active' ? 'pause' : 'play' ?>"></i>
              <?= $m['status']==='active' ? 'Deactivate' : 'Activate' ?>
            </a>
            <a href="?delete=<?= $m['id'] ?>" class="action-btn btn-del" onclick="return confirm('\'<?= htmlspecialchars($m['name'],ENT_QUOTES) ?>\' market delete karna chahte ho?')">
              <i class="fas fa-trash"></i>
            </a>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
  </div>

</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-head-icon">✏️</div>
      <h3>Market Edit Karein</h3>
    </div>
    <form method="POST">
      <input type="hidden" name="edit_id" id="edit_id">
      <div class="modal-body">
        <div class="mfg">
          <label>Market Name</label>
          <input type="text" name="name" id="edit_name" required style="text-transform:uppercase;">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="mfg">
            <label>Open Time</label>
            <input type="text" name="open_time" id="edit_open" placeholder="e.g. 09:30 PM">
          </div>
          <div class="mfg">
            <label>Close Time</label>
            <input type="text" name="close_time" id="edit_close" placeholder="e.g. 11:30 PM">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="mfooter-btn mbtn-cancel" onclick="closeModal()">Cancel</button>
        <button type="submit" name="edit" class="mfooter-btn mbtn-save"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(id, name, open, close) {
  document.getElementById('edit_id').value   = id;
  document.getElementById('edit_name').value = name;
  document.getElementById('edit_open').value = open;
  document.getElementById('edit_close').value= close;
  document.getElementById('editModal').classList.add('show');
}
function closeModal() {
  document.getElementById('editModal').classList.remove('show');
}
document.getElementById('editModal').addEventListener('click', function(e){
  if(e.target === this) closeModal();
});
</script>

<?php include 'footer.php'; ?>
