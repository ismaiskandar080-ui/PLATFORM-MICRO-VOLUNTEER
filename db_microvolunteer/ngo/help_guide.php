<?php session_start(); require_once '../config/db.php'; requireLogin('ngo'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NGO Help Guide — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>
    .guide-section { margin-bottom:48px; } .guide-title { font-family:'Playfair Display',serif; font-size:22px; font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:12px; }
    .guide-steps { counter-reset:step; display:flex; flex-direction:column; gap:16px; } .guide-step { display:flex; gap:18px; align-items:flex-start; background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:20px 24px; }
    .step-num-badge { width:36px; height:36px; border-radius:50%; background:var(--blue-dim); border:1px solid rgba(91,156,246,.3); display:flex; align-items:center; justify-content:center; font-family:'Playfair Display',serif; font-size:16px; font-weight:700; color:var(--blue); flex-shrink:0; }
    .step-content-title { font-size:15px; font-weight:500; margin-bottom:6px; } .step-content-desc { font-size:13px; color:var(--grey-light); line-height:1.7; }
    .tip-box { background:rgba(91,156,246,.07); border:1px solid rgba(91,156,246,.2); border-radius:var(--radius); padding:20px 24px; margin-top:12px; } .tip-box p { font-size:13px; color:var(--blue); line-height:1.7; }
    .faq-item { border:1px solid var(--border); border-radius:var(--radius); margin-bottom:10px; overflow:hidden; } .faq-q { padding:18px 22px; cursor:pointer; font-size:14px; font-weight:500; display:flex; justify-content:space-between; align-items:center; transition:background .2s; }
    .faq-q:hover { background:rgba(255,255,255,.04); } .faq-a { padding:0 22px; max-height:0; overflow:hidden; transition:max-height .3s ease, padding .3s; font-size:13px; color:var(--grey-light); line-height:1.7; } .faq-a.open { max-height:500px; padding:0 22px 18px; }
  </style>
</head>
<body>
<div class="dash-wrap">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><span style="width:12px;height:12px;background:var(--white);transform:rotate(45deg);display:inline-block"></span>&nbsp;MicroVolunteer</a>
    <div class="sidebar-user">
      <div class="avatar">🏢</div>
      <div><div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['name']) ?></div><div class="sidebar-user-role">NGO Organization</div></div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-nav-title">Menu</div>
      <a href="dashboard.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>Dashboard</a>
      <a href="project_add.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 5v6M5 8h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Add Project</a>
      <a href="project_manage.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Manage Projects</a>
      <div class="sidebar-nav-title" style="margin-top:16px">Volunteers</div>
      <a href="volunteer_list.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.3"/><path d="M1 14c0-3 2.24-5 5-5s5 2 5 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Volunteer List</a>
      <div class="sidebar-nav-title" style="margin-top:16px">Information</div>
      <a href="help_guide.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help</a>
      <a href="about.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header"><h1 class="page-title">How to <span class="italic-blue">Help</span> (NGO)</h1><p class="page-sub">Complete guide for NGO organizations.</p></div>
    <div class="guide-section">
      <div class="guide-title">➕ 1. How to Add a New Project</div>
      <div class="guide-steps">
        <div class="guide-step"><div class="step-num-badge">1</div><div><div class="step-content-title">Click "Add Project"</div><div class="step-content-desc">Go to the sidebar menu.</div></div></div>
        <div class="guide-step"><div class="step-num-badge">2</div><div><div class="step-content-title">Fill in Project Information</div><div class="step-content-desc">Complete all fields: Name, Description, Category, Date, Time, Venue, Quota, and Contact No.</div></div></div>
        <div class="guide-step"><div class="step-num-badge">3</div><div><div class="step-content-title">Pin Location on Map</div><div class="step-content-desc">Mark the exact project location on the interactive map.</div></div></div>
        <div class="guide-step"><div class="step-num-badge">4</div><div><div class="step-content-title">Post Project</div><div class="step-content-desc">Your project will be listed immediately.</div></div></div>
      </div>
    </div>
    <div class="guide-section">
      <div class="guide-title">📋 2. How to Manage Projects</div>
      <div class="guide-steps">
        <div class="guide-step"><div class="step-num-badge">1</div><div><div class="step-content-title">View All Projects</div><div class="step-content-desc">Use the filter tabs in "Manage Projects".</div></div></div>
        <div class="guide-step"><div class="step-num-badge">2</div><div><div class="step-content-title">Mark Project as Completed</div><div class="step-content-desc">Click "✓ Complete" after the project is over.</div></div></div>
        <div class="guide-step"><div class="step-num-badge">3</div><div><div class="step-content-title">Cancel Project</div><div class="step-content-desc">Click "✗ Cancel" if needed. You can reactivate later.</div></div></div>
      </div>
    </div>
    <div class="guide-section">
      <div class="guide-title">👥 3. How to Manage Volunteers</div>
      <div class="guide-steps">
        <div class="guide-step"><div class="step-num-badge">1</div><div><div class="step-content-title">View Applications</div><div class="step-content-desc">Check the "Volunteer List" or dashboard.</div></div></div>
        <div class="guide-step"><div class="step-num-badge">2</div><div><div class="step-content-title">Accept or Reject</div><div class="step-content-desc">Review profiles and take action. Volunteers receive notifications.</div></div></div>
        <div class="guide-step"><div class="step-num-badge">3</div><div><div class="step-content-title">Record Attendance</div><div class="step-content-desc">Mark volunteers as present or absent on project day.</div></div></div>
        <div class="guide-step"><div class="step-num-badge">4</div><div><div class="step-content-title">Read Feedback</div><div class="step-content-desc">Review volunteer comments and ratings to improve.</div></div></div>
      </div>
    </div>
    <div class="guide-section">
      <div class="guide-title">❓ FAQ</div>
      <?php foreach ([['Can I edit?','Yes, through Manage Projects.'],['How many volunteers?','Set a quota when adding a project.'],['How to view feedback?','Check the volunteer details panel.']] as $i => $faq): ?>
      <div class="faq-item"><div class="faq-q" onclick="toggleFaq(<?=$i?>)"><?=$faq[0]?><span id="faq-icon-<?=$i?>">+</span></div><div class="faq-a" id="faq-a-<?=$i?>"><?=$faq[1]?></div></div>
      <?php endforeach; ?>
    </div>
  </main>
</div>
<script src="../assets/js/main.js"></script>
<script>
function toggleFaq(i) { const a = document.getElementById('faq-a-'+i), icon = document.getElementById('faq-icon-'+i); a.classList.toggle('open'); icon.textContent = a.classList.contains('open') ? '×' : '+'; }
</script>
</body>
</html>
