<?php
require_once 'auth.php';
$pageTitle = 'Users';
$msg = '';

// Add balance / deduct
if (isset($_POST['update_balance'])) {
    $uid = (int)$_POST['user_id'];
    $amount = (float)$_POST['amount'];
    $type = sanitize($conn, $_POST['type']);
    if ($type === 'add') {
        updateBalance($conn, $uid, $amount, 'add');
        addTransaction($conn, $uid, 'deposit', $amount, 'Admin credit');
        $msg = '<div class="alert alert-success">Balance added!</div>';
    } else {
        $bal = getUserBalance($conn, $uid);
        if ($bal >= $amount) {
            updateBalance($conn, $uid, $amount, 'subtract');
            addTransaction($conn, $uid, 'withdraw', $amount, 'Admin debit');
            $msg = '<div class="alert alert-success">Balance deducted!</div>';
        } else {
            $msg = '<div class="alert alert-danger">Insufficient balance!</div>';
        }
    }
}

// Block/Unblock
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE users SET status=IF(status='active','blocked','active') WHERE id=$id");
    header("Location: users.php"); exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
include 'header.php';
?>

<?= $msg ?>

<div class="card">
  <h3>All Users</h3>
  <div style="overflow-x:auto;">
  <table>
    <thead><tr><th>#</th><th>Name</th><th>Mobile</th><th>Balance</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
    <?php while($u = $users->fetch_assoc()): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= $u['mobile'] ?></td>
      <td><strong>₹<?= number_format($u['balance'], 2) ?></strong></td>
      <td><span class="badge badge-<?= $u['status'] ?>"><?= strtoupper($u['status']) ?></span></td>
      <td style="font-size:11px;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
      <td style="white-space:nowrap;">
        <button class="btn btn-info" style="font-size:11px;" onclick="openBalance(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>', <?= $u['balance'] ?>)">Balance</button>
        <a href="?toggle=<?= $u['id'] ?>" class="btn <?= $u['status']=='active'?'btn-danger':'btn-success' ?>" style="font-size:11px;"><?= $u['status']=='active'?'Block':'Unblock' ?></a>
        <a href="user_bets.php?user_id=<?= $u['id'] ?>" class="btn btn-warning" style="font-size:11px;">Bets</a>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- Balance Modal -->
<div id="balanceModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:25px;width:320px;max-width:95%;">
    <h3 style="margin-bottom:15px;color:#7c4066;">Update Balance</h3>
    <form method="POST">
      <input type="hidden" name="user_id" id="modal_uid">
      <p id="modal_name" style="margin-bottom:10px;font-weight:700;"></p>
      <p id="modal_bal" style="margin-bottom:15px;color:#28a745;"></p>
      <div class="form-group">
        <label>Action</label>
        <select name="type">
          <option value="add">Add Balance</option>
          <option value="deduct">Deduct Balance</option>
        </select>
      </div>
      <div class="form-group">
        <label>Amount (₹)</label>
        <input type="number" name="amount" min="1" required>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" name="update_balance" class="btn btn-primary">Update</button>
        <button type="button" onclick="document.getElementById('balanceModal').style.display='none'" class="btn btn-danger">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openBalance(id, name, bal) {
  document.getElementById('modal_uid').value = id;
  document.getElementById('modal_name').textContent = 'User: ' + name;
  document.getElementById('modal_bal').textContent = 'Current Balance: ₹' + parseFloat(bal).toFixed(2);
  document.getElementById('balanceModal').style.display = 'flex';
}
</script>

<?php include 'footer.php'; ?>
