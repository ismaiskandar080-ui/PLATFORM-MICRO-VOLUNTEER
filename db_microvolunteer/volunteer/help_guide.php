<?php
session_start();
require_once '../config/db.php';
requireLogin('volunteer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>How to Help — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>
    .guide-section { margin-bottom:48px; }
    .guide-title { font-family:'Playfair Display',serif; font-size:22px; font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:12px; }
    .guide-steps { counter-reset:step; display:flex; flex-direction:column; gap:16px; }
    .guide-step { display:flex; gap:18px; align-items:flex-start; background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:20px 24px; }
    .step-num-badge { width:36px; height:36px; border-radius:50%; background:var(--blue-dim); border:1px solid rgba(91,156,246,.3); display:flex; align-items:center; justify-content:center; font-family:'Playfair Display',serif; font-size:16px; font-weight:700; color:var(--blue); flex-shrink:0; }
    .step-content-title { font-size:15px; font-weight:500; margin-bottom:6px; }
    .step-content-desc { font-size:13px; color:var(--grey-light); line-height:1.7; }
    .tip-box { background:rgba(91,156,246,.07); border:1px solid rgba(91,156,246,.2); border-radius:var(--radius); padding:20px 24px; margin-top:12px; }
    .tip-box p { font-size:13px; color:var(--blue); line-height:1.7; }
    .faq-item { border:1px solid var(--border); border-radius:var(--radius); margin-bottom:10px; overflow:hidden; }
    .faq-q { padding:18px 22px; cursor:pointer; font-size:14px; font-weight:500; display:flex; justify-content:space-between; align-items:center; transition:background .2s; }
    .faq-q:hover { background:rgba(255,255,255,.04); }
    .faq-a { padding:0 22px; max-height:0; overflow:hidden; transition:max-height .3s ease, padding .3s; font-size:13px; color:var(--grey-light); line-height:1.7; }
    .faq-a.open { max-height:300px; padding:0 22px 18px; }
    .faq-icon { transition:transform .3s; }
    .faq-icon.open { transform:rotate(45deg); }
  </style>
</head>
<body>
<div class="dash-wrap">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><span style="width:12px;height:12px;background:var(--white);transform:rotate(45deg);display:inline-block"></span>&nbsp;MicroVolunteer</a>
    <div class="sidebar-user">
      <div class="avatar">🙋</div>
      <div><div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['name']) ?></div><div class="sidebar-user-role">Volunteer</div></div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-nav-title">Menu</div>
      <a href="dashboard.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>Dashboard</a>
      <a href="map.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1C5.24 1 3 3.24 3 6c0 4.25 5 9 5 9s5-4.75 5-9c0-2.76-2.24-5-5-5z" stroke="currentColor" stroke-width="1.3"/><circle cx="8" cy="6" r="1.8" stroke="currentColor" stroke-width="1.3"/></svg>Project Map</a>
      <a href="projects.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>All Projects</a>
      <div class="sidebar-nav-title" style="margin-top:16px">My Account</div>
      <a href="schedule.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 1v2M11 1v2M1 6h14" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>My Schedule</a>
      <a href="history.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 4.5V8l2.5 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Project History</a>
      <a href="help_guide.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help</a>
      <a href="about.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">How <span class="italic-blue">to Help</span></h1>
      <p class="page-sub">A complete guide to using the MicroVolunteer platform as a volunteer.</p>
    </div>
    <div class="guide-section">
      <div class="guide-title">🗺️ 1. How to Use the Project Map</div>
      <div class="guide-steps">
        <div class="guide-step">
          <div class="step-num-badge">1</div>
          <div><div class="step-content-title">Open Map Menu</div><div class="step-content-desc">Click on the <strong>"Project Map"</strong> link in the sidebar. A map of Malaysia will show colorful pins reflecting projects.</div></div>
        </div>
        <div class="guide-step">
          <div class="step-num-badge">2</div>
          <div><div class="step-content-title">Understand Pin Colors</div><div class="step-content-desc">Pins correspond to project categories — <span style="color:#5b9cf6">Blue = Education</span>, <span style="color:#4ade80">Green = Environment</span>, <span style="color:#fb923c">Orange = Social</span>, etc.</div></div>
        </div>
        <div class="guide-step">
          <div class="step-num-badge">3</div>
          <div><div class="step-content-title">Search by Location</div><div class="step-content-desc">Use the search box above the map. Type a state, city, or project name to filter pins.</div></div>
        </div>
        <div class="guide-step">
          <div class="step-num-badge">4</div>
          <div><div class="step-content-title">Click Pin to View Details</div><div class="step-content-desc">Click any pin to see project details. Click <strong>"View Details & Apply"</strong> to join.</div></div>
        </div>
      </div>
    </div>
    <div class="guide-section">
      <div class="guide-title">📋 2. How to Use the Projects Page</div>
      <div class="guide-steps">
        <div class="guide-step">
          <div class="step-num-badge">1</div>
          <div><div class="step-content-title">Open Projects List</div><div class="step-content-desc">Click <strong>"All Projects"</strong> in the sidebar. Open active projects are displayed as cards.</div></div>
        </div>
        <div class="guide-step">
          <div class="step-num-badge">2</div>
          <div><div class="step-content-title">Filter & Search Projects</div><div class="step-content-desc">Use the search bar to filter by name, state, or category.</div></div>
        </div>
        <div class="guide-step">
          <div class="step-num-badge">3</div>
          <div><div class="step-content-title">Check Details & Apply</div><div class="step-content-desc">Click <strong>"View & Apply"</strong> on a card, read full info, then click <strong>"Send Application"</strong>.</div></div>
        </div>
      </div>
    </div>
    <div class="guide-section">
      <div class="guide-title">📅 3. How to Manage Your Schedule</div>
      <div class="guide-steps">
        <div class="guide-step">
          <div class="step-num-badge">1</div>
          <div><div class="step-content-title">Open My Schedule</div><div class="step-content-desc">Click <strong>"My Schedule"</strong> in the sidebar to view all your upcoming projects.</div></div>
        </div>
        <div class="guide-step">
          <div class="step-num-badge">2</div>
          <div><div class="step-content-title">Check for Conflict Warnings ⚠️</div><div class="step-content-desc">The system flags overlapping projects with an <span style="color:var(--orange)">orange warning ⚠️ Schedule Conflict</span>.</div></div>
        </div>
        <div class="guide-step">
          <div class="step-num-badge">3</div>
          <div><div class="step-content-title">Leave a Project</div><div class="step-content-desc">Click <strong>"✗ Leave Project"</strong> if you can no longer attend.</div></div>
        </div>
      </div>
    </div>
    <div class="guide-section">
      <div class="guide-title">🏅 4. How to Use Project History</div>
      <div class="guide-steps">
        <div class="guide-step">
          <div class="step-num-badge">1</div>
          <div><div class="step-content-title">Open Project History</div><div class="step-content-desc">Click <strong>"Project History"</strong> to see past projects.</div></div>
        </div>
        <div class="guide-step">
          <div class="step-num-badge">2</div>
          <div><div class="step-content-title">Mark as Completed</div><div class="step-content-desc">Click <strong>"✅ Completed"</strong> to confirm your attendance.</div></div>
        </div>
        <div class="guide-step">
          <div class="step-num-badge">3</div>
          <div><div class="step-content-title">Leave a Rating & Comment</div><div class="step-content-desc">Provide feedback and a star rating for the NGO.</div></div>
        </div>
      </div>
    </div>
    <div class="guide-section">
      <div class="guide-title">❓ Frequently Asked Questions (FAQ)</div>
      <div id="faq">
        <?php foreach ([['How long does confirmation take?', 'Usually 1-3 business days.'],['Can I join multiple?', 'Yes, as long as times do not overlap.'],['What if canceled?', 'You will receive a notification and it will disappear from your schedule.']] as $i => $faq): ?>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(<?= $i ?>)"><?= $faq[0] ?><span class="faq-icon" id="faq-icon-<?= $i ?>">+</span></div>
          <div class="faq-a" id="faq-a-<?= $i ?>"><?= $faq[1] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>
<script src="../assets/js/main.js"></script>
<script>
function toggleFaq(i) {
  const a = document.getElementById('faq-a-' + i), icon = document.getElementById('faq-icon-' + i);
  const open = a.classList.toggle('open');
  icon.textContent = open ? '×' : '+'; icon.classList.toggle('open', open);
}
</script>
</body>
</html>
