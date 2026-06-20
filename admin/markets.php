<?php
require_once 'auth.php';
$pageTitle = 'Markets';
$msg = '';

// Add market
if (isset($_POST['add'])) {
    $name = sanitize($conn, $_POST['name']);
    $slug = sanitize($conn, strtolower(str_replace(' ', '-', $_POST['name'])));
    $open = sanitize($conn, $_POST['open_time']);
    $close = sanitize($conn, $_POST['close_time']);
    $conn->query("INSERT INTO markets (name,slug,open_time,close_time) VALUES ('$name','$slug','$open','$close')");
    $msg = '<div class="alert alert-success">Market added successfully!</div>';
}

// Toggle status
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE markets SET status=IF(status='active','inactive','active') WHERE id=$id");
    header("Location: markets.php"); exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM markets WHERE id=$id");
    header("Location: markets.php"); exit;
}

$markets = $conn->query("SELECT * FROM markets ORDER BY id");
include 'header.php';
?>

<?= $msg ?>

<div class="card">
  <h3>Add New Market</h3>
  <form method="POST">
    <div class="form-row">
      <div class="form-group"><label>Market Name</label><input type="text" name="name" required placeholder="e.g. KALYAN NIGHT"></div>
      <div class="form-group"><label>Open Time</label><input type="text" name="open_time" placeholder="e.g. 09:30 PM"></div>
      <div class="form-group"><label>Close Time</label><input type="text" name="close_time" placeholder="e.g. 11:30 PM"></div>
    </div>
    <button type="submit" name="add" class="btn btn-primary">Add Market</button>
  </form>
</div>

<div class="card">
  <h3>All Markets</h3>
  <div style="overflow-x:auto;">
  <table>
    <thead><tr><th>#</th><th>Name</th><th>Open</th><th>Close</th><th>Result</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php while($m = $markets->fetch_assoc()): ?>
    <tr>
      <td><?= $m['id'] ?></td>
      <td><?= htmlspecialchars($m['name']) ?></td>
      <td><?= $m['open_time'] ?></td>
      <td><?= $m['close_time'] ?></td>
      <td><strong><?= $m['result'] ?? '-' ?></strong></td>
      <td><span class="badge badge-<?= $m['status'] ?>"><?= strtoupper($m['status']) ?></span></td>
      <td>
        <a href="?toggle=<?= $m['id'] ?>" class="btn btn-warning" style="font-size:11px;">Toggle</a>
        <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger" style="font-size:11px;" onclick="return confirm('Delete this market?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  </div>
</div>

<?php include 'footer.php'; ?>
