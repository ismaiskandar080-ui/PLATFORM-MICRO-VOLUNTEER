<?php
session_start();
require_once '../config/db.php';
requireLogin('volunteer');
$uid = $_SESSION['user_id'];
$cari   = isset($_GET['cari'])   ? trim($_GET['cari'])   : '';
$negeri = isset($_GET['negeri']) ? trim($_GET['negeri']) : '';
$kat    = isset($_GET['kat'])    ? trim($_GET['kat'])    : '';
$where = "pr.status='active' AND pr.date >= CURDATE()";
$params = [];
$types  = '';
if ($cari)   { $where .= " AND (pr.project_name LIKE ? OR pr.description LIKE ? OR pr.city LIKE ?)"; $like = "%{$cari}%"; $params[] = &$like; $params[] = &$like; $params[] = &$like; $types .= 'sss'; }
if ($negeri) { $where .= " AND pr.state = ?"; $params[] = &$negeri; $types .= 's'; }
if ($kat)    { $where .= " AND pr.category = ?"; $params[] = &$kat; $types .= 's'; }
$sql = "SELECT pr.*, u.name as ngo_name,
        (SELECT COUNT(*) FROM applications p WHERE p.project_id=pr.id AND p.status IN ('accepted','attended')) as vol_count,
        (SELECT COUNT(*) FROM applications p WHERE p.project_id=pr.id AND p.volunteer_id=$uid) as already_applied
        FROM projects pr
        JOIN users u ON pr.ngo_id=u.id
        WHERE $where
        ORDER BY pr.date ASC, pr.created_at DESC";
if ($types) {
    $stmt = $conn->prepare($sql);
    array_unshift($params, $types);
    call_user_func_array([$stmt, 'bind_param'], $params);
    $stmt->execute();
    $projects_res = $stmt->get_result();
} else {
    $projects_res = $conn->query($sql);
}
$badge_map = ['education'=>'badge-blue','environment'=>'badge-green','social'=>'badge-orange','health'=>'badge-red','technology'=>'badge-purple','other'=>'badge-grey'];
$kat_map   = ['education'=>'Education','environment'=>'Environment','social'=>'Social','health'=>'Health','technology'=>'Technology','other'=>'Others'];
$negeri_list = ['Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Perak','Perlis','Pulau Pinang','Sabah','Sarawak','Selangor','Terengganu','W.P. Kuala Lumpur','W.P. Labuan','W.P. Putrajaya'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>All Projects — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>
    .filter-bar { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:28px; }
    .filter-bar input, .filter-bar select { flex:1; min-width:160px; }
    .projek-count { font-size:13px; color:var(--grey); margin-bottom:20px; }
    .empty-state { text-align:center; padding:80px 40px; }
    .mohon-badge { font-size:11px; color:var(--green); background:rgba(74,222,128,0.12); border:1px solid rgba(74,222,128,0.2); padding:4px 12px; border-radius:100px; }
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
      <a href="projects.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>All Projects</a>
      <div class="sidebar-nav-title" style="margin-top:16px">My Account</div>
      <a href="schedule.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 1v2M11 1v2M1 6h14" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>My Schedule</a>
      <a href="history.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 4.5V8l2.5 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Project History</a>
      <a href="help_guide.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help</a>
      <a href="about.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">All <span class="italic-blue">Projects</span></h1>
      <p class="page-sub">Search and apply for volunteer projects that suit you.</p>
    </div>
    <form method="GET" class="filter-bar">
      <input type="text" name="cari" class="form-control" placeholder="🔍  Search project name, city..." value="<?= htmlspecialchars($cari) ?>"/>
      <select name="negeri" class="form-control" style="max-width:200px">
        <option value="">All States</option>
        <?php foreach ($negeri_list as $n): ?>
        <option value="<?= $n ?>" <?= $negeri === $n ? 'selected' : '' ?>><?= $n ?></option>
        <?php endforeach; ?>
      </select>
      <select name="kat" class="form-control" style="max-width:180px">
        <option value="">All Categories</option>
        <?php foreach ($kat_map as $k => $v): ?>
        <option value="<?= $k ?>" <?= $kat === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-blue" style="flex-shrink:0">Search</button>
      <?php if ($cari || $negeri || $kat): ?><a href="projects.php" class="btn btn-ghost" style="flex-shrink:0">Reset</a><?php endif; ?>
    </form>
    <?php
    $rows = [];
    while ($p = $projects_res->fetch_assoc()) $rows[] = $p;
    ?>
    <div class="projek-count"><?= count($rows) ?> projects found</div>
    <?php if (empty($rows)): ?>
    <div class="card empty-state">
      <div style="font-size:48px;margin-bottom:20px">🔎</div>
      <h3 style="font-size:22px;margin-bottom:12px">No projects found</h3>
      <p style="color:var(--grey);margin-bottom:24px">Try changing your search filters or check back later.</p>
      <a href="projects.php" class="btn btn-blue">View All Projects</a>
    </div>
    <?php else: ?>
    <div class="grid-3">
      <?php foreach ($rows as $p):
        $b = $badge_map[$p['category']] ?? 'badge-grey';
        $k = $kat_map[$p['category']] ?? 'Others';
        $penuh = $p['vol_count'] >= $p['quota'];
        $sisa  = $p['quota'] - $p['vol_count'];
      ?>
      <div class="task-card card-hover fade-up-1">
        <div class="task-top">
          <span class="badge <?= $b ?>"><?= $k ?></span>
          <div style="display:flex;gap:6px;align-items:center">
            <?php if ($p['already_applied']): ?><span class="mohon-badge">✓ Applied</span><?php endif; ?>
            <?php if (strtotime($p['date']) <= strtotime('+3 days')): ?><span style="font-size:11px;color:var(--orange)">● Urgent</span><?php endif; ?>
            <?php if ($penuh): ?><span style="font-size:11px;color:var(--red)">● Full</span><?php endif; ?>
          </div>
        </div>
        <div class="task-title"><?= htmlspecialchars($p['project_name']) ?></div>
        <div class="task-ngo">🏢 <?= htmlspecialchars($p['ngo_name']) ?></div>
        <div class="task-desc"><?= htmlspecialchars(substr($p['description'], 0, 120)) ?>...</div>
        <div class="task-meta">
          <span>📅 <?= date('d M Y', strtotime($p['date'])) ?></span>
          <span>⏰ <?= date('H:i', strtotime($p['start_time'])) ?> – <?= date('H:i', strtotime($p['end_time'])) ?></span>
          <span>📍 <?= htmlspecialchars($p['city']) ?>, <?= htmlspecialchars($p['state']) ?></span>
          <span>👥 <?= $sisa < 0 ? 0 : $sisa ?>/<?= $p['quota'] ?> slots</span>
        </div>
        <a href="project_details.php?id=<?= $p['id'] ?>" class="btn <?= $penuh ? 'btn-ghost' : 'btn-blue' ?> btn-sm" style="margin-top:16px;width:100%;justify-content:center">
          <?= $p['already_applied'] ? '✓ View Application' : ($penuh ? 'Project Full' : 'View & Apply →') ?>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
