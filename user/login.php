<?php
session_start();
require_once '../includes/db.php';
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = sanitize($conn, $_POST['mobile']);
    $password = $_POST['password'];
    $r = $conn->query("SELECT * FROM users WHERE mobile='$mobile'");
    $user = $r->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'blocked') {
            $error = 'Aapka account block hai. Admin se contact karein.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php"); exit;
        }
    } else {
        $error = 'Mobile ya password galat hai!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - Crazy King</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:linear-gradient(135deg,#ffaa33 0%,#ff6600 100%);display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:Helvetica,sans-serif;font-weight:700;padding:15px;}
.box{background:#fff;border-radius:20px;width:100%;max-width:360px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3);}
.box-header{background:linear-gradient(135deg,#1a0030,#3a0060);padding:30px 25px;text-align:center;}
.box-header .logo{font-size:30px;margin-bottom:8px;}
.box-header h1{color:#ffcc00;font-size:20px;letter-spacing:1px;}
.box-header p{color:#ccc;font-size:12px;font-style:normal;margin-top:5px;}
.box-body{padding:25px;}
.form-group{margin-bottom:14px;}
.form-group label{display:block;font-size:11px;color:#888;margin-bottom:5px;font-style:normal;text-transform:uppercase;letter-spacing:.5px;}
.form-group input{width:100%;padding:12px 14px;border:2px solid #e0e0e0;border-radius:10px;font-size:15px;font-family:inherit;transition:border .2s;}
.form-group input:focus{border-color:#7c4066;outline:none;box-shadow:0 0 0 3px rgba(124,64,102,.1);}
.btn{width:100%;padding:14px;background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;border:none;border-radius:10px;font-size:15px;cursor:pointer;font-family:inherit;font-weight:700;transition:all .2s;letter-spacing:.5px;}
.btn:hover{transform:translateY(-1px);box-shadow:0 5px 15px rgba(124,64,102,.4);}
.error-box{background:#fff0f0;border:2px solid #ff0016;border-radius:10px;padding:12px 14px;margin-bottom:15px;display:flex;align-items:center;gap:10px;font-style:normal;}
.error-box span{font-size:13px;color:#c00;flex:1;}
.link-row{text-align:center;margin-top:16px;font-size:13px;font-style:normal;color:#888;}
.link-row a{color:#7c4066;font-weight:700;text-decoration:none;}
.divider{text-align:center;margin:15px 0;color:#ccc;font-size:12px;font-style:normal;position:relative;}
.divider::before,.divider::after{content:'';position:absolute;top:50%;width:40%;height:1px;background:#eee;}
.divider::before{left:0;}.divider::after{right:0;}
</style>
</head>
<body>
<div class="box">
  <div class="box-header">
    <div class="logo">👑</div>
    <h1>CRAZY KING</h1>
    <p>Satta Matka Fast Result</p>
  </div>
  <div class="box-body">
    <h2 style="font-size:18px;color:#1a0030;margin-bottom:18px;text-align:center;">User Login</h2>
    <?php if ($error): ?>
    <div class="error-box">
      <span>❌</span><span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>📱 Mobile Number</label>
        <input type="tel" name="mobile" required placeholder="10 digit mobile number" maxlength="10" pattern="[0-9]{10}">
      </div>
      <div class="form-group">
        <label>🔒 Password</label>
        <div style="position:relative;">
          <input type="password" name="password" required placeholder="Password daaliye" id="pwdInp" style="padding-right:45px;">
          <button type="button" onclick="togglePwd()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:#888;" id="eyeBtn">👁</button>
        </div>
      </div>
      <button type="submit" class="btn">Login Karein →</button>
    </form>
    <div class="divider">OR</div>
    <div class="link-row">New user? <a href="register.php">Register karein</a></div>
    <div class="link-row" style="margin-top:8px;"><a href="../index.html" style="color:#aaa;">← Main Site par jaiye</a></div>
  </div>
</div>
<script>
function togglePwd(){
  var inp = document.getElementById('pwdInp');
  inp.type = inp.type==='password' ? 'text' : 'password';
  document.getElementById('eyeBtn').textContent = inp.type==='password' ? '👁' : '🙈';
}
</script>
</body>
</html>
