<?php
session_start();
require_once '../config/db.php';
requireLogin('volunteer');
$uid = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $rid = (int)$_POST['remove_id'];
    $stmt = $conn->prepare("DELETE FROM applications WHERE id=? AND volunteer_id=?");
    $stmt->bind_param("ii", $rid, $uid);
    $stmt->execute();
    header("Location: schedule.php?msg=removed"); exit();
}
$jadual = $conn->query("
    SELECT pm.id as pm_id, pm.status as pm_status, pr.id as project_id,
           pr.project_name, pr.date, pr.start_time, pr.end_time,
           pr.location, pr.city, pr.state, pr.contact_phone,
           u.name as ngo_name
    FROM applications pm
    JOIN projects pr ON pm.project_id = pr.id
    JOIN users u ON pr.ngo_id = u.id
    WHERE pm.volunteer_id = $uid
      AND pm.status IN ('pending','accepted')
      AND pr.date >= CURDATE()
    ORDER BY pr.date ASC, pr.start_time ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Schedule — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
</head>
<body>
<div class="dash-wrap">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><span style="width:12px;height:12px;background:var(--white);transform:rotate(45deg);display:inline-block"></span>&nbsp;MicroVolunteer</a>
    <div class="sidebar-user">
      <div class="avatar">🙋</div>
      <div>
        <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['name']) ?></div>
        <div class="sidebar-user-role">Volunteer</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-nav-title">Menu</div>
      <a href="dashboard.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>Dashboard</a>
      <a href="map.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1C5.24 1 3 3.24 3 6c0 4.25 5 9 5 9s5-4.75 5-9c0-2.76-2.24-5-5-5z" stroke="currentColor" stroke-width="1.3"/><circle cx="8" cy="6" r="1.8" stroke="currentColor" stroke-width="1.3"/></svg>Project Map</a>
      <a href="projects.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>All Projects</a>
      <div class="sidebar-nav-title" style="margin-top:16px">My Account</div>
      <a href="schedule.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="13" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 1v2M11 1v2M1 6h14" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>My Schedule</a>
      <a href="history.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 4.5V8l2.5 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Project History</a>
      <a href="help_guide.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help</a>
      <a href="about.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">My <span class="italic-blue">Schedule</span></h1>
      <p class="page-sub">Projects you will join. Manage and check schedule conflicts here.</p>
    </div>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'removed'): ?>
    <div class="alert alert-success alert-auto">✅ You have successfully left the project.</div>
    <?php endif; ?>
    <div class="alert alert-info" style="margin-bottom:28px"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink:0"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V7.5M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>The system will show a <strong>⚠️ Schedule Conflict</strong> warning if two of your projects overlap on the same date and time.</div>
    <?php
    $rows = [];
    while ($r = $jadual->fetch_assoc()) $rows[] = $r;
    $konflik_ids = [];
    for ($i = 0; $i < count($rows); $i++) {
        for ($j = $i+1; $j < count($rows); $j++) {
            $a = $rows[$i]; $b = $rows[$j];
            if ($a['date'] !== $b['date']) continue;
            $aM = strtotime($a['date'].' '.$a['start_time']);
            $aT = strtotime($a['date'].' '.$a['end_time']);
            $bM = strtotime($b['date'].' '.$b['start_time']);
            $bT = strtotime($b['date'].' '.$b['end_time']);
            if ($aM < $bT && $aT > $bM) {
                $konflik_ids[] = $a['pm_id'];
                $konflik_ids[] = $b['pm_id'];
            }
        }
    }
    $konflik_ids = array_unique($konflik_ids);
    ?>
    <?php if (empty($rows)): ?>
    <div class="card" style="text-align:center;padding:72px 40px">
      <div style="font-size:48px;margin-bottom:20px">📅</div>
      <h3 style="font-size:20px;margin-bottom:12px">No Projects in Schedule</h3>
      <p style="color:var(--grey);margin-bottom:28px">You haven't joined any projects yet. Let's find a suitable project!</p>
      <a href="projects.php" class="btn btn-blue">📋 Search Projects</a>
    </div>
    <?php else: ?>
    <?php
    $grouped = [];
    foreach ($rows as $r) { $grouped[$r['date']][] = $r; }
    foreach ($grouped as $date => $items):
    ?>
    <div style="margin-bottom:32px">
      <div style="font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:var(--grey);margin-bottom:14px;display:flex;align-items:center;gap:12px">
        <span><?= date('l, d F Y', strtotime($date)) ?></span>
        <?php if (count($items) > 1 && array_intersect(array_column($items,'pm_id'), $konflik_ids)): ?>
        <span style="background:rgba(251,146,60,0.15);color:var(--orange);border:1px solid rgba(251,146,60,0.3);font-size:10px;padding:3px 10px;border-radius:100px">⚠️ Schedule Conflict</span>
        <?php endif; ?>
      </div>
      <?php foreach ($items as $item):
        $is_konflik = in_array($item['pm_id'], $konflik_ids);
        $status_col = $item['pm_status'] === 'accepted' ? 'badge-green' : 'badge-orange';
        $status_lbl = $item['pm_status'] === 'accepted' ? 'Accepted' : 'Pending';
      ?>
      <div class="card" style="margin-bottom:12px;border-color:<?= $is_konflik ? 'rgba(251,146,60,0.4)' : 'var(--border)' ?>">
        <?php if ($is_konflik): ?>
        <div class="konflik-popup show" style="margin-bottom:16px">
          <span class="konflik-icon">⚠️</span>
          <div>
            <div class="konflik-title">Schedule Conflict Detected!</div>
            <div class="konflik-msg">This project overlaps in time with another project on the same day. Please remove one to avoid issues.</div>
          </div>
        </div>
        <?php endif; ?>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
          <div style="flex:1">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
              <span style="font-size:16px;font-weight:500"><?= htmlspecialchars($item['project_name']) ?></span>
              <span class="badge <?= $status_col ?>"><?= $status_lbl ?></span>
            </div>
            <div style="font-size:13px;color:var(--grey-light);margin-bottom:4px">🏢 <?= htmlspecialchars($item['ngo_name']) ?></div>
            <div style="display:flex;gap:20px;font-size:13px;color:var(--grey);flex-wrap:wrap;margin-top:8px">
              <span>⏰ <?= date('H:i', strtotime($item['start_time'])) ?> – <?= date('H:i', strtotime($item['end_time'])) ?></span>
              <span>📍 <?= htmlspecialchars($item['location']) ?></span>
              <?php if ($item['contact_phone']): ?><span>📞 <?= htmlspecialchars($item['contact_phone']) ?></span><?php endif; ?>
            </div>
          </div>
          <form method="POST" onsubmit="return confirm('Are you sure you want to leave this project?')">
            <input type="hidden" name="remove_id" value="<?= $item['pm_id'] ?>"/>
            <button type="submit" class="btn btn-danger btn-sm">✗ Leave Project</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
