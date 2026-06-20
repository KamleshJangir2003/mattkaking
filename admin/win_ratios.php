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
    $msg = 'success';
}

$ratios = $conn->query("SELECT * FROM win_ratios ORDER BY FIELD(bet_type,'single','jodi','sp','dp','tp','half_sangam','full_sangam')");
include 'header.php';
?>

<style>
.wr-wrap{max-width:900px;}
.wr-header{background:linear-gradient(135deg,#1e1035,#3d1a6e);border-radius:16px;padding:25px 28px;margin-bottom:25px;display:flex;align-items:center;gap:18px;}
.wr-header-icon{width:56px;height:56px;background:rgba(255,204,0,.15);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0;}
.wr-header h2{color:#fff;font-size:20px;font-weight:800;}
.wr-header p{color:rgba(255,255,255,.5);font-size:13px;margin-top:3px;}

.alert-box{display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:13px;font-weight:700;background:#e8faf4;color:#00875a;border:1.5px solid #b2dfdb;}
.alert-box i{font-size:18px;}

.ratio-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:25px;}
.ratio-card{background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,.06);border-top:4px solid var(--rc);transition:.2s;}
.ratio-card:hover{transform:translateY(-3px);box-shadow:0 8px 25px rgba(0,0,0,.1);}
.rc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.rc-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;background:rgba(var(--rc-rgb),.12);}
.rc-badge{font-size:10px;font-weight:800;padding:4px 10px;border-radius:20px;background:rgba(var(--rc-rgb),.12);color:var(--rc);letter-spacing:.5px;}
.rc-label{font-size:13px;font-weight:800;color:#1e1035;margin-bottom:3px;}
.rc-desc{font-size:11px;color:#aaa;margin-bottom:14px;}
.rc-input-wrap{display:flex;align-items:center;gap:10px;}
.rc-input-wrap span{font-size:13px;color:#888;font-weight:700;white-space:nowrap;}
.rc-input{width:80px;padding:9px 12px;border:2px solid #ece8f5;border-radius:10px;font-size:16px;font-weight:800;color:#1e1035;text-align:center;outline:none;transition:.2s;}
.rc-input:focus{border-color:var(--rc);box-shadow:0 0 0 3px rgba(var(--rc-rgb),.12);}
.rc-preview{margin-top:12px;padding:10px;background:#f8f5ff;border-radius:10px;font-size:12px;color:#6c2d7e;font-weight:700;text-align:center;}
.rc-preview span{font-size:15px;color:#00b894;}

.save-bar{background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 2px 12px rgba(0,0,0,.06);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;}
.save-bar p{font-size:13px;color:#888;}
.save-bar p strong{color:#1e1035;}
.save-btn{padding:13px 32px;background:linear-gradient(135deg,#6c2d7e,#9b4db5);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:8px;transition:.2s;letter-spacing:.3px;}
.save-btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(108,45,126,.35);}
</style>

<div class="wr-wrap">

<?php if($msg === 'success'): ?>
<div class="alert-box"><i class="fas fa-check-circle"></i> Win Ratios successfully update ho gaye!</div>
<?php endif; ?>

<div class="wr-header">
  <div class="wr-header-icon">🎯</div>
  <div>
    <h2>Win Ratios Management</h2>
    <p>Har bet type ke liye jeetne ka ratio set karein — ₹1 bet par kitna milega</p>
  </div>
</div>

<form method="POST" id="ratioForm">
<div class="ratio-grid">
<?php
$config = [
  'single'      => ['icon'=>'1️⃣', 'label'=>'Single Ank',    'desc'=>'1 digit number (0-9)',           'color'=>'#0984e3', 'rgb'=>'9,132,227',   'tag'=>'BASIC'],
  'jodi'        => ['icon'=>'2️⃣', 'label'=>'Jodi',          'desc'=>'2 digit number (00-99)',          'color'=>'#6c2d7e', 'rgb'=>'108,45,126',  'tag'=>'POPULAR'],
  'sp'          => ['icon'=>'🔢', 'label'=>'Single Panna',   'desc'=>'3 digit, all unique (e.g. 123)',  'color'=>'#00b894', 'rgb'=>'0,184,148',   'tag'=>'SP'],
  'dp'          => ['icon'=>'🔣', 'label'=>'Double Panna',   'desc'=>'3 digit, 2 same (e.g. 112)',      'color'=>'#e67e22', 'rgb'=>'230,126,34',  'tag'=>'DP'],
  'tp'          => ['icon'=>'💎', 'label'=>'Triple Panna',   'desc'=>'3 digit, all same (e.g. 111)',    'color'=>'#e84393', 'rgb'=>'232,67,147',  'tag'=>'TP'],
  'half_sangam' => ['icon'=>'⚡', 'label'=>'Half Sangam',    'desc'=>'Open panna + Close ank',          'color'=>'#f39c12', 'rgb'=>'243,156,18',  'tag'=>'ADV'],
  'full_sangam' => ['icon'=>'👑', 'label'=>'Full Sangam',    'desc'=>'Complete result match',           'color'=>'#c0003c', 'rgb'=>'192,0,60',    'tag'=>'JACKPOT'],
];

while($r = $ratios->fetch_assoc()):
  $c = $config[$r['bet_type']] ?? ['icon'=>'🎲','label'=>strtoupper($r['bet_type']),'desc'=>'','color'=>'#6c2d7e','rgb'=>'108,45,126','tag'=>''];
?>
<div class="ratio-card" style="--rc:<?= $c['color'] ?>;--rc-rgb:<?= $c['rgb'] ?>;">
  <div class="rc-top">
    <div class="rc-icon"><?= $c['icon'] ?></div>
    <div class="rc-badge"><?= $c['tag'] ?></div>
  </div>
  <div class="rc-label"><?= $c['label'] ?></div>
  <div class="rc-desc"><?= $c['desc'] ?></div>
  <div class="rc-input-wrap">
    <span>1 : </span>
    <input type="number" class="rc-input" name="ratio[<?= $r['bet_type'] ?>]" value="<?= $r['ratio'] ?>" min="1" max="99999"
      oninput="updatePreview(this, '<?= $r['bet_type'] ?>')" id="inp_<?= $r['bet_type'] ?>">
    <span>ratio</span>
  </div>
  <div class="rc-preview" id="prev_<?= $r['bet_type'] ?>">
    ₹10 bet → win <span>₹<?= number_format(10 * $r['ratio']) ?></span>
  </div>
</div>
<?php endwhile; ?>
</div>

<div class="save-bar">
  <p><strong>Note:</strong> Ratio change karne ke baad sabhi naye bets pe effect hoga.</p>
  <button type="submit" name="save" class="save-btn"><i class="fas fa-save"></i> Save All Ratios</button>
</div>
</form>

</div>

<script>
function updatePreview(inp, type) {
  var val = parseInt(inp.value) || 0;
  var prev = document.getElementById('prev_' + type);
  if(prev) prev.innerHTML = '₹10 bet → win <span>₹' + (10 * val).toLocaleString('en-IN') + '</span>';
}
</script>

<?php include 'footer.php'; ?>
