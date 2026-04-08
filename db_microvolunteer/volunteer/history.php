<?php
session_start();
require_once '../config/db.php';
requireLogin('volunteer');
$uid = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settle_id'])) {
    $sid    = (int)$_POST['settle_id'];
    $komen  = clean($_POST['komen'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $rating = max(1, min(5, $rating));
    $stmt = $conn->prepare("UPDATE applications SET is_completed=1, volunteer_comment=?, volunteer_rating=? WHERE id=? AND volunteer_id=?");
    $stmt->bind_param('siii', $komen, $rating, $sid, $uid);
    $stmt->execute();
    header("Location: history.php?settled=1"); exit;
}
$sejarah = $conn->query("
    SELECT pm.id as pm_id, pm.status as pm_status, pm.is_completed, pm.volunteer_comment, pm.volunteer_rating,
           pr.id as project_id, pr.project_name, pr.date, pr.start_time, pr.end_time,
           pr.location, pr.city, pr.state, pr.category,
           u.name as ngo_name
    FROM applications pm
    JOIN projects pr ON pm.project_id=pr.id
    JOIN users u ON pr.ngo_id=u.id
    WHERE pm.volunteer_id=$uid
      AND (pr.date < CURDATE() OR pr.status IN ('completed','cancelled'))
      AND pm.status IN ('accepted','attended','pending')
    ORDER BY pr.date DESC
");
$badge_map = ['education'=>'badge-blue','environment'=>'badge-green','social'=>'badge-orange','health'=>'badge-red','technology'=>'badge-purple','other'=>'badge-grey'];
$kat_map   = ['education'=>'Education','environment'=>'Environment','social'=>'Social','health'=>'Health','technology'=>'Technology','other'=>'Others'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Project History — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>
    .sejarah-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:24px; margin-bottom:16px; transition:border-color .25s; }
    .sejarah-card:hover { border-color:rgba(255,255,255,.15); }
    .settle-form { margin-top:20px; padding-top:20px; border-top:1px solid var(--border); }
    .star-rating { display:flex; gap:6px; margin:10px 0; }
    .star-rating input { display:none; }
    .star-rating label { font-size:24px; cursor:pointer; color:#333; transition:color .15s; }
    .star-rating { flex-direction:row-reverse; justify-content:flex-start; }
    .star-rating label:hover, .star-rating label:hover ~ label, .star-rating input:checked ~ label { color:#fb923c; }
    .komen-display { background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:10px; padding:14px 16px; font-size:13px; color:var(--grey-light); line-height:1.65; margin-top:10px; }
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
      <a href="history.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 4.5V8l2.5 2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Project History</a>
      <a href="help_guide.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help</a>
      <a href="about.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Project <span class="italic-blue">History</span></h1>
      <p class="page-sub">Record of campaigns and projects you have joined before.</p>
    </div>
    <?php if (isset($_GET['settled'])): ?>
    <div class="alert alert-success">⭐ Thank you! Your feedback has been recorded.</div>
    <?php endif; ?>
    <?php
    $rows = [];
    while ($r = $sejarah->fetch_assoc()) $rows[] = $r;
    ?>
    <?php if (empty($rows)): ?>
    <div class="card" style="text-align:center;padding:80px 40px">
      <div style="font-size:56px;margin-bottom:24px">🏅</div>
      <h2 style="font-size:26px;margin-bottom:12px">No Project History Yet</h2>
      <p style="color:var(--grey);margin-bottom:32px;max-width:400px;margin-left:auto;margin-right:auto;line-height:1.7">You haven't joined any projects yet. Start your volunteer journey today!</p>
      <a href="projects.php" class="btn btn-blue" style="padding:16px 40px">📋 Search Projects Now</a>
    </div>
    <?php else: ?>
    <?php foreach ($rows as $r):
      $b = $badge_map[$r['category']] ?? 'badge-grey';
      $k = $kat_map[$r['category']] ?? 'Others';
      $settled = (bool)$r['is_completed'];
    ?>
    <div class="sejarah-card">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div style="flex:1">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;flex-wrap:wrap">
            <span class="badge <?= $b ?>"><?= $k ?></span>
            <?php if ($settled): ?><span class="badge badge-green">✓ Completed</span>
            <?php else: ?><span class="badge badge-orange">Not Yet Confirmed Completed</span><?php endif; ?>
          </div>
          <div style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;margin-bottom:6px"><?= htmlspecialchars($r['project_name']) ?></div>
          <div style="font-size:13px;color:var(--grey);margin-bottom:10px">🏢 <?= htmlspecialchars($r['ngo_name']) ?></div>
          <div style="display:flex;gap:20px;font-size:13px;color:var(--grey-light);flex-wrap:wrap">
            <span>📅 <?= date('d M Y', strtotime($r['date'])) ?></span>
            <span>⏰ <?= date('H:i', strtotime($r['start_time'])) ?> – <?= date('H:i', strtotime($r['end_time'])) ?></span>
            <span>📍 <?= htmlspecialchars($r['city']) ?>, <?= htmlspecialchars($r['state']) ?></span>
          </div>
        </div>
      </div>
      <?php if ($settled && $r['volunteer_comment']): ?>
      <div class="settle-form">
        <div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:var(--grey);margin-bottom:8px">Your Feedback</div>
        <?php if ($r['volunteer_rating']): ?>
        <div style="margin-bottom:8px;font-size:20px"><?= str_repeat('⭐', $r['volunteer_rating']) ?><?= str_repeat('☆', 5 - $r['volunteer_rating']) ?></div>
        <?php endif; ?>
        <div class="komen-display"><?= htmlspecialchars($r['volunteer_comment']) ?></div>
      </div>
      <?php elseif (!$settled): ?>
      <div class="settle-form">
        <div style="font-size:13px;color:var(--grey-light);margin-bottom:16px">Have you joined this project? Mark as completed and leave feedback for the NGO.</div>
        <form method="POST" id="form-settle-<?= $r['pm_id'] ?>">
          <input type="hidden" name="settle_id" value="<?= $r['pm_id'] ?>"/>
          <div class="form-group">
            <label class="form-label">Experience Rating</label>
            <div class="star-rating">
              <input type="radio" name="rating" id="s5-<?= $r['pm_id'] ?>" value="5"/><label for="s5-<?= $r['pm_id'] ?>">★</label>
              <input type="radio" name="rating" id="s4-<?= $r['pm_id'] ?>" value="4"/><label for="s4-<?= $r['pm_id'] ?>">★</label>
              <input type="radio" name="rating" id="s3-<?= $r['pm_id'] ?>" value="3"/><label for="s3-<?= $r['pm_id'] ?>">★</label>
              <input type="radio" name="rating" id="s2-<?= $r['pm_id'] ?>" value="2"/><label for="s2-<?= $r['pm_id'] ?>">★</label>
              <input type="radio" name="rating" id="s1-<?= $r['pm_id'] ?>" value="1"/><label for="s1-<?= $r['pm_id'] ?>">★</label>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Comment / Suggestion for NGO (Optional)</label>
            <textarea name="komen" class="form-control" rows="3" placeholder="e.g.: Project was very organized and meaningful."></textarea>
          </div>
          <button type="submit" class="btn btn-blue" onclick="this.form.komen.value = this.form.komen.value || 'No comments.'">✅ Completed — Send Feedback</button>
        </form>
      </div>
      <?php else: ?>
      <div class="settle-form"><span class="badge badge-green" style="padding:8px 16px">✓ Project Marked as Completed</span></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
