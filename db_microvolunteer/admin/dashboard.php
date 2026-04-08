<?php
session_start();
require_once '../config/db.php';
requireLogin('admin');
$vol_count = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'volunteer'")->fetch_row()[0];
$ngo_count = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'ngo'")->fetch_row()[0];
$proj_count = $conn->query("SELECT COUNT(*) FROM projects WHERE status = 'active'")->fetch_row()[0];
$latest_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>
    .stat-card { background:var(--card); border:1px solid var(--border); padding:24px; border-radius:var(--radius); flex:1; min-width:200px; position:relative; overflow:hidden; }
    .stat-card::after { content:''; position:absolute; top:0; right:0; width:100px; height:100px; background:var(--blue); opacity:0.03; border-radius:50%; transform:translate(30%, -30%); }
    .stat-val { font-size:32px; font-weight:700; margin-bottom:4px; font-family:'Playfair Display',serif; }
    .stat-label { font-size:11px; letter-spacing:0.12em; text-transform:uppercase; color:var(--grey); }
    .quick-list { margin-top:20px; } .quick-item { display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid var(--border); } .quick-item:last-child { border-bottom:none; }
  </style>
</head>
<body>
<div class="dash-wrap">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><span style="width:12px;height:12px;background:var(--white);transform:rotate(45deg);display:inline-block"></span>&nbsp;MicroVolunteer</a>
    <div class="sidebar-user">
      <div class="avatar">⚙️</div>
      <div><div class="sidebar-user-name">Administrator</div><div class="sidebar-user-role">Super Control</div></div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-nav-title">System Console</div>
      <a href="dashboard.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>Dashboard</a>
      <a href="users.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.3"/><path d="M1 14c0-3 2.24-5 5-5s5 2 5 5" stroke="currentColor" stroke-width="1.3"/></svg>Manage Users</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header"><h1 class="page-title">Admin <span class="italic-blue">Dashboard</span></h1><p class="page-sub">Comprehensive overview of system.</p></div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:32px">
      <div class="stat-card"><div class="stat-val"><?= $vol_count ?></div><div class="stat-label">Registered Volunteers</div></div>
      <div class="stat-card"><div class="stat-val"><?= $ngo_count ?></div><div class="stat-label">Total NGOs</div></div>
      <div class="stat-card"><div class="stat-val"><?= $proj_count ?></div><div class="stat-label">Active Projects</div></div>
    </div>
    <div class="grid-2">
      <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px"><h3 style="font-size:16px">Latest Registrations</h3><a href="users.php" style="font-size:12px;color:var(--blue);text-decoration:none">View All →</a></div>
        <div class="quick-list">
          <?php while($u = $latest_users->fetch_assoc()): ?>
            <div class="quick-item"><div style="width:32px;height:32px;background:var(--blue-dim);color:var(--blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:12px"><?= strtoupper(substr($u['name'], 0, 1)) ?></div><div style="flex:1"><div style="font-size:14px;font-weight:500"><?= htmlspecialchars($u['name']) ?></div><div style="font-size:11px;color:var(--grey)"><?= htmlspecialchars($u['email']) ?></div></div><span class="badge badge-grey" style="font-size:9px"><?= strtoupper($u['role']) ?></span></div>
          <?php endwhile; ?>
        </div>
      </div>
      <div class="card" style="display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;background:var(--blue-dim);border-color:rgba(91,156,246,0.2)">
        <div style="font-size:40px;margin-bottom:16px">🔐</div><h3 style="margin-bottom:12px">System Security</h3><p style="font-size:13px;color:var(--grey-light);max-width:260px;margin:0 auto 20px">Always remember to logout after tasks.</p><a href="users.php" class="btn btn-blue">Enter User Console</a>
      </div>
    </div>
  </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>