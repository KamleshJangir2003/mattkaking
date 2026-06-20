<?php
require_once 'auth.php';
$pageTitle = 'Wallet';
$uid = $_SESSION['user_id'];
$popup = null;

if (isset($_POST['withdraw'])) {
    $amount = (float)$_POST['amount'];
    $balance = getUserBalance($conn, $uid);
    if ($amount < 100) {
        $popup = ['type'=>'error','title'=>'Invalid Amount!','body'=>'Minimum withdrawal amount ₹100 hai.'];
    } elseif ($balance < $amount) {
        $popup = ['type'=>'error','title'=>'Insufficient Balance!','body'=>"Aapke paas sirf ₹".number_format($balance,2)." hai. Itna withdraw nahi kar sakte."];
    } else {
        updateBalance($conn, $uid, $amount, 'subtract');
        addTransaction($conn, $uid, 'withdraw', $amount, 'Withdrawal request');
        $popup = ['type'=>'success','title'=>'Request Submit!','body'=>"₹".number_format($amount,2)." ka withdrawal request submit ho gaya. Admin jald process karega."];
    }
}

$balance = getUserBalance($conn, $uid);
$transactions = $conn->query("SELECT * FROM transactions WHERE user_id=$uid ORDER BY created_at DESC LIMIT 30");
$total_won = $conn->query("SELECT COALESCE(SUM(win_amount),0) as c FROM bets WHERE user_id=$uid AND status='won'")->fetch_assoc()['c'];
$total_bet = $conn->query("SELECT COALESCE(SUM(amount),0) as c FROM bets WHERE user_id=$uid")->fetch_assoc()['c'];

include 'header.php';
if ($popup):
?>
<script>
document.addEventListener('DOMContentLoaded',function(){
  showPopup('<?= $popup['type'] ?>','<?= addslashes($popup['title']) ?>','<p><?= addslashes($popup['body']) ?></p>');
});
</script>
<?php endif; ?>

<!-- BALANCE CARD -->
<div style="background:linear-gradient(135deg,#1a0030,#3a0060);border-radius:16px;padding:24px 20px;margin-bottom:12px;text-align:center;box-shadow:0 8px 25px rgba(26,0,48,.4);">
  <div style="color:#aaa;font-size:12px;font-style:normal;letter-spacing:1px;text-transform:uppercase;">Available Balance</div>
  <div style="color:#ffcc00;font-size:40px;font-weight:900;margin:8px 0;">₹<?= number_format($balance, 2) ?></div>
  <div style="color:#ccc;font-size:12px;font-style:normal;"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
</div>

<!-- STATS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
  <div style="background:#fff;border:2px solid #ff182c;border-radius:12px;padding:12px;text-align:center;">
    <div style="font-size:16px;color:#ff0016;font-weight:700;">₹<?= number_format($total_bet,2) ?></div>
    <div style="font-size:10px;color:#888;font-style:normal;">Total Bets Placed</div>
  </div>
  <div style="background:#fff;border:2px solid #ff182c;border-radius:12px;padding:12px;text-align:center;">
    <div style="font-size:16px;color:#28a745;font-weight:700;">₹<?= number_format($total_won,2) ?></div>
    <div style="font-size:10px;color:#888;font-style:normal;">Total Won</div>
  </div>
</div>

<!-- DEPOSIT INFO -->
<div class="card">
  <div class="card-title">💳 Balance Add Kaise Karein?</div>
  <div style="font-style:normal;font-size:13px;line-height:1.8;color:#555;">
    <p>Balance add karne ke liye admin se contact karein:</p>
    <div style="background:#f0e8f4;border-radius:10px;padding:12px;margin-top:10px;">
      <div>📱 <strong>WhatsApp:</strong> Admin Number par message karein</div>
      <div>💰 <strong>UPI/Bank:</strong> Admin se details lein</div>
      <div>⏰ <strong>Processing:</strong> 5-10 minutes mein balance add hoga</div>
    </div>
  </div>
</div>

<!-- WITHDRAWAL -->
<div class="card">
  <div class="card-title">💸 Withdrawal Request</div>
  <form method="POST" onsubmit="return confirmWithdraw(event)">
    <div class="form-group">
      <label>Amount (Min ₹100)</label>
      <input type="number" name="amount" min="100" max="<?= $balance ?>" placeholder="₹ Enter amount" id="wd_amount">
    </div>
    <!-- Quick amounts -->
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;">
      <?php foreach([100,200,500,1000,2000,5000] as $qa): ?>
      <button type="button" onclick="document.getElementById('wd_amount').value=<?= $qa ?>" style="background:#f0e8f4;color:#7c4066;border:1px solid #d0b8cc;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;">₹<?= $qa ?></button>
      <?php endforeach; ?>
    </div>
    <button type="submit" name="withdraw" class="btn btn-danger">💸 Withdrawal Request</button>
  </form>
</div>

<!-- TRANSACTION HISTORY -->
<div class="card">
  <div class="card-title">📜 Transaction History</div>
  <?php if ($transactions->num_rows == 0): ?>
  <div style="text-align:center;padding:25px;font-style:normal;color:#999;">
    <div style="font-size:35px;margin-bottom:8px;">📭</div>
    Koi transaction nahi mili.
  </div>
  <?php else: ?>
  <?php while($t = $transactions->fetch_assoc()):
    $is_credit = in_array($t['type'], ['deposit','win']);
    $icons = ['deposit'=>'💳','withdraw'=>'💸','bet'=>'🎰','win'=>'🏆'];
  ?>
  <div style="display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid #f5e6f0;">
    <div style="width:38px;height:38px;border-radius:50%;background:<?= $is_credit?'#d4edda':'#f8d7da' ?>;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">
      <?= $icons[$t['type']] ?? '💰' ?>
    </div>
    <div style="flex:1;min-width:0;">
      <div style="font-size:13px;color:#1a0030;"><?= strtoupper($t['type']) ?></div>
      <div style="font-size:11px;color:#999;font-style:normal;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($t['note']) ?></div>
      <div style="font-size:10px;color:#bbb;font-style:normal;"><?= date('d M Y h:i A', strtotime($t['created_at'])) ?></div>
    </div>
    <div style="font-size:15px;font-weight:900;color:<?= $is_credit?'#28a745':'#ff0016' ?>;white-space:nowrap;">
      <?= $is_credit?'+':'-' ?>₹<?= number_format($t['amount'],2) ?>
    </div>
  </div>
  <?php endwhile; ?>
  <?php endif; ?>
</div>

<script>
function confirmWithdraw(e) {
  e.preventDefault();
  var amt = parseFloat(document.getElementById('wd_amount').value) || 0;
  if (amt < 100) { showPopup('error','Invalid Amount!','<p>Minimum withdrawal ₹100 hai.</p>'); return false; }
  showConfirmPopup('Withdrawal Confirm', `
    <div class='detail-row'><span class='key'>Amount</span><span class='val' style='font-size:20px;color:#7c4066;'>₹${amt.toFixed(2)}</span></div>
    <div class='detail-row'><span class='key'>Processing Time</span><span class='val'>5-30 minutes</span></div>
    <p style='margin-top:10px;font-size:12px;color:#666;'>Admin aapka request process karega.</p>
  `, function(){ document.querySelector('form').submit(); });
  return false;
}
</script>

<?php include 'footer.php'; ?>
