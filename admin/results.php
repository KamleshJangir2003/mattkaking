<?php
require_once 'auth.php';
$pageTitle = 'Results';
$msg = '';

// Declare result and settle bets
if (isset($_POST['declare'])) {
    $market_id = (int)$_POST['market_id'];
    $result = sanitize($conn, $_POST['result']);
    // Format: e.g. 559-96-169  open panna - jodi - close panna
    $conn->query("UPDATE markets SET result='$result' WHERE id=$market_id");

    // Parse result: open_panna - jodi - close_panna
    $parts = explode('-', str_replace(' ', '', $result));
    $open_panna = $parts[0] ?? '';
    $jodi = $parts[1] ?? '';
    $close_panna = $parts[2] ?? '';
    $open_ank = strlen($jodi) >= 1 ? $jodi[0] : '';
    $close_ank = strlen($jodi) >= 2 ? $jodi[1] : '';

    // Get all pending bets for this market today
    $bets = $conn->query("SELECT * FROM bets WHERE market_id=$market_id AND status='pending' AND bet_date=CURDATE()");
    while ($bet = $bets->fetch_assoc()) {
        $won = false;
        $ratio = getWinRatio($conn, $bet['bet_type']);
        switch ($bet['bet_type']) {
            case 'single':
                $won = ($bet['session']=='open' && $bet['number']==$open_ank) || ($bet['session']=='close' && $bet['number']==$close_ank);
                break;
            case 'jodi':
                $won = ($bet['number'] == $jodi);
                break;
            case 'sp':
            case 'dp':
            case 'tp':
                // Panna match - open ya close session ke hisaab se
                $won = ($bet['session']=='open' && $bet['number']==$open_panna) || ($bet['session']=='close' && $bet['number']==$close_panna);
                break;
            case 'half_sangam':
                // open panna + close ank ya open ank + close panna
                $won = ($open_panna.'-'.$close_ank == $bet['number']) || ($open_ank.'-'.$close_panna == $bet['number']);
                break;
            case 'full_sangam':
                $won = ($open_panna.'-'.$jodi.'-'.$close_panna == $bet['number']);
                break;
        }
        $bid = $bet['id'];
        $uid = $bet['user_id'];
        if ($won) {
            $win_amt = $bet['amount'] * $ratio;
            $conn->query("UPDATE bets SET status='won',win_amount=$win_amt WHERE id=$bid");
            updateBalance($conn, $uid, $win_amt, 'add');
            addTransaction($conn, $uid, 'win', $win_amt, "Won bet #$bid");
        } else {
            $conn->query("UPDATE bets SET status='lost' WHERE id=$bid");
        }
    }
    $msg = '<div class="alert alert-success">Result declared and bets settled!</div>';
}

$markets = $conn->query("SELECT * FROM markets WHERE status='active' ORDER BY id");
$recent_results = $conn->query("SELECT m.name,m.result,b.count,b.total FROM markets m LEFT JOIN (SELECT market_id,COUNT(*) as count,SUM(amount) as total FROM bets WHERE bet_date=CURDATE() GROUP BY market_id) b ON m.id=b.market_id ORDER BY m.id");

include 'header.php';
?>

<?= $msg ?>

<div class="card">
  <h3>Declare Result</h3>
  <form method="POST">
    <div class="form-row">
      <div class="form-group">
        <label>Select Market</label>
        <select name="market_id" required>
          <option value="">-- Select Market --</option>
          <?php $markets->data_seek(0); while($m = $markets->fetch_assoc()): ?>
          <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?> <?= $m['result'] ? '('.$m['result'].')' : '' ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Result (Format: 559-96-169)</label>
        <input type="text" name="result" required placeholder="e.g. 559-96-169">
        <small style="color:#666;font-size:11px;">Open Panna - Jodi - Close Panna</small>
      </div>
    </div>
    <button type="submit" name="declare" class="btn btn-success">Declare Result & Settle Bets</button>
  </form>
</div>

<div class="card">
  <h3>Today's Market Results</h3>
  <table>
    <thead><tr><th>Market</th><th>Result</th><th>Total Bets</th><th>Total Amount</th></tr></thead>
    <tbody>
    <?php while($r = $recent_results->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($r['name']) ?></td>
      <td><strong style="color:<?= $r['result'] ? '#28a745' : '#999' ?>"><?= $r['result'] ?? 'Pending' ?></strong></td>
      <td><?= $r['count'] ?? 0 ?></td>
      <td>₹<?= number_format($r['total'] ?? 0, 2) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include 'footer.php'; ?>
