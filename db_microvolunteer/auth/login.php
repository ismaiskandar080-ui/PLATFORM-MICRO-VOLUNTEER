<?php
session_start();
require_once '../config/db.php';
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'volunteer') header("Location: ../volunteer/dashboard.php");
    elseif ($_SESSION['role'] === 'ngo') header("Location: ../ngo/dashboard.php");
    else header("Location: ../admin/dashboard.php");
    exit();
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = clean($_POST['role'] ?? 'volunteer');
    if (empty($email) || empty($password)) {
        $error = 'Please fill in email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['name']      = $user['name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['email']     = $email;
            if ($user['role'] === 'volunteer') header("Location: ../volunteer/dashboard.php");
            elseif ($user['role'] === 'ngo')   header("Location: ../ngo/dashboard.php");
            else                               header("Location: ../admin/dashboard.php");
            exit();
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>
    body { display:flex; overflow:hidden; min-height:100vh; }
    .left-panel {
      width:50%; position:relative; overflow:hidden;
      display:flex; flex-direction:column; justify-content:flex-end; padding:56px;
    }
    .left-bg {
      position:absolute; inset:0;
      background:url('https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=1200&q=80') center/cover no-repeat;
      filter:brightness(0.25);
      animation:bgZoom 20s ease-in-out infinite alternate;
    }
    @keyframes bgZoom { from{transform:scale(1.03)} to{transform:scale(1.1)} }
    .left-overlay {
      position:absolute; inset:0;
      background:linear-gradient(to top, rgba(8,8,8,0.97) 0%, rgba(8,8,8,0.15) 60%, transparent 100%);
    }
    .left-content { position:relative; z-index:2; }
    .left-brand {
      position:absolute; top:48px; left:56px; z-index:2;
      display:flex; align-items:center; gap:10px;
      font-size:13px; letter-spacing:0.14em; text-transform:uppercase;
      font-weight:500; text-decoration:none; color:var(--white);
    }
    .left-brand .diamond { width:13px; height:13px; background:var(--white); transform:rotate(45deg); }
    .left-title { font-size:clamp(28px,3.5vw,50px); line-height:1.08; margin-bottom:16px; }
    .left-sub { font-size:14px; color:var(--grey-light); line-height:1.75; max-width:320px; }
    .left-stats {
      display:flex; gap:40px; margin-top:44px; padding-top:28px;
      border-top:1px solid var(--border);
    }
    .lstat-num { font-family:'Playfair Display',serif; font-size:26px; font-weight:700; }
    .lstat-lbl { font-size:10px; letter-spacing:0.18em; text-transform:uppercase; color:var(--grey); margin-top:4px; }
    .right-panel {
      width:50%; background:var(--dark);
      display:flex; flex-direction:column;
      padding:48px 56px; border-left:1px solid var(--border); overflow-y:auto; height:100vh;
    }
    .form-box { width:100%; max-width:400px; margin:auto; padding:20px 0; }
    .form-tag-lbl {
      font-size:11px; letter-spacing:0.2em; text-transform:uppercase;
      color:var(--blue); margin-bottom:12px;
      display:flex; align-items:center; gap:8px;
    }
    .form-tag-lbl::before { content:''; width:20px; height:1px; background:var(--blue); }
    .form-title { font-size:clamp(26px,3vw,38px); margin-bottom:6px; }
    .form-sub { font-size:13px; color:var(--grey); margin-bottom:32px; }
    .form-sub a { color:var(--blue); text-decoration:none; }
    .form-sub a:hover { text-decoration:underline; }
    .role-tabs { display:flex; gap:8px; margin-bottom:28px; }
    .role-tab {
      flex:1; padding:11px 8px; border-radius:10px;
      border:1px solid var(--border); background:transparent;
      color:var(--grey-light); font-family:'DM Sans',sans-serif;
      font-size:12px; font-weight:400; cursor:pointer;
      transition:all 0.2s; text-align:center; letter-spacing:0.04em;
    }
    .role-tab:hover { border-color:rgba(255,255,255,0.2); color:var(--white); }
    .role-tab.active { background:var(--blue-dim); border-color:var(--blue); color:var(--white); }
    .role-tab .ti { font-size:18px; display:block; margin-bottom:5px; }
    .field-wrap { position:relative; }
    .field-icon {
      position:absolute; right:14px; top:50%; transform:translateY(-50%);
      color:var(--grey); pointer-events:none;
    }
    .toggle-pw {
      position:absolute; right:14px; top:50%; transform:translateY(-50%);
      background:none; border:none; color:var(--grey); cursor:pointer;
      padding:0; line-height:0; transition:color 0.2s;
    }
    .toggle-pw:hover { color:var(--white); }
    .forgot-row { text-align:right; margin:-10px 0 24px; }
    .forgot-row a { font-size:12px; color:var(--grey); text-decoration:none; transition:color 0.2s; }
    .forgot-row a:hover { color:var(--white); }
    .divider { display:flex; align-items:center; gap:14px; margin:24px 0; }
    .divider::before, .divider::after { content:''; flex:1; height:1px; background:var(--border); }
    .divider span { font-size:11px; color:var(--grey); letter-spacing:0.1em; }
    .reg-row { text-align:center; font-size:13px; color:var(--grey); }
    .reg-row a { color:var(--white); font-weight:500; text-decoration:none; }
    .reg-row a:hover { text-decoration:underline; }
    .btn-submit { position:relative; overflow:hidden; }
    .btn-submit.loading { pointer-events:none; opacity:0.7; }
    .btn-submit .spinner {
      display:none; width:16px; height:16px; border:2px solid rgba(0,0,0,0.25);
      border-top-color:#000; border-radius:50%; animation:spin 0.7s linear infinite;
      position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
    }
    .btn-submit.loading .btn-txt { opacity:0; }
    .btn-submit.loading .spinner { display:block; }
    @keyframes spin { to{transform:translate(-50%,-50%) rotate(360deg)} }
    @media(max-width:768px) {
      body { flex-direction:column; overflow:auto; }
      .left-panel { width:100%; min-height:220px; padding:32px 24px 40px; }
      .left-brand { top:24px; left:24px; }
      .left-stats { display:none; }
      .right-panel { width:100%; padding:40px 24px 60px; border-left:none; border-top:1px solid var(--border); }
    }
  </style>
</head>
<body>
<div class="left-panel">
  <div class="left-bg"></div>
  <div class="left-overlay"></div>
  <a href="../index.php" class="left-brand">
    <span class="diamond"></span> MicroVolunteer
  </a>
  <div class="left-content">
    <h1 class="left-title">Every skill<br/>has its place.<br/><span class="italic-blue">in your hands.</span></h1>
    <p class="left-sub">Log in and connect with the NGO and volunteer community across Malaysia.</p>
    <div class="left-stats">
      <div><div class="lstat-num">2,400+</div><div class="lstat-lbl">Volunteers</div></div>
      <div><div class="lstat-num">180+</div><div class="lstat-lbl">Registered NGOs</div></div>
      <div><div class="lstat-num">9,600+</div><div class="lstat-lbl">Hours Contributed</div></div>
    </div>
  </div>
</div>
<div class="right-panel">
  <div class="form-box">
    <div class="form-tag-lbl">Welcome back</div>
    <h2 class="form-title">Login</h2>
    <p class="form-sub">Don't have an account? <a href="register.php">Register now →</a></p>
    <div class="role-tabs">
      <button class="role-tab active" type="button" onclick="setRole('volunteer',this)">
        <span class="ti">🙋</span>Volunteer
      </button>
      <button class="role-tab" type="button" onclick="setRole('ngo',this)">
        <span class="ti">🏢</span>NGO Organization
      </button>
      <button class="role-tab" type="button" onclick="setRole('admin',this)">
        <span class="ti">⚙️</span>Admin
      </button>
    </div>
    <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" id="loginForm" novalidate>
      <input type="hidden" name="role" id="roleInput" value="volunteer"/>
      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="field-wrap">
          <input type="email" name="email" class="form-control" placeholder="name@email.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
          <span class="field-icon">
            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><rect x="1" y="3" width="13" height="9" rx="2" stroke="currentColor" stroke-width="1.2"/><path d="M1 5l6.5 4.5L14 5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
          </span>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="field-wrap">
          <input type="password" name="password" id="passInput" class="form-control" placeholder="••••••••" required/>
          <button type="button" class="toggle-pw" id="toggleBtn" onclick="togglePass('passInput','toggleBtn')">
            <svg width="17" height="17" viewBox="0 0 17 17" fill="none">
              <path d="M1 8.5S4 3 8.5 3 16 8.5 16 8.5 13 14 8.5 14 1 8.5 1 8.5z" stroke="currentColor" stroke-width="1.2"/>
              <circle cx="8.5" cy="8.5" r="2.3" stroke="currentColor" stroke-width="1.2"/>
            </svg>
          </button>
        </div>
      </div>
      <div class="forgot-row"><a href="#">Forgot password?</a></div>
      <button type="submit" class="btn btn-white btn-full btn-submit" id="submitBtn" onclick="this.classList.add('loading')">
        <span class="btn-txt">Login</span>
        <span class="spinner"></span>
      </button>
    </form>
    <div class="divider"><span>or</span></div>
    <div class="reg-row">New user? <a href="register.php">Create a free account →</a></div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
function setRole(role, btn) {
  document.getElementById('roleInput').value = role;
  document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
}
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const email = this.querySelector('[name="email"]').value.trim();
  const pass  = this.querySelector('[name="password"]').value;
  if (!email || !pass) {
    e.preventDefault();
    document.getElementById('submitBtn').classList.remove('loading');
  }
});
</script>
</body>
</html>
