<?php
require_once 'auth.php';
$pageTitle = 'Win Ratios';
$msg = '';

if (isset($_POST['save'])) {
    foreach ($_POST['ratio'] as $type => $ratio) {
        $type = sanitize($conn, $type);
        $ratio = (int)$ratio;
        $conn->query("UPDATE win_ratios SET ratio=$ratio WHERE bet_type='$type'");
    }
    $msg = '<div class="alert alert-success">Win ratios updated!</div>';
}

$ratios = $conn->query("SELECT * FROM win_ratios");
include 'header.php';
?>
<?= $msg ?>
<div class="card">
  <h3>Win Ratios (1 rupee bet pays X rupees on win)</h3>
  <form method="POST">
    <table>
      <thead><tr><th>Bet Type</th><th>Ratio (1:X)</th><th>Description</th></tr></thead>
      <tbody>
      <?php 
      $type_labels = [
        'single' => 'Single Ank (1 digit)',
        'jodi' => 'Jodi (2 digit)',
        'sp' => 'Single Panna SP (teeno alag)',
        'dp' => 'Double Panna DP (2 same)',
        'tp' => 'Triple Panna TP (teeno same)',
        'half_sangam' => 'Half Sangam',
        'full_sangam' => 'Full Sangam',
      ];
      while($r = $ratios->fetch_assoc()): ?>
      <tr>
        <td><?= $type_labels[$r['bet_type']] ?? strtoupper(str_replace('_',' ',$r['bet_type'])) ?></td>
        <td><input type="number" name="ratio[<?= $r['bet_type'] ?>]" value="<?= $r['ratio'] ?>" min="1" style="width:100px;padding:6px;border:2px solid #ddd;border-radius:6px;"></td>
        <td style="font-size:12px;color:#666;">₹10 bet → ₹<?= 10 * $r['ratio'] ?> win</td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    <br>
    <button type="submit" name="save" class="btn btn-primary">Save Ratios</button>
  </form>
</div>
<?php include 'footer.php'; ?>
