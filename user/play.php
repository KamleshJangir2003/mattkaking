<?php
require_once 'auth.php';
$pageTitle = 'Play / Bet';
$uid = $_SESSION['user_id'];
$selected_market = isset($_GET['market']) ? (int)$_GET['market'] : 0;
$popup = null; // ['type','title','body']

function getPannaType($num) {
    $d = str_split($num);
    if (count(array_unique($d)) == 3) return 'sp';
    if (count(array_unique($d)) == 2) return 'dp';
    if (count(array_unique($d)) == 1) return 'tp';
    return false;
}

if (isset($_POST['place_bet'])) {
    $market_id  = (int)$_POST['market_id'];
    $bet_type   = sanitize($conn, $_POST['bet_type']);
    $number     = sanitize($conn, trim($_POST['number']));
    $amount     = (float)$_POST['amount'];
    $session    = sanitize($conn, $_POST['session']);
    $balance    = getUserBalance($conn, $uid);
    $valid_types = ['single','jodi','sp','dp','tp','half_sangam','full_sangam'];
    $err = '';

    if (!in_array($bet_type, $valid_types)) {
        $err = 'Invalid bet type selected!';
    } elseif (!in_array($session, ['open','close'])) {
        $err = 'Invalid session selected!';
    } elseif ($amount < 10) {
        $err = 'Minimum bet amount ₹10 hai!';
    } elseif ($balance < $amount) {
        $err = "Insufficient balance! Aapke paas sirf ₹".number_format($balance,2)." hai.";
    } elseif ($bet_type == 'single' && (!ctype_digit($number) || strlen($number) != 1)) {
        $err = 'Single Ank mein sirf 1 digit (0-9) dalo!';
    } elseif ($bet_type == 'jodi' && (!ctype_digit($number) || strlen($number) != 2)) {
        $err = 'Jodi mein exactly 2 digit dalo! (00 se 99)';
    } elseif (in_array($bet_type,['sp','dp','tp']) && (!ctype_digit($number) || strlen($number) != 3)) {
        $err = 'Panna mein exactly 3 digit dalo!';
    } elseif ($bet_type == 'sp' && getPannaType($number) !== 'sp') {
        $err = 'Single Panna mein teeno digits ALAG hone chahiye! (e.g. 123, 456)';
    } elseif ($bet_type == 'dp' && getPannaType($number) !== 'dp') {
        $err = 'Double Panna mein 2 digits SAME hone chahiye! (e.g. 112, 344)';
    } elseif ($bet_type == 'tp' && getPannaType($number) !== 'tp') {
        $err = 'Triple Panna mein TEENO digits same hone chahiye! (e.g. 111, 555)';
    }

    if ($err) {
        $popup = ['type'=>'error','title'=>'Bet Error!','body'=>$err];
        $selected_market = $market_id;
    } else {
        $date = date('Y-m-d');
        $conn->query("INSERT INTO bets (user_id,market_id,bet_type,number,amount,session,bet_date) VALUES ($uid,$market_id,'$bet_type','$number',$amount,'$session','$date')");
        $bid = $conn->insert_id;
        updateBalance($conn, $uid, $amount, 'subtract');
        addTransaction($conn, $uid, 'bet', $amount, "Bet #$bid");
        $new_bal = getUserBalance($conn, $uid);
        $ratio = getWinRatio($conn, $bet_type);
        $win_possible = $amount * $ratio;
        $popup = ['type'=>'success','title'=>'Bet Placed! 🎉','body'=>"
            <div class='detail-row'><span class='key'>Bet #</span><span class='val'>#$bid</span></div>
            <div class='detail-row'><span class='key'>Number</span><span class='val' style='font-size:20px;color:#7c4066;'>$number</span></div>
            <div class='detail-row'><span class='key'>Amount</span><span class='val'>₹$amount</span></div>
            <div class='detail-row'><span class='key'>Session</span><span class='val'>".strtoupper($session)."</span></div>
            <div class='detail-row'><span class='key'>Win Possible</span><span class='val' style='color:#28a745;font-size:16px;'>₹$win_possible</span></div>
            <div class='detail-row'><span class='key'>New Balance</span><span class='val'>₹".number_format($new_bal,2)."</span></div>
        "];
        $selected_market = $market_id;
    }
}

$markets = $conn->query("SELECT * FROM markets WHERE status='active' ORDER BY id");
$ratios_q = $conn->query("SELECT * FROM win_ratios");
$ratio_map = [];
while($r = $ratios_q->fetch_assoc()) $ratio_map[$r['bet_type']] = $r['ratio'];

// Today's bets for this market
$today_bets_list = [];
if ($selected_market) {
    $tb = $conn->query("SELECT b.*,m.name as mname FROM bets b JOIN markets m ON b.market_id=m.id WHERE b.user_id=$uid AND b.market_id=$selected_market AND b.bet_date=CURDATE() ORDER BY b.created_at DESC");
    while($row = $tb->fetch_assoc()) $today_bets_list[] = $row;
}

include 'header.php';

// Show popup via JS if needed
if ($popup):
?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  showPopup('<?= $popup['type'] ?>', '<?= addslashes($popup['title']) ?>', '<?= addslashes($popup['body']) ?>');
});
</script>
<?php endif; ?>

<!-- MARKET SELECT -->
<div class="card">
  <div class="card-title">🎯 Market Chuniye</div>
  <form method="GET" id="mktForm">
    <div class="form-group" style="margin:0;">
      <select name="market" onchange="document.getElementById('mktForm').submit()" style="width:100%;padding:12px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;font-family:inherit;font-style:normal;background:#fafafa;">
        <option value="">-- Market Select Karein --</option>
        <?php $markets->data_seek(0); while($m = $markets->fetch_assoc()): ?>
        <option value="<?= $m['id'] ?>" <?= $selected_market==$m['id']?'selected':'' ?>>
          <?= htmlspecialchars($m['name']) ?> (<?= $m['open_time'] ?> - <?= $m['close_time'] ?>)
        </option>
        <?php endwhile; ?>
      </select>
    </div>
  </form>
</div>

<?php if ($selected_market):
  $mkt = $conn->query("SELECT * FROM markets WHERE id=$selected_market")->fetch_assoc();
  if ($mkt):
?>

<!-- MARKET INFO -->
<div style="background:linear-gradient(135deg,#1a0030,#3a0060);border-radius:12px;padding:14px 16px;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
  <div>
    <div style="color:#ffcc00;font-size:15px;"><?= htmlspecialchars($mkt['name']) ?></div>
    <div style="color:#ccc;font-size:11px;font-style:normal;margin-top:3px;">⏰ <?= $mkt['open_time'] ?> → <?= $mkt['close_time'] ?></div>
  </div>
  <div style="text-align:right;">
    <div style="color:#aaa;font-size:10px;font-style:normal;">Today's Result</div>
    <div style="color:#fff;font-size:18px;font-weight:900;"><?= $mkt['result'] ?? '⏳ Awaited' ?></div>
  </div>
</div>

<!-- BET FORM -->
<div class="card">
  <div class="card-title">🎰 Bet Lagaiye</div>
  <form method="POST" id="betForm" onsubmit="return confirmBet(event)">
    <input type="hidden" name="market_id" value="<?= $selected_market ?>">
    <input type="hidden" name="place_bet" value="1">

    <!-- STEP 1: BET TYPE -->
    <div style="font-size:11px;color:#888;font-style:normal;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Step 1 — Bet Type Chuniye</div>
    <div class="bet-type-grid">
      <?php
      $bet_types = [
        'single'      => ['name'=>'Single Ank','icon'=>'1️⃣','example'=>'0-9','desc'=>'1 digit'],
        'jodi'        => ['name'=>'Jodi','icon'=>'🎯','example'=>'00-99','desc'=>'2 digit'],
        'sp'          => ['name'=>'Single Panna','icon'=>'🅢','example'=>'123','desc'=>'Teeno alag'],
        'dp'          => ['name'=>'Double Panna','icon'=>'🅓','example'=>'112','desc'=>'2 same'],
        'tp'          => ['name'=>'Triple Panna','icon'=>'🅣','example'=>'111','desc'=>'Teeno same'],
        'half_sangam' => ['name'=>'Half Sangam','icon'=>'½','example'=>'559-6','desc'=>'Panna+Ank'],
        'full_sangam' => ['name'=>'Full Sangam','icon'=>'💯','example'=>'559-96-169','desc'=>'Full match'],
      ];
      foreach($bet_types as $key => $bt):
        $ratio = $ratio_map[$key] ?? '-';
      ?>
      <div class="bet-type-card" id="btcard_<?= $key ?>" onclick="selectBetType('<?= $key ?>')">
        <div style="font-size:20px;"><?= $bt['icon'] ?></div>
        <div class="bt-name"><?= $bt['name'] ?></div>
        <div class="bt-ratio">1:<?= $ratio ?></div>
        <div class="bt-example"><?= $bt['example'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <input type="hidden" name="bet_type" id="bet_type_input" required>

    <!-- HINT BOX -->
    <div class="hint-box" id="hintBox">
      <p id="hintText"></p>
      <div class="hint-examples" id="hintExamples"></div>
    </div>

    <!-- STEP 2: SESSION -->
    <div style="font-size:11px;color:#888;font-style:normal;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Step 2 — Session Chuniye</div>
    <div style="display:flex;gap:10px;margin-bottom:14px;">
      <label style="flex:1;cursor:pointer;">
        <input type="radio" name="session" value="open" checked style="display:none;" id="sess_open">
        <div class="session-card" id="scard_open" style="border:2px solid #7c4066;border-radius:10px;padding:12px;text-align:center;background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;font-style:normal;">
          <div style="font-size:18px;">🌅</div>
          <div style="font-size:13px;font-weight:700;">OPEN</div>
          <div style="font-size:10px;opacity:.8;"><?= $mkt['open_time'] ?></div>
        </div>
      </label>
      <label style="flex:1;cursor:pointer;">
        <input type="radio" name="session" value="close" style="display:none;" id="sess_close">
        <div class="session-card" id="scard_close" style="border:2px solid #ddd;border-radius:10px;padding:12px;text-align:center;background:#f5f5f5;color:#555;font-style:normal;">
          <div style="font-size:18px;">🌙</div>
          <div style="font-size:13px;font-weight:700;">CLOSE</div>
          <div style="font-size:10px;opacity:.7;"><?= $mkt['close_time'] ?></div>
        </div>
      </label>
    </div>

    <!-- STEP 3: NUMBER -->
    <div style="font-size:11px;color:#888;font-style:normal;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Step 3 — Number Daaliye</div>
    <div class="form-group">
      <div class="number-input-wrap">
        <input type="text" name="number" id="number_inp" placeholder="Number daaliye..." maxlength="12" autocomplete="off" oninput="validateNumber(this)">
        <button type="button" class="num-clear" onclick="document.getElementById('number_inp').value='';validateNumber({value:''})">✕</button>
      </div>
      <div id="num_feedback" style="font-size:11px;margin-top:4px;font-style:normal;display:none;"></div>
    </div>

    <!-- STEP 4: AMOUNT -->
    <div style="font-size:11px;color:#888;font-style:normal;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Step 4 — Amount Daaliye</div>
    <div class="form-group">
      <input type="number" name="amount" id="amount_inp" placeholder="₹ Amount (Min ₹10)" min="10" oninput="calcWin()">
    </div>

    <!-- QUICK AMOUNT BUTTONS -->
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;">
      <?php foreach([10,20,50,100,200,500] as $qa): ?>
      <button type="button" onclick="setAmount(<?= $qa ?>)" style="background:#f0e8f4;color:#7c4066;border:1px solid #d0b8cc;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;">₹<?= $qa ?></button>
      <?php endforeach; ?>
    </div>

    <!-- WIN PREVIEW -->
    <div class="win-preview" id="winPreview">
      <div class="wp-label">🏆 Jeetne par milega</div>
      <div class="wp-amount" id="winAmount">₹0</div>
      <div style="font-size:11px;color:#155724;margin-top:2px;" id="winFormula"></div>
    </div>

    <!-- SUBMIT -->
    <button type="submit" class="btn btn-primary" style="font-size:15px;padding:14px;">
      🎰 BET LAGAIYE
    </button>

    <div style="text-align:center;font-size:11px;color:#999;margin-top:8px;font-style:normal;">Bet lagane par balance se amount kat jayega</div>
  </form>
</div>

<!-- TODAY'S BETS FOR THIS MARKET -->
<?php if (!empty($today_bets_list)): ?>
<div class="card">
  <div class="card-title">📝 Aaj Ki Bets - <?= htmlspecialchars($mkt['name']) ?></div>
  <table>
    <thead>
      <tr><th>Type</th><th>Number</th><th>Amt</th><th>Session</th><th>Status</th><th>Win</th></tr>
    </thead>
    <tbody>
    <?php foreach($today_bets_list as $b): ?>
    <tr>
      <td style="font-size:11px;"><?= strtoupper(str_replace('_',' ',$b['bet_type'])) ?></td>
      <td><strong style="font-size:15px;color:#7c4066;"><?= $b['number'] ?></strong></td>
      <td>₹<?= $b['amount'] ?></td>
      <td style="font-size:10px;"><?= strtoupper($b['session']) ?></td>
      <td><span class="badge badge-<?= $b['status'] ?>"><?= strtoupper($b['status']) ?></span></td>
      <td><?= $b['win_amount']>0 ? '<span style="color:#28a745;">₹'.$b['win_amount'].'</span>' : '-' ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php endif; endif; ?>

<!-- WIN RATIOS TABLE -->
<div class="card">
  <div class="card-title">📊 Win Ratios</div>
  <table>
    <thead><tr><th>Bet Type</th><th>Number</th><th>Example</th><th>Ratio</th></tr></thead>
    <tbody>
      <?php
      $info = [
        'single'      => ['Single Ank','1 digit (0-9)','5'],
        'jodi'        => ['Jodi','2 digit (00-99)','56'],
        'sp'          => ['Single Panna','3 digit, teeno alag','123'],
        'dp'          => ['Double Panna','3 digit, 2 same','112'],
        'tp'          => ['Triple Panna','3 digit, teeno same','111'],
        'half_sangam' => ['Half Sangam','Panna+Ank','559-6'],
        'full_sangam' => ['Full Sangam','Full result','559-96-169'],
      ];
      foreach($info as $key => $v):
        $r = $ratio_map[$key] ?? '-';
      ?>
      <tr>
        <td style="font-weight:700;color:#7c4066;"><?= $v[0] ?></td>
        <td style="font-size:11px;"><?= $v[1] ?></td>
        <td><code style="background:#f0e8f4;padding:2px 6px;border-radius:4px;font-size:12px;"><?= $v[2] ?></code></td>
        <td><strong>1:<?= $r ?></strong></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
var ratios = <?= json_encode($ratio_map) ?>;
var selectedBetType = '';
var hints = {
  single:      {text:'Koi ek digit (0 se 9 mein se) chuniye.',      examples:['0','3','7','9']},
  jodi:        {text:'2 digit ki jodi likho (00 se 99).',            examples:['05','23','56','99']},
  sp:          {text:'Single Panna: 3 digit, TEENO alag hone chahiye.', examples:['123','456','178','349']},
  dp:          {text:'Double Panna: 3 digit, 2 digits same hone chahiye.', examples:['112','233','455','677']},
  tp:          {text:'Triple Panna: TEENO digits same hone chahiye.', examples:['000','111','555','999']},
  half_sangam: {text:'Open Panna + "-" + Close Ank  YA  Open Ank + "-" + Close Panna likho.', examples:['559-6','9-169','123-5']},
  full_sangam: {text:'Pura result likho: Open Panna + "-" + Jodi + "-" + Close Panna.', examples:['559-96-169','123-45-678']},
};

function selectBetType(type) {
  // Remove all selected
  document.querySelectorAll('.bet-type-card').forEach(function(c){ c.classList.remove('selected'); });
  var card = document.getElementById('btcard_' + type);
  if (card) card.classList.add('selected');
  selectedBetType = type;
  document.getElementById('bet_type_input').value = type;

  // Show hint
  var hintBox = document.getElementById('hintBox');
  var h = hints[type];
  if (h) {
    document.getElementById('hintText').textContent = h.text;
    var exDiv = document.getElementById('hintExamples');
    exDiv.innerHTML = 'Examples: ';
    h.examples.forEach(function(ex){ exDiv.innerHTML += '<span>' + ex + '</span> '; });
    hintBox.style.display = 'block';
  }
  // Clear number field
  document.getElementById('number_inp').value = '';
  document.getElementById('num_feedback').style.display = 'none';
  document.getElementById('number_inp').classList.remove('error-field');
  calcWin();
}

function validateNumber(inp) {
  var val = typeof inp === 'string' ? inp : inp.value;
  val = val.trim();
  var fb = document.getElementById('num_feedback');
  if (!selectedBetType || !val) { fb.style.display='none'; return; }

  var ok = false, msg = '';
  if (selectedBetType === 'single') {
    ok = /^[0-9]$/.test(val);
    msg = ok ? '✅ Valid Single Ank' : '❌ Sirf 1 digit (0-9) chahiye';
  } else if (selectedBetType === 'jodi') {
    ok = /^[0-9]{2}$/.test(val);
    msg = ok ? '✅ Valid Jodi' : '❌ Exactly 2 digit chahiye (00-99)';
  } else if (['sp','dp','tp'].includes(selectedBetType)) {
    if (!/^[0-9]{3}$/.test(val)) {
      ok = false; msg = '❌ Exactly 3 digit chahiye';
    } else {
      var d = val.split('');
      var uniq = [...new Set(d)].length;
      if (selectedBetType==='sp') { ok = uniq===3; msg = ok?'✅ Valid Single Panna':'❌ Teeno digits ALAG hone chahiye (e.g. 123)'; }
      if (selectedBetType==='dp') { ok = uniq===2; msg = ok?'✅ Valid Double Panna':'❌ 2 digits SAME hone chahiye (e.g. 112)'; }
      if (selectedBetType==='tp') { ok = uniq===1; msg = ok?'✅ Valid Triple Panna':'❌ TEENO digits same hone chahiye (e.g. 111)'; }
    }
  } else if (selectedBetType==='half_sangam') {
    ok = /^[0-9]{1,3}-[0-9]{1,3}$/.test(val);
    msg = ok ? '✅ Valid Half Sangam' : '❌ Format: Panna-Ank ya Ank-Panna (e.g. 559-6)';
  } else if (selectedBetType==='full_sangam') {
    ok = /^[0-9]{3}-[0-9]{2}-[0-9]{3}$/.test(val);
    msg = ok ? '✅ Valid Full Sangam' : '❌ Format: OpenPanna-Jodi-ClosePanna (e.g. 559-96-169)';
  }
  fb.style.display = 'block';
  fb.style.color = ok ? '#28a745' : '#ff0016';
  fb.textContent = msg;
  if (typeof inp !== 'string') {
    inp.classList.toggle('error-field', !ok);
  }
  calcWin();
}

function setAmount(amt) {
  document.getElementById('amount_inp').value = amt;
  calcWin();
}

function calcWin() {
  var type = selectedBetType;
  var amt = parseFloat(document.getElementById('amount_inp').value) || 0;
  var prev = document.getElementById('winPreview');
  if (type && amt >= 10 && ratios[type]) {
    var win = amt * ratios[type];
    document.getElementById('winAmount').textContent = '₹' + win.toFixed(0);
    document.getElementById('winFormula').textContent = '₹' + amt + ' × ' + ratios[type] + ' = ₹' + win.toFixed(0);
    prev.style.display = 'block';
  } else {
    prev.style.display = 'none';
  }
}

// Session toggle UI
document.querySelectorAll('input[name="session"]').forEach(function(radio) {
  radio.addEventListener('change', function() {
    var openCard = document.getElementById('scard_open');
    var closeCard = document.getElementById('scard_close');
    if (this.value === 'open') {
      openCard.style.cssText = 'border:2px solid #7c4066;border-radius:10px;padding:12px;text-align:center;background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;font-style:normal;';
      closeCard.style.cssText = 'border:2px solid #ddd;border-radius:10px;padding:12px;text-align:center;background:#f5f5f5;color:#555;font-style:normal;';
    } else {
      closeCard.style.cssText = 'border:2px solid #7c4066;border-radius:10px;padding:12px;text-align:center;background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;font-style:normal;';
      openCard.style.cssText = 'border:2px solid #ddd;border-radius:10px;padding:12px;text-align:center;background:#f5f5f5;color:#555;font-style:normal;';
    }
  });
});

// Confirm before submit
function confirmBet(e) {
  e.preventDefault();
  var type = document.getElementById('bet_type_input').value;
  var number = document.getElementById('number_inp').value.trim();
  var amount = parseFloat(document.getElementById('amount_inp').value) || 0;
  var session = document.querySelector('input[name="session"]:checked').value;

  if (!type) { showPopup('error','Bet Type Chuniye!','Pehle koi bet type select karein.'); return false; }
  if (!number) { showPopup('error','Number Daaliye!','Apna lucky number daaliye.'); return false; }
  if (amount < 10) { showPopup('error','Amount Kam Hai!','Minimum bet amount ₹10 hai.'); return false; }

  var typeNames = {single:'Single Ank',jodi:'Jodi',sp:'Single Panna',dp:'Double Panna',tp:'Triple Panna',half_sangam:'Half Sangam',full_sangam:'Full Sangam'};
  var ratio = ratios[type] || 1;
  var winAmt = amount * ratio;

  showConfirmPopup('Bet Confirm Karein', `
    <div class='detail-row'><span class='key'>Bet Type</span><span class='val'>${typeNames[type]}</span></div>
    <div class='detail-row'><span class='key'>Number</span><span class='val' style='font-size:20px;color:#7c4066;font-weight:900;'>${number}</span></div>
    <div class='detail-row'><span class='key'>Amount</span><span class='val'>₹${amount}</span></div>
    <div class='detail-row'><span class='key'>Session</span><span class='val'>${session.toUpperCase()}</span></div>
    <div class='detail-row'><span class='key'>Possible Win</span><span class='val' style='color:#28a745;font-size:16px;'>₹${winAmt}</span></div>
  `, function() {
    document.getElementById('betForm').submit();
  });
  return false;
}
</script>

<?php include 'footer.php'; ?>
