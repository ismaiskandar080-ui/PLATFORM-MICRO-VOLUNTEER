<?php
session_start();
require_once '../config/db.php';
requireLogin('ngo');
$uid  = $_SESSION['user_id'];
$name = $_SESSION['name'];
$total_projects  = $conn->query("SELECT COUNT(*) FROM projects WHERE ngo_id=$uid")->fetch_row()[0];
$projects_active = $conn->query("SELECT COUNT(*) FROM projects WHERE ngo_id=$uid AND status='active'")->fetch_row()[0];
$total_vol      = $conn->query("SELECT COUNT(*) FROM applications p JOIN projects pr ON p.project_id=pr.id WHERE pr.ngo_id=$uid AND p.status IN ('accepted','attended')")->fetch_row()[0];
$total_completed = $conn->query("SELECT COUNT(*) FROM projects WHERE ngo_id=$uid AND status='completed'")->fetch_row()[0];
$projects_res = $conn->query("SELECT pr.*, (SELECT COUNT(*) FROM applications p WHERE p.project_id=pr.id AND p.status IN ('accepted','attended')) as vol_count FROM projects pr WHERE pr.ngo_id=$uid ORDER BY pr.created_at DESC LIMIT 5");
$applications_res = $conn->query("SELECT p.*, u.name as volunteer_name, pr.project_name FROM applications p JOIN users u ON p.volunteer_id=u.id JOIN projects pr ON p.project_id=pr.id WHERE pr.ngo_id=$uid AND p.status='pending' ORDER BY p.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NGO Dashboard — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
</head>
<body>
<div class="dash-wrap">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><span style="width:12px;height:12px;background:var(--white);transform:rotate(45deg);display:inline-block"></span>&nbsp;MicroVolunteer</a>
    <div class="sidebar-user">
      <div class="avatar">🏢</div>
      <div><div class="sidebar-user-name"><?= htmlspecialchars($name) ?></div><div class="sidebar-user-role">NGO Organization</div></div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-nav-title">Menu</div>
      <a href="dashboard.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>Dashboard</a>
      <a href="project_add.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 5v6M5 8h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Add Project</a>
      <a href="project_manage.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Manage Projects</a>
      <div class="sidebar-nav-title" style="margin-top:16px">Volunteers</div>
      <a href="volunteer_list.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.3"/><path d="M1 14c0-3 2.24-5 5-5s5 2 5 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/><path d="M12 7l1.5 1.5L16 6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Volunteer List</a>
      <div class="sidebar-nav-title" style="margin-top:16px">Information</div>
      <a href="help_guide.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help</a>
      <a href="about.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">NGO <span class="italic-blue">Dashboard</span></h1>
      <p class="page-sub">Welcome back, <?= htmlspecialchars($name) ?>. Here is a summary of your organization.</p>
    </div>
    <div class="stats-grid fade-up-1">
      <div class="stat-card"><div class="stat-icon">📋</div><div class="stat-val"><?= $total_projects ?></div><div class="stat-lbl">Total Projects</div></div>
      <div class="stat-card"><div class="stat-icon">🟢</div><div class="stat-val"><?= $projects_active ?></div><div class="stat-lbl">Active Projects</div></div>
      <div class="stat-card"><div class="stat-icon">🙋</div><div class="stat-val"><?= $total_vol ?></div><div class="stat-lbl">Accepted Volunteers</div></div>
      <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-val"><?= $total_completed ?></div><div class="stat-lbl">Completed Projects</div></div>
    </div>
    <div style="display:flex;gap:12px;margin-bottom:36px;flex-wrap:wrap" class="fade-up-2">
      <a href="project_add.php" class="btn btn-blue">＋ &nbsp;Add New Project</a>
      <a href="project_manage.php" class="btn btn-ghost">📋 &nbsp;Manage Projects</a>
      <a href="volunteer_list.php" class="btn btn-ghost">👥 &nbsp;View Volunteers</a>
    </div>
    <div class="grid-2 fade-up-3">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px"><h3 style="font-size:18px">Recent Projects</h3><a href="project_manage.php" class="btn btn-ghost btn-sm">All →</a></div>
        <div style="display:flex;flex-direction:column;gap:12px">
          <?php if ($projects_res->num_rows === 0): ?>
          <div class="card" style="text-align:center;padding:36px"><div style="font-size:32px;margin-bottom:12px">📭</div><p style="color:var(--grey);font-size:13px">No projects yet. <a href="project_add.php" style="color:var(--blue)">Add now →</a></p></div>
          <?php else: ?>
          <?php while ($p = $projects_res->fetch_assoc()):
            $sb = ['active'=>'badge-green','full'=>'badge-orange','completed'=>'badge-grey','cancelled'=>'badge-red'][$p['status']]??'badge-grey';
            $sl = ['active'=>'Active','full'=>'Full','completed'=>'Completed','cancelled'=>'Cancelled'][$p['status']]??ucfirst($p['status']);
          ?>
          <div class="card" style="padding:20px">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
              <div style="flex:1"><div style="font-size:15px;font-weight:500;margin-bottom:4px"><?= htmlspecialchars($p['project_name']) ?></div><div style="font-size:12px;color:var(--grey)">📅 <?= date('d M Y', strtotime($p['date'])) ?> · 👥 <?= $p['vol_count'] ?>/<?= $p['quota'] ?> volunteers</div></div>
              <span class="badge <?= $sb ?>"><?= $sl ?></span>
            </div>
          </div>
          <?php endwhile; ?>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px"><h3 style="font-size:18px">New Applications</h3><a href="volunteer_list.php" class="btn btn-ghost btn-sm">All →</a></div>
        <div style="display:flex;flex-direction:column;gap:12px">
          <?php if ($applications_res->num_rows === 0): ?>
          <div class="card" style="text-align:center;padding:36px"><div style="font-size:32px;margin-bottom:12px">🎉</div><p style="color:var(--grey);font-size:13px">No new applications at the moment.</p></div>
          <?php else: ?>
          <?php while ($pm = $applications_res->fetch_assoc()): ?>
          <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
              <div><div style="font-size:14px;font-weight:500"><?= htmlspecialchars($pm['volunteer_name']) ?></div><div style="font-size:12px;color:var(--grey);margin-top:3px"><?= htmlspecialchars($pm['project_name']) ?></div></div>
              <div style="display:flex;gap:8px">
                <form method="POST" action="process_application.php"><input type="hidden" name="id" value="<?= $pm['id'] ?>"/><input type="hidden" name="action" value="accept"/><button class="btn btn-ghost btn-sm" style="color:var(--green);border-color:rgba(74,222,128,0.3)">✓ Accept</button></form>
                <form method="POST" action="process_application.php"><input type="hidden" name="id" value="<?= $pm['id'] ?>"/><input type="hidden" name="action" value="reject"/><button class="btn btn-danger btn-sm">✗ Reject</button></form>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
