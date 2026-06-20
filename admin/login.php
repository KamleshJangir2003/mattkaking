<?php
session_start();
require_once '../includes/db.php';
if (isset($_SESSION['admin_id'])) { header("Location: dashboard.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, $_POST['username']);
    $password = $_POST['password'];
    $r = $conn->query("SELECT * FROM admins WHERE username='$username'");
    $admin = $r->fetch_assoc();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_user'] = $admin['username'];
        header("Location: dashboard.php"); exit;
    }
    $error = 'Username ya password galat hai!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login - Crazy King</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:linear-gradient(135deg,#1a0030 0%,#3a0060 100%);display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:Helvetica,sans-serif;font-weight:700;padding:15px;}
.box{background:#fff;border-radius:20px;width:100%;max-width:360px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5);}
.box-header{background:linear-gradient(135deg,#7c4066,#a0005a);padding:30px 25px;text-align:center;}
.box-header .logo{font-size:36px;margin-bottom:8px;}
.box-header h1{color:#fff;font-size:20px;letter-spacing:2px;}
.box-header p{color:rgba(255,255,255,.7);font-size:12px;font-style:normal;margin-top:5px;}
.box-body{padding:28px 25px;}
h2{font-size:17px;color:#1a0030;margin-bottom:20px;text-align:center;}
.form-group{margin-bottom:15px;}
.form-group label{display:block;font-size:11px;color:#888;margin-bottom:5px;font-style:normal;text-transform:uppercase;letter-spacing:.5px;}
.form-group input{width:100%;padding:12px 14px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;font-family:inherit;transition:border .2s;}
.form-group input:focus{border-color:#7c4066;outline:none;box-shadow:0 0 0 3px rgba(124,64,102,.1);}
.pwd-wrap{position:relative;}
.pwd-wrap input{padding-right:45px;}
.pwd-eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:#888;}
.btn{width:100%;padding:14px;background:linear-gradient(135deg,#7c4066,#a0005a);color:#fff;border:none;border-radius:10px;font-size:15px;cursor:pointer;font-family:inherit;font-weight:700;transition:all .2s;letter-spacing:.5px;}
.btn:hover{transform:translateY(-1px);box-shadow:0 5px 20px rgba(124,64,102,.5);}
.error-box{background:#fff0f0;border:2px solid #ff0016;border-radius:10px;padding:12px 14px;margin-bottom:16px;font-style:normal;display:flex;align-items:center;gap:8px;}
.error-box span{font-size:13px;color:#c00;}
.back-link{text-align:center;margin-top:15px;font-size:12px;font-style:normal;}
.back-link a{color:#7c4066;text-decoration:none;}
</style>
</head>
<body>
<div class="box">
  <div class="box-header">
    <div class="logo">⚙️</div>
    <h1>ADMIN PANEL</h1>
    <p>Crazy King Management</p>
  </div>
  <div class="box-body">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
    <div class="error-box"><span>❌</span><span><?= htmlspecialchars($error) ?></span></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>👤 Username</label>
        <input type="text" name="username" required placeholder="Admin username" value="<?= isset($_POST['username'])?htmlspecialchars($_POST['username']):'' ?>">
      </div>
      <div class="form-group">
        <label>🔒 Password</label>
        <div class="pwd-wrap">
          <input type="password" name="password" required placeholder="Password daaliye" id="apwd">
          <button type="button" class="pwd-eye" onclick="var i=document.getElementById('apwd');i.type=i.type==='password'?'text':'password';this.textContent=i.type==='password'?'👁':'🙈';">👁</button>
        </div>
      </div>
      <button type="submit" class="btn">Login Karein →</button>
    </form>
    <div class="back-link"><a href="../index.html">← Main Site</a></div>
  </div>
</div>
</body>
</html>
