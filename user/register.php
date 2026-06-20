<?php
session_start();
require_once '../includes/db.php';
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = sanitize($conn, $_POST['name']);
    $mobile = sanitize($conn, $_POST['mobile']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (strlen($mobile) != 10 || !ctype_digit($mobile)) {
        $error = 'Mobile number 10 digit ka hona chahiye!';
    } elseif (strlen($password) < 6) {
        $error = 'Password kam se kam 6 characters ka hona chahiye!';
    } elseif ($password !== $confirm) {
        $error = 'Password aur Confirm Password match nahi kar rahe!';
    } else {
        $check = $conn->query("SELECT id FROM users WHERE mobile='$mobile'");
        if ($check->num_rows > 0) {
            $error = 'Yeh mobile number already registered hai!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO users (name,mobile,password) VALUES ('$name','$mobile','$hash')");
            $uid = $conn->insert_id;
            $_SESSION['user_id'] = $uid;
            $_SESSION['user_name'] = $name;
            header("Location: dashboard.php"); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register - Crazy King</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:linear-gradient(135deg,#ffaa33 0%,#ff6600 100%);display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:Helvetica,sans-serif;font-weight:700;padding:15px;}
.box{background:#fff;border-radius:20px;width:100%;max-width:380px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3);}
.box-header{background:linear-gradient(135deg,#1a0030,#3a0060);padding:22px 25px;text-align:center;}
.box-header .logo{font-size:28px;margin-bottom:6px;}
.box-header h1{color:#ffcc00;font-size:18px;letter-spacing:1px;}
.box-body{padding:22px 25px;}
.form-group{margin-bottom:13px;}
.form-group label{display:block;font-size:11px;color:#888;margin-bottom:4px;font-style:normal;text-transform:uppercase;letter-spacing:.5px;}
.form-group input{width:100%;padding:11px 13px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;font-family:inherit;transition:border .2s;}
.form-group input:focus{border-color:#7c4066;outline:none;box-shadow:0 0 0 3px rgba(124,64,102,.1);}
.form-group input.valid{border-color:#28a745;}
.form-group input.invalid{border-color:#ff0016;}
.field-hint{font-size:11px;margin-top:3px;font-style:normal;}
.field-hint.ok{color:#28a745;} .field-hint.err{color:#ff0016;}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;border:none;border-radius:10px;font-size:15px;cursor:pointer;font-family:inherit;font-weight:700;transition:all .2s;}
.btn:hover{transform:translateY(-1px);box-shadow:0 5px 15px rgba(124,64,102,.4);}
.error-box{background:#fff0f0;border:2px solid #ff0016;border-radius:10px;padding:11px 13px;margin-bottom:13px;font-style:normal;display:flex;gap:8px;align-items:flex-start;}
.error-box span{font-size:13px;color:#c00;}
.link-row{text-align:center;margin-top:14px;font-size:13px;font-style:normal;color:#888;}
.link-row a{color:#7c4066;font-weight:700;text-decoration:none;}
</style>
</head>
<body>
<div class="box">
  <div class="box-header">
    <div class="logo">👑</div>
    <h1>CRAZY KING</h1>
  </div>
  <div class="box-body">
    <h2 style="font-size:17px;color:#1a0030;margin-bottom:15px;text-align:center;">New Account Banayein</h2>
    <?php if ($error): ?>
    <div class="error-box"><span>❌</span><span><?= htmlspecialchars($error) ?></span></div>
    <?php endif; ?>
    <form method="POST" onsubmit="return validateForm()">
      <div class="form-group">
        <label>👤 Full Name</label>
        <input type="text" name="name" required placeholder="Apna pura naam" value="<?= isset($_POST['name'])?htmlspecialchars($_POST['name']):'' ?>">
      </div>
      <div class="form-group">
        <label>📱 Mobile Number</label>
        <input type="tel" name="mobile" required placeholder="10 digit mobile number" maxlength="10" id="mobileInp" oninput="checkMobile(this)" value="<?= isset($_POST['mobile'])?htmlspecialchars($_POST['mobile']):'' ?>">
        <div class="field-hint" id="mobileHint"></div>
      </div>
      <div class="form-group">
        <label>🔒 Password</label>
        <input type="password" name="password" required placeholder="Min 6 characters" minlength="6" id="pwdInp" oninput="checkPwd()">
        <div class="field-hint" id="pwdHint"></div>
      </div>
      <div class="form-group">
        <label>🔒 Confirm Password</label>
        <input type="password" name="confirm_password" required placeholder="Password dobara daaliye" id="cpwdInp" oninput="checkCPwd()">
        <div class="field-hint" id="cpwdHint"></div>
      </div>
      <button type="submit" class="btn">Account Banayein ✓</button>
    </form>
    <div class="link-row">Already account hai? <a href="login.php">Login karein</a></div>
  </div>
</div>
<script>
function checkMobile(inp) {
  var h = document.getElementById('mobileHint');
  var v = inp.value.replace(/\D/,'');
  inp.value = v;
  if (v.length === 10) { inp.className='valid'; h.className='field-hint ok'; h.textContent='✅ Valid mobile number'; }
  else if (v.length > 0) { inp.className='invalid'; h.className='field-hint err'; h.textContent='❌ 10 digit mobile number chahiye ('+v.length+'/10)'; }
  else { inp.className=''; h.textContent=''; }
}
function checkPwd() {
  var v = document.getElementById('pwdInp').value;
  var h = document.getElementById('pwdHint');
  if (v.length >= 6) { h.className='field-hint ok'; h.textContent='✅ Password theek hai'; }
  else if (v.length > 0) { h.className='field-hint err'; h.textContent='❌ Kam se kam 6 characters chahiye'; }
  else { h.textContent=''; }
  checkCPwd();
}
function checkCPwd() {
  var p = document.getElementById('pwdInp').value;
  var c = document.getElementById('cpwdInp').value;
  var h = document.getElementById('cpwdHint');
  if (!c) { h.textContent=''; return; }
  if (p === c) { h.className='field-hint ok'; h.textContent='✅ Password match kar raha hai'; }
  else { h.className='field-hint err'; h.textContent='❌ Password match nahi kar raha'; }
}
function validateForm() {
  var mobile = document.getElementById('mobileInp').value;
  var pwd = document.getElementById('pwdInp').value;
  var cpwd = document.getElementById('cpwdInp').value;
  if (mobile.length !== 10) { alert('Mobile number 10 digit ka hona chahiye!'); return false; }
  if (pwd.length < 6) { alert('Password kam se kam 6 characters ka hona chahiye!'); return false; }
  if (pwd !== cpwd) { alert('Password match nahi kar raha!'); return false; }
  return true;
}
</script>
</body>
</html>
