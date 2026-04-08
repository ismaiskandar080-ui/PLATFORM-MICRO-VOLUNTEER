<?php
session_start();
require_once '../config/db.php';
requireLogin('volunteer');
$uid = $_SESSION['user_id'];
$name = $_SESSION['name'];
$total_join = $conn->query("SELECT COUNT(*) FROM applications WHERE volunteer_id=$uid AND status IN ('accepted','attended')")->fetch_row()[0];
$total_completed = $conn->query("SELECT COUNT(*) FROM applications WHERE volunteer_id=$uid AND is_completed=1")->fetch_row()[0];
$upcoming = $conn->query("SELECT COUNT(*) FROM applications p JOIN projects pr ON p.project_id=pr.id WHERE p.volunteer_id=$uid AND p.status='accepted' AND pr.date >= CURDATE()")->fetch_row()[0];
$projects_res = $conn->query("SELECT pr.*, u.name as ngo_name FROM projects pr JOIN users u ON pr.ngo_id=u.id WHERE pr.status='active' AND pr.date >= CURDATE() ORDER BY pr.created_at DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Volunteer Dashboard — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
</head>
<body>
<div class="dash-wrap">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><span class="diamond" style="width:12px;height:12px;background:var(--white);transform:rotate(45deg);display:inline-block"></span>&nbsp;MicroVolunteer</a>
    <div class="sidebar-user">
      <div class="avatar">🙋</div>
      <div>
        <div class="sidebar-user-name"><?= htmlspecialchars($name) ?></div>
        <div class="sidebar-user-role">Volunteer</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-nav-title">Menu</div>
      <a href="dashboard.php" class="sidebar-link active">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>Dashboard
      </a>
      <a href="map.php" class="sidebar-link">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1C5.24 1 3 3.24 3 6c0 4.25 5 9 5 9s5-4.75 5-9c0-2.76-2.24-5-5-5z" stroke="currentColor" stroke-width="1.3"/><circle cx="8" cy="6" r="1.8" stroke="currentColor" stroke-width="1.3"/></svg>Project Map
      </a>
      <a href="projects.php" class="sidebar-link">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>All Projects
      </a>
      <div class="sidebar-nav-title" style="margin-top:16px">My Account</div>
      <a href="schedule.php" class="sidebar-link">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 1v2M11 1v2M1 6h14" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>My Schedule
      </a>
      <a href="history.php" class="sidebar-link">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 4.5V8l2.5 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Project History
      </a>
      <a href="help_guide.php" class="sidebar-link">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help
      </a>
      <a href="about.php" class="sidebar-link">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us
      </a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Welcome, <span class="italic-blue"><?= htmlspecialchars(explode(' ',$name)[0]) ?></span> 👋</h1>
      <p class="page-sub">Here is a summary of your volunteer activity.</p>
    </div>
    <div class="stats-grid fade-up-1">
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-val"><?= $total_join ?></div>
        <div class="stat-lbl">Joined Projects</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div class="stat-val"><?= $total_completed ?></div>
        <div class="stat-lbl">Projects Completed</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-val"><?= $upcoming ?></div>
        <div class="stat-lbl">Upcoming</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⏱️</div>
        <div class="stat-val"><?= $total_completed * 3 ?>h</div>
        <div class="stat-lbl">Estimated Hours</div>
      </div>
    </div>
    <div style="display:flex;gap:12px;margin-bottom:36px;flex-wrap:wrap" class="fade-up-2">
      <a href="map.php" class="btn btn-blue">🗺️ &nbsp;Find Projects on Map</a>
      <a href="projects.php" class="btn btn-ghost">📋 &nbsp;All Projects</a>
      <a href="schedule.php" class="btn btn-ghost">📅 &nbsp;View My Schedule</a>
    </div>
    <div class="fade-up-3">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <h2 style="font-size:22px">Open Projects</h2>
        <a href="projects.php" class="btn btn-ghost btn-sm">View All →</a>
      </div>
      <?php if ($projects_res->num_rows === 0): ?>
      <div class="card" style="text-align:center;padding:56px">
        <div style="font-size:40px;margin-bottom:16px">📭</div>
        <p style="color:var(--grey)">No active projects at this time.</p>
      </div>
      <?php else: ?>
      <div class="grid-3">
        <?php
        $badge_map = ['education'=>'badge-blue','environment'=>'badge-green','social'=>'badge-orange','health'=>'badge-red','technology'=>'badge-purple','other'=>'badge-grey'];
        $kat_map   = ['education'=>'Education','environment'=>'Environment','social'=>'Social','health'=>'Health','technology'=>'Technology','other'=>'Others'];
        while ($p = $projects_res->fetch_assoc()):
          $badge = $badge_map[$p['category']] ?? 'badge-grey';
          $kat   = $kat_map[$p['category']] ?? 'Others';
        ?>
        <div class="task-card card-hover">
          <div class="task-top">
            <span class="badge <?= $badge ?>"><?= $kat ?></span>
            <?php if (strtotime($p['date']) <= strtotime('+3 days')): ?>
            <span style="font-size:11px;color:var(--orange)">● Urgent</span>
            <?php endif; ?>
          </div>
          <div class="task-title"><?= htmlspecialchars($p['project_name']) ?></div>
          <div class="task-ngo"><?= htmlspecialchars($p['ngo_name']) ?></div>
          <div class="task-desc"><?= htmlspecialchars(substr($p['description'],0,120)) ?>...</div>
          <div class="task-meta">
            <span>📅 <?= date('d M Y', strtotime($p['date'])) ?></span>
            <span>📍 <?= htmlspecialchars($p['city']) ?></span>
            <span>👥 <?= $p['quota'] ?> slots</span>
          </div>
          <a href="project_details.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm" style="margin-top:16px;width:100%;justify-content:center">View Details</a>
        </div>
        <?php endwhile; ?>
      </div>
      <?php endif; ?>
    </div>
  </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
