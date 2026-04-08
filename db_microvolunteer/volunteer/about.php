<?php session_start(); require_once '../config/db.php'; requireLogin('volunteer'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About Us — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>
    .about-hero { background:linear-gradient(135deg,rgba(91,156,246,.12) 0%,rgba(74,222,128,.06) 100%); border:1px solid var(--border); border-radius:20px; padding:56px 48px; text-align:center; margin-bottom:40px; position:relative; overflow:hidden; }
    .about-hero::before { content:''; position:absolute; top:-80px; right:-80px; width:300px; height:300px; border-radius:50%; background:radial-gradient(circle,rgba(91,156,246,.08),transparent 70%); pointer-events:none; }
    .about-subtitle { font-family:'Cormorant Garamond',serif; font-style:italic; font-size:22px; color:var(--blue); margin-bottom:16px; }
    .about-title { font-family:'Playfair Display',serif; font-size:clamp(28px,4vw,48px); font-weight:900; margin-bottom:20px; line-height:1.15; }
    .about-desc { font-size:15px; color:var(--grey-light); max-width:680px; margin:0 auto; line-height:1.8; }
    .stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:40px; }
    .stat-big { text-align:center; background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:32px 20px; }
    .stat-big-num { font-family:'Playfair Display',serif; font-size:48px; font-weight:900; color:var(--blue); }
    .stat-big-lbl { font-size:11px; letter-spacing:.16em; text-transform:uppercase; color:var(--grey); margin-top:8px; }
    .mission-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:20px; margin-bottom:40px; }
    .mission-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:28px; }
    .mission-icon { font-size:32px; margin-bottom:16px; }
    .mission-title { font-family:'Playfair Display',serif; font-size:18px; font-weight:700; margin-bottom:10px; }
    .mission-desc { font-size:13px; color:var(--grey-light); line-height:1.75; }
    .values-list { display:flex; flex-direction:column; gap:12px; }
    .value-item { display:flex; gap:16px; align-items:flex-start; padding:18px 22px; background:var(--card); border:1px solid var(--border); border-radius:var(--radius); }
    .value-badge { width:40px; height:40px; border-radius:10px; background:var(--blue-dim); border:1px solid rgba(91,156,246,.2); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    .value-title { font-size:15px; font-weight:500; margin-bottom:4px; }
    .value-desc { font-size:13px; color:var(--grey-light); line-height:1.65; }
    .contact-box { background:var(--card); border:1px solid var(--border); border-radius:20px; padding:36px; margin-top:40px; text-align:center; }
    @media(max-width:768px){ .mission-grid,.stats-row{grid-template-columns:1fr} }
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
      <a href="help_guide.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help</a>
      <a href="about.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>

  <main class="main-content">
    <!-- Hero -->
    <div class="about-hero">
      <div class="about-subtitle">together we are stronger</div>
      <h1 class="about-title">About <span class="italic-blue">MicroVolunteer</span></h1>
      <p class="about-desc">
        MicroVolunteer is a digital platform that connects sincere volunteers with Non-Governmental Organizations (NGOs) 
        throughout Malaysia. Our mission is to simplify the search, registration, and management of volunteering projects 
        to be more transparent, efficient, and meaningful for all parties.
      </p>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-big"><div class="stat-big-num">2,400+</div><div class="stat-big-lbl">Registered Volunteers</div></div>
      <div class="stat-big"><div class="stat-big-num">180+</div><div class="stat-big-lbl">Active NGOs</div></div>
      <div class="stat-big"><div class="stat-big-num">9,600+</div><div class="stat-big-lbl">Volunteer Hours Completed</div></div>
    </div>

    <!-- Misi & Visi -->
    <div class="mission-grid">
      <div class="mission-card">
        <div class="mission-icon">🎯</div>
        <div class="mission-title">Our Mission</div>
        <div class="mission-desc">Simplify for Malaysian volunteers to find and join meaningful community projects, while helping NGOs manage their volunteers more systematically and efficiently through technology.</div>
      </div>
      <div class="mission-card">
        <div class="mission-icon">🌟</div>
        <div class="mission-title">Our Vision</div>
        <div class="mission-desc">To be the leading volunteering platform in Malaysia that connects every layer of society — students, professionals, retirees — with opportunities to make a positive impact on the community and the environment.</div>
      </div>
      <div class="mission-card">
        <div class="mission-icon">💡</div>
        <div class="mission-title">Why MicroVolunteer?</div>
        <div class="mission-desc">The concept of "micro-volunteering" allows you to contribute even with limited time. No long-term commitment — pick a project, join, and complete. Every hour you dedicate makes a real impact.</div>
      </div>
      <div class="mission-card">
        <div class="mission-icon">🤝</div>
        <div class="mission-title">Who Are We?</div>
        <div class="mission-desc">MicroVolunteer was developed as a final year project for the Diploma in Computer Science at Universiti Poly-Tech Malaysia (UPTM) by <strong>Ismadaris Qaiyum</strong>, with the aim of solving volunteering coordination challenges in Malaysia.</div>
      </div>
    </div>

    <!-- Nilai-nilai -->
    <div class="page-header" style="margin-top:40px">
      <h2 style="font-size:26px">Platform <span class="italic-blue">Values</span></h2>
    </div>
    <div class="values-list">
      <div class="value-item">
        <div class="value-badge">🔓</div>
        <div><div class="value-title">Transparent & Open</div><div class="value-desc">All project information is fully disclosed to volunteers before they apply — no hidden surprises. NGOs are responsible for ensuring accurate and up-to-date information.</div></div>
      </div>
      <div class="value-item">
        <div class="value-badge">⚡</div>
        <div><div class="value-title">Fast & Easy</div><div class="value-desc">The registration and application process is designed to be completed in less than two minutes. A simple interface ensures users of all ages can use it comfortably.</div></div>
      </div>
      <div class="value-item">
        <div class="value-badge">🛡️</div>
        <div><div class="value-title">Safe & Trusted</div><div class="value-desc">All registered NGOs are subject to admin review before their projects are published. User personal data is controlled with password encryption and strict access.</div></div>
      </div>
      <div class="value-item">
        <div class="value-badge">🗓️</div>
        <div><div class="value-title">Smart Schedule Management</div><div class="value-desc">An automatic schedule conflict detection system ensures volunteers do not register for two overlapping projects at the same time — facilitating time management and personal commitment.</div></div>
      </div>
      <div class="value-item">
        <div class="value-badge">📊</div>
        <div><div class="value-title">Measurable Impact</div><div class="value-desc">Every volunteering activity is recorded in your profile as project history. Feedback and ratings provided help NGOs improve the quality of their programs over time.</div></div>
      </div>
    </div>

    <!-- Hubungi -->
    <div class="contact-box">
      <div style="font-size:36px;margin-bottom:16px">📬</div>
      <h3 style="font-size:22px;margin-bottom:10px">Have Questions or Suggestions?</h3>
      <p style="font-size:13px;color:var(--grey-light);margin-bottom:24px;line-height:1.7">
        We always want to hear your feedback to improve this platform.<br>
        Contact us via email below or use the How to Help page for further guidance.
      </p>
      <a href="mailto:admin@microvolunteer.com" class="btn btn-blue" style="padding:14px 36px">✉️ &nbsp;admin@microvolunteer.com</a>
      <div style="margin-top:20px;font-size:12px;color:var(--grey)">
        © 2026 MicroVolunteer · Universiti Poly-Tech Malaysia · Ismadaris Qaiyum · Diploma in Computer Science
      </div>
    </div>
  </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
