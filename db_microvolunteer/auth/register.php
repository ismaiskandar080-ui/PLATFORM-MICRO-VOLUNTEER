<?php
session_start();
require_once '../config/db.php';
if (isset($_SESSION['user_id'])) {
    $dst = $_SESSION['role'] === 'ngo' ? '../ngo/dashboard.php' : '../volunteer/dashboard.php';
    header("Location: $dst"); exit;
}
$error = $success = '';
$role_default = in_array($_GET['role'] ?? '', ['volunteer','ngo']) ? $_GET['role'] : 'volunteer';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = clean($_POST['full_name'] ?? '');
    $email     = clean($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $role      = in_array($_POST['role'] ?? '', ['volunteer','ngo']) ? $_POST['role'] : 'volunteer';
    $phone      = clean($_POST['phone'] ?? '');
    if (empty($full_name) || empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all mandatory fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password2) {
        $error = 'Password confirmation does not match.';
    } else {
        $chk = $conn->prepare("SELECT id FROM users WHERE email=?");
        $chk->bind_param('s', $email); $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'This email is already registered. Please use another email or log in.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $full_name, $email, $hashed, $role);
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                if ($role === 'volunteer') {
                    $vp = $conn->prepare("INSERT INTO volunteer_profiles (user_id) VALUES (?)");
                    $vp->bind_param('i', $new_id); $vp->execute();
                } else {
                    $np = $conn->prepare("INSERT INTO ngo_profiles (user_id, organization_name) VALUES (?,?)");
                    $np->bind_param('is', $new_id, $full_name); $np->execute();
                }
                $_SESSION['user_id'] = $new_id;
                $_SESSION['name']    = $full_name;
                $_SESSION['role']    = $role;
                $_SESSION['email']   = $email;
                $dst = $role === 'ngo' ? '../ngo/dashboard.php' : '../volunteer/dashboard.php';
                header("Location: $dst"); exit;
            } else {
                $error = 'Error during registration. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register Account — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>
    body { display:flex; overflow:hidden; min-height:100vh; }
    .left-panel {
      width:45%; position:relative; overflow:hidden;
      display:flex; flex-direction:column; justify-content:flex-end; padding:56px;
    }
    .left-bg {
      position:absolute; inset:0;
      background:url('https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=1200&q=80') center/cover no-repeat;
      filter:brightness(0.22);
      animation:bgZoom 22s ease-in-out infinite alternate;
    }
    @keyframes bgZoom { from{transform:scale(1.03)} to{transform:scale(1.1)} }
    .left-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(8,8,8,0.97) 0%, rgba(8,8,8,0.1) 60%, transparent 100%); }
    .left-content { position:relative; z-index:2; }
    .left-brand { position:absolute; top:48px; left:56px; z-index:2; display:flex; align-items:center; gap:10px; font-size:13px; letter-spacing:.14em; text-transform:uppercase; font-weight:500; text-decoration:none; color:var(--white); }
    .left-brand .diamond { width:13px; height:13px; background:var(--white); transform:rotate(45deg); }
    .left-title { font-size:clamp(26px,3.2vw,46px); line-height:1.1; margin-bottom:14px; }
    .left-sub { font-size:14px; color:var(--grey-light); line-height:1.75; max-width:300px; }
    .right-panel {
      width:55%; background:var(--dark);
      display:flex; flex-direction:column;
      padding:40px 56px; border-left:1px solid var(--border); overflow-y:auto; height:100vh;
    }
    .form-box { width:100%; max-width:440px; margin:auto; padding:20px 0; }
    .form-tag-lbl { font-size:11px; letter-spacing:.2em; text-transform:uppercase; color:var(--blue); margin-bottom:12px; display:flex; align-items:center; gap:8px; }
    .form-tag-lbl::before { content:''; width:20px; height:1px; background:var(--blue); }
    .form-title { font-size:clamp(24px,2.8vw,36px); margin-bottom:6px; }
    .form-sub { font-size:13px; color:var(--grey); margin-bottom:28px; }
    .form-sub a { color:var(--blue); text-decoration:none; }
    .role-tabs { display:flex; gap:8px; margin-bottom:24px; }
    .role-tab { flex:1; padding:14px 10px; border-radius:12px; border:1px solid var(--border); background:transparent; color:var(--grey-light); font-family:'DM Sans',sans-serif; font-size:13px; cursor:pointer; transition:all .2s; text-align:center; }
    .role-tab:hover { border-color:rgba(255,255,255,.2); color:var(--white); }
    .role-tab.active { background:var(--blue-dim); border-color:var(--blue); color:var(--white); }
    .role-tab .ti { font-size:22px; display:block; margin-bottom:6px; }
    .role-tab .tl { font-size:12px; font-weight:500; letter-spacing:.06em; }
    .role-tab .ts { font-size:10px; color:var(--grey); display:block; margin-top:3px; }
    .role-tab.active .ts { color:rgba(91,156,246,.8); }
    .field-wrap { position:relative; }
    .toggle-pw { position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--grey); cursor:pointer; padding:0; line-height:0; transition:color .2s; }
    .toggle-pw:hover { color:var(--white); }
    .grid-2-reg { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .login-row { text-align:center; font-size:13px; color:var(--grey); margin-top:16px; }
    .login-row a { color:var(--white); font-weight:500; text-decoration:none; }
    .pw-strength { height:4px; border-radius:2px; margin-top:6px; background:var(--border); overflow:hidden; }
    .pw-fill { height:100%; border-radius:2px; width:0; transition:width .3s, background .3s; }
    @media(max-width:900px) {
      body { flex-direction:column; overflow:auto; }
      .left-panel { width:100%; min-height:200px; padding:28px 24px 36px; }
      .left-brand { top:24px; left:24px; }
      .right-panel { width:100%; padding:36px 24px 60px; border-left:none; border-top:1px solid var(--border); }
      .grid-2-reg { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>
<div class="left-panel">
  <div class="left-bg"></div>
  <div class="left-overlay"></div>
  <a href="../index.php" class="left-brand"><span class="diamond"></span>MicroVolunteer</a>
  <div class="left-content">
    <h1 class="left-title">Start<br/>your volunteer<br/><span class="italic-blue">journey.</span></h1>
    <p class="left-sub">Register for free and join thousands of volunteers making a difference in Malaysia.</p>
  </div>
</div>
<div class="right-panel">
  <div class="form-box">
    <div class="form-tag-lbl">New Registration</div>
    <h2 class="form-title">Create Account</h2>
    <p class="form-sub">Already have an account? <a href="login.php">Log in here →</a></p>
    <div class="role-tabs">
      <button class="role-tab <?= ($role_default === 'volunteer') ? 'active' : '' ?>" type="button" id="tab-vol" onclick="setRole('volunteer')">
        <span class="ti">🙋</span>
        <span class="tl">Volunteer</span>
        <span class="ts">Join campaigns & projects</span>
      </button>
      <button class="role-tab <?= ($role_default === 'ngo') ? 'active' : '' ?>" type="button" id="tab-ngo" onclick="setRole('ngo')">
        <span class="ti">🏢</span>
        <span class="tl">NGO Organization</span>
        <span class="ts">Post & manage projects</span>
      </button>
    </div>
    <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:20px"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" id="regForm" novalidate>
      <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($role_default) ?>"/>
      <div class="grid-2-reg">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Full Name <span style="color:var(--red)">*</span></label>
          <input type="text" name="full_name" class="form-control" placeholder="Your full name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Email <span style="color:var(--red)">*</span></label>
          <input type="email" name="email" class="form-control" placeholder="name@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Phone No. <span style="color:var(--red)">*</span></label>
          <input type="tel" name="phone" class="form-control" placeholder="01X-XXXXXXX" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Password <span style="color:var(--red)">*</span></label>
          <div class="field-wrap">
            <input type="password" name="password" id="passInput" class="form-control" placeholder="Min 6 characters" required oninput="checkStrength(this.value)"/>
            <button type="button" class="toggle-pw" onclick="togglePass('passInput',this)">
              <svg width="17" height="17" viewBox="0 0 17 17" fill="none"><path d="M1 8.5S4 3 8.5 3 16 8.5 16 8.5 13 14 8.5 14 1 8.5 1 8.5z" stroke="currentColor" stroke-width="1.2"/><circle cx="8.5" cy="8.5" r="2.3" stroke="currentColor" stroke-width="1.2"/></svg>
            </button>
          </div>
          <div class="pw-strength"><div class="pw-fill" id="pwFill"></div></div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password <span style="color:var(--red)">*</span></label>
          <div class="field-wrap">
            <input type="password" name="password2" id="pass2Input" class="form-control" placeholder="Repeat password" required/>
            <button type="button" class="toggle-pw" onclick="togglePass('pass2Input',this)">
              <svg width="17" height="17" viewBox="0 0 17 17" fill="none"><path d="M1 8.5S4 3 8.5 3 16 8.5 16 8.5 13 14 8.5 14 1 8.5 1 8.5z" stroke="currentColor" stroke-width="1.2"/><circle cx="8.5" cy="8.5" r="2.3" stroke="currentColor" stroke-width="1.2"/></svg>
            </button>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-white btn-full" style="margin-top:8px">Register Now →</button>
    </form>
    <div class="login-row">Already have an account? <a href="login.php">Login</a></div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
function setRole(role) {
  document.getElementById('roleInput').value = role;
  document.getElementById('tab-vol').classList.toggle('active', role === 'volunteer');
  document.getElementById('tab-ngo').classList.toggle('active', role === 'ngo');
}
function checkStrength(pw) {
  const fill = document.getElementById('pwFill');
  let score = 0;
  if (pw.length >= 6) score++;
  if (pw.length >= 10) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;
  const pct = (score / 5) * 100;
  const col = score <= 1 ? '#f87171' : score <= 3 ? '#fb923c' : '#4ade80';
  fill.style.width = pct + '%';
  fill.style.background = col;
}
</script>
</body>
</html>