<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/register.php");
    exit();
} else {
    if ($_SESSION['role'] === 'volunteer') header("Location: volunteer/dashboard.php");
    elseif ($_SESSION['role'] === 'ngo') header("Location: ngo/dashboard.php");
    else header("Location: auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MicroVolunteer — Malaysia Volunteer Platform</title>
  <link rel="stylesheet" href="assets/css/style.css"/>
  <style>
    #hero {
      position:relative; min-height:100vh;
      display:flex; flex-direction:column; justify-content:flex-end;
      padding:0 56px 80px;
    }
    .hero-bg {
      position:absolute; inset:0;
      background:url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=1600&q=80') center/cover no-repeat;
      filter:brightness(0.3);
      animation:bgZoom 18s ease-in-out infinite alternate;
    }
    @keyframes bgZoom{from{transform:scale(1.04)}to{transform:scale(1.10)}}
    .hero-overlay {
      position:absolute; inset:0;
      background:linear-gradient(to top,rgba(8,8,8,0.9) 0%,rgba(8,8,8,0.1) 60%,transparent 100%);
    }
    .hero-content { position:relative; z-index:2; max-width:1100px; }
    .hero-tag {
      display:inline-flex; align-items:center; gap:8px;
      font-size:11px; letter-spacing:0.18em; text-transform:uppercase;
      color:#a8c8ff; margin-bottom:28px; opacity:0;
      animation:fadeUp 0.8s 0.3s forwards;
    }
    .hero-tag::before{content:'';width:24px;height:1px;background:#5b9cf6}
    .hero-title {
      font-size:clamp(48px,7vw,100px); font-weight:900; line-height:1.02;
      letter-spacing:-0.02em; margin-bottom:18px; opacity:0;
      animation:fadeUp 0.9s 0.5s forwards;
    }
    .hero-sub {
      font-family:'Cormorant Garamond',serif; font-style:italic;
      font-size:clamp(20px,2.5vw,34px); color:#5b9cf6; margin-bottom:56px;
      opacity:0; animation:fadeUp 0.9s 0.7s forwards;
    }
    .hero-stats {
      display:flex; gap:60px; flex-wrap:wrap;
      opacity:0; animation:fadeUp 0.9s 1s forwards;
    }
    .hero-stat-num{font-family:'Playfair Display',serif;font-size:clamp(26px,3vw,40px);font-weight:700}
    .hero-stat-lbl{font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:#666;margin-top:5px}
    .hero-cta {
      position:absolute; bottom:80px; right:56px; z-index:2;
      display:flex; gap:14px; flex-wrap:wrap;
      opacity:0; animation:fadeUp 0.9s 1.1s forwards;
    }
    .scroll-hint {
      position:absolute; bottom:32px; left:50%; transform:translateX(-50%);
      z-index:2; display:flex; flex-direction:column; align-items:center; gap:7px;
      opacity:0; animation:fadeUp 1s 1.4s forwards;
    }
    .scroll-hint span{font-size:9px;letter-spacing:0.2em;text-transform:uppercase;color:#555}
    .scroll-line{width:1px;height:36px;background:linear-gradient(to bottom,#555,transparent);animation:scrollPulse 2s ease-in-out infinite}
    @keyframes scrollPulse{0%,100%{opacity:0.3}50%{opacity:1}}
    @keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}

    section { padding:120px 56px; }
    .section-title { font-size:clamp(32px,5vw,72px); line-height:1.06; margin-bottom:0; }

    #cara-bantu { background:var(--black); border-top:1px solid var(--border); }
    .steps-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:3px; margin-top:64px; }
    .step-card { background:var(--card); padding:48px 40px; border-radius:4px; position:relative; transition:background 0.3s; }
    .step-card:hover{background:var(--card2)}
    .step-num { font-family:'Playfair Display',serif; font-size:80px; font-weight:900; color:rgba(255,255,255,0.04); position:absolute; top:20px; right:28px; line-height:1; }
    .step-icon { font-size:32px; margin-bottom:24px; }
    .step-title { font-family:'Playfair Display',serif; font-size:20px; font-weight:700; margin-bottom:12px; }
    .step-desc { font-size:13px; color:#888; line-height:1.7; }

    #projek { background:var(--dark); border-top:1px solid var(--border); }
    .projek-header { display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:52px; flex-wrap:wrap; gap:20px; }
    .tasks-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }

    #join { background:var(--black); padding:80px 56px 120px; }
    .join-box {
      background:var(--card); border:1px solid var(--border);
      border-radius:28px; padding:96px 80px; text-align:center;
      position:relative; overflow:hidden;
    }
    .join-box::before {
      content:''; position:absolute; top:-60px; left:50%; transform:translateX(-50%);
      width:360px; height:360px; border-radius:50%;
      background:radial-gradient(circle,rgba(91,156,246,0.07) 0%,transparent 70%);
      pointer-events:none;
    }
    .join-italic { font-family:'Cormorant Garamond',serif; font-style:italic; font-size:20px; color:#5b9cf6; display:block; margin-bottom:16px; }
    .join-title { font-size:clamp(32px,5vw,64px); margin-bottom:48px; }

    footer { background:var(--black); border-top:1px solid var(--border); padding:56px; }
    .footer-grid { display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:48px; margin-bottom:48px; }
    .footer-brand-txt { font-size:13px; color:#666; margin-top:12px; max-width:260px; line-height:1.7; }
    .footer-col h4 { font-size:10px; letter-spacing:0.2em; text-transform:uppercase; color:#555; margin-bottom:16px; }
    .footer-col a { display:block; font-size:14px; color:#888; text-decoration:none; margin-bottom:10px; transition:color 0.2s; }
    .footer-col a:hover { color:var(--white); }
    .footer-bottom { display:flex; justify-content:space-between; padding-top:24px; border-top:1px solid var(--border); font-size:12px; color:#444; flex-wrap:wrap; gap:12px; }

    @media(max-width:900px) {
      section, #join, footer { padding-left:24px; padding-right:24px; }
      #hero { padding:0 24px 80px; }
      .hero-cta { right:24px; flex-direction:column; }
      .steps-grid, .tasks-grid { grid-template-columns:1fr; gap:8px; }
      .join-box { padding:56px 28px; }
      .footer-grid { grid-template-columns:1fr 1fr; gap:32px; }
      .projek-header { flex-direction:column; align-items:flex-start; }
    }
  </style>
</head>
<body>
<nav class="navbar" id="navbar">
  <a href="index.php" class="nav-brand"><span class="diamond"></span>MicroVolunteer</a>
  <ul class="nav-links">
    <li><a href="#projek">Projects</a></li>
    <li><a href="#cara-bantu">How to Help</a></li>
    <li><a href="#join">About Us</a></li>
  </ul>
  <div class="nav-right">
    <?php if (isset($_SESSION['user_id'])): ?>
      <?php $dash = $_SESSION['role'] === 'ngo' ? 'ngo' : ($_SESSION['role'] === 'admin' ? 'admin' : 'volunteer'); ?>
      <a href="<?= $dash ?>/dashboard.php" class="btn btn-white btn-sm">Dashboard →</a>
    <?php else: ?>
      <a href="auth/login.php" style="font-size:12px;color:var(--grey-light);text-decoration:none;letter-spacing:0.1em">Login</a>
      <a href="auth/register.php" class="btn btn-white btn-sm">Register</a>
    <?php endif; ?>
  </div>
</nav>
<section id="hero">
  <div class="hero-bg"></div>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-tag">Malaysia Volunteer Platform</div>
    <h1 class="hero-title">Your skills can<br/>change lives</h1>
    <p class="hero-sub">in your hands</p>
    <div class="hero-stats">
      <div><div class="hero-stat-num" data-count="2400" data-suffix="+">2,400+</div><div class="hero-stat-lbl">Volunteers</div></div>
      <div><div class="hero-stat-num" data-count="180" data-suffix="+">180+</div><div class="hero-stat-lbl">Registered NGOs</div></div>
      <div><div class="hero-stat-num" data-count="9600" data-suffix="+">9,600+</div><div class="hero-stat-lbl">Hours Contributed</div></div>
    </div>
  </div>
  <div class="hero-cta">
    <a href="auth/register.php?role=volunteer" class="btn btn-white">Become a Volunteer</a>
    <a href="#projek" class="btn btn-ghost">View Projects</a>
  </div>
  <div class="scroll-hint"><span>Scroll</span><div class="scroll-line"></div></div>
</section>
<section id="cara-bantu">
  <div class="section-tag"><span class="dot"></span>How the Platform Works</div>
  <h2 class="section-title reveal">Three easy steps<br/>to real impact</h2>
  <div class="steps-grid">
    <div class="step-card reveal fade-up-1">
      <div class="step-num">01</div>
      <div class="step-icon">📋</div>
      <h3 class="step-title">NGOs Post Tasks</h3>
      <p class="step-desc">Organizations list micro-tasks with clear scope, skill requirements, dates, and locations.</p>
    </div>
    <div class="step-card reveal fade-up-2">
      <div class="step-num">02</div>
      <div class="step-icon">🙋</div>
      <h3 class="step-title">Volunteers Apply</h3>
      <p class="step-desc">Find projects via map or list. Apply in under a minute. The system auto-detects schedule conflicts.</p>
    </div>
    <div class="step-card reveal fade-up-3">
      <div class="step-num">03</div>
      <div class="step-icon">✅</div>
      <h3 class="step-title">Impact Verified</h3>
      <p class="step-desc">Complete tasks, provide feedback, and build a real impact record for your volunteer portfolio.</p>
    </div>
  </div>
</section>
<section id="projek">
  <div class="projek-header">
    <div>
      <div class="section-tag reveal"><span class="dot"></span>Active Projects</div>
      <h2 class="section-title reveal">Projects<br/>awaiting you</h2>
    </div>
    <a href="auth/register.php" class="btn btn-ghost reveal">View All →</a>
  </div>
  <?php
  require_once 'config/db.php';
  $pres = $conn->query("SELECT pr.*, u.name as ngo_name FROM projects pr JOIN users u ON pr.ngo_id=u.id WHERE pr.status='active' AND pr.date >= CURDATE() ORDER BY pr.created_at DESC LIMIT 3");
  $badge_map = ['education'=>'badge-blue','environment'=>'badge-green','social'=>'badge-orange','health'=>'badge-red','technology'=>'badge-purple','other'=>'badge-grey'];
  $kat_map   = ['education'=>'Education','environment'=>'Environment','social'=>'Social','health'=>'Health','technology'=>'Technology','other'=>'Others'];
  ?>
  <div class="tasks-grid">
  <?php while ($p = $pres->fetch_assoc()): $b = $badge_map[$p['category']]??'badge-grey'; $k=$kat_map[$p['category']]??'Other'; ?>
    <div class="task-card card-hover reveal">
      <div class="task-top">
        <span class="badge <?= $b ?>"><?= $k ?></span>
        <?php if(strtotime($p['date']) <= strtotime('+3 days')): ?><span style="font-size:11px;color:var(--orange)">● Urgent</span><?php endif; ?>
      </div>
      <div class="task-title"><?= htmlspecialchars($p['project_name']) ?></div>
      <div class="task-ngo"><?= htmlspecialchars($p['ngo_name']) ?></div>
      <div class="task-desc"><?= htmlspecialchars(substr($p['description'],0,110)) ?>...</div>
      <div class="task-meta">
        <span>📅 <?= date('d M Y',strtotime($p['date'])) ?></span>
        <span>📍 <?= htmlspecialchars($p['city']) ?></span>
        <span>👥 <?= $p['quota'] ?> slot</span>
      </div>
      <a href="auth/register.php" class="btn btn-ghost btn-sm" style="margin-top:16px;width:100%;justify-content:center">Register &amp; Apply →</a>
    </div>
  <?php endwhile; ?>
  </div>
</section>
<section id="join">
  <div class="join-box reveal">
    <span class="join-italic">together we are stronger</span>
    <h2 class="join-title">Join our community</h2>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <a href="auth/register.php?role=volunteer" class="btn btn-white" style="padding:18px 48px">Register as Volunteer</a>
      <a href="auth/register.php?role=ngo" class="btn btn-ghost" style="padding:18px 48px">Register as NGO</a>
    </div>
  </div>
</section>
<footer>
  <div class="footer-grid">
    <div>
      <a href="index.php" class="nav-brand"><span class="diamond"></span>&nbsp;MicroVolunteer</a>
      <p class="footer-brand-txt">A platform connecting volunteers and NGOs through flexible and meaningful micro-tasks in Malaysia.</p>
    </div>
    <div class="footer-col">
      <h4>Platform</h4>
      <a href="#projek">Find Projects</a>
      <a href="#cara-bantu">How it Works</a>
      <a href="auth/register.php?role=ngo">Register NGO</a>
    </div>
    <div class="footer-col">
      <h4>Account</h4>
      <a href="auth/login.php">Login</a>
      <a href="auth/register.php?role=volunteer">Volunteer Registration</a>
    </div>
    <div class="footer-col">
      <h4>Information</h4>
      <a href="#join">About Us</a>
      <a href="#">Privacy Policy</a>
      <a href="#">Contact Us</a>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2026 MicroVolunteer · Universiti Poly-Tech Malaysia · Ismadaris Qaiyum</span>
    <span>Diploma in Computer Science — Final Year Project</span>
  </div>
</footer>
<script src="assets/js/main.js"></script>
<script>
window.addEventListener('load', () => setTimeout(animateCounters, 1200));
</script>
</body>
</html>
