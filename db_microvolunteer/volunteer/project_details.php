<?php
session_start();
require_once '../config/db.php';
requireLogin('volunteer');
$uid = $_SESSION['user_id'];
$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$pid) { header('Location: projects.php'); exit; }
$stmt = $conn->prepare("SELECT pr.*, u.name as ngo_name, '' as ngo_tel,
    (SELECT COUNT(*) FROM applications p WHERE p.project_id=pr.id AND p.status IN ('accepted','attended')) as vol_count,
    (SELECT id FROM applications p WHERE p.project_id=pr.id AND p.volunteer_id=?) as pm_id,
    (SELECT status FROM applications p WHERE p.project_id=pr.id AND p.volunteer_id=?) as pm_status
    FROM projects pr JOIN users u ON pr.ngo_id=u.id WHERE pr.id=?");
$stmt->bind_param('iii', $uid, $uid, $pid);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { header('Location: projects.php'); exit; }
$error = $konflik = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mohon'])) {
    $cek = $conn->prepare("
        SELECT pr.project_name FROM applications pm
        JOIN projects pr ON pm.project_id=pr.id
        WHERE pm.volunteer_id=? AND pm.status IN ('pending','accepted')
          AND pr.date=? AND pr.id != ?
          AND ? < pr.end_time AND ? > pr.start_time
    ");
    $cek->bind_param('issss', $uid, $p['date'], $pid, $p['start_time'], $p['end_time']);
    $cek->execute();
    $konflik_res = $cek->get_result();
    if ($konflik_res->num_rows > 0) {
        $pr_konflik = $konflik_res->fetch_assoc();
        $konflik = "You already have another project at the same time: <strong>" . htmlspecialchars($pr_konflik['project_name']) . "</strong>. Please remove that project first or choose another project.";
    } elseif ($p['vol_count'] >= $p['quota']) {
        $error = 'Sorry, this project\'s slots are full.';
    } elseif (!$p['pm_id']) {
        $ins = $conn->prepare("INSERT INTO applications (project_id, volunteer_id) VALUES (?,?)");
        $ins->bind_param('ii', $pid, $uid);
        $ins->execute();
        $msg = "Your application for project '{$p['project_name']}' has been received. Please wait for NGO confirmation.";
        $not = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?,?,?)");
        $tajuk = 'Application Successfully Sent';
        $not->bind_param('iss', $uid, $tajuk, $msg);
        $not->execute();
        $success = true;
        header("Location: project_details.php?id=$pid&mohon=ok"); exit;
    }
}
$kat_map = ['education'=>'Education','environment'=>'Environment','social'=>'Social','health'=>'Health','technology'=>'Technology','other'=>'Others'];
$badge_map = ['education'=>'badge-blue','environment'=>'badge-green','social'=>'badge-orange','health'=>'badge-red','technology'=>'badge-purple','other'=>'badge-grey'];
$sisa = $p['quota'] - $p['vol_count'];
$penuh = $sisa <= 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($p['project_name']) ?> — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    .detail-grid { display:grid; grid-template-columns:1fr 340px; gap:24px; }
    .info-block { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:28px; margin-bottom:20px; }
    .info-block h3 { font-size:13px; letter-spacing:.14em; text-transform:uppercase; color:var(--grey); margin-bottom:18px; font-family:'DM Sans',sans-serif; font-weight:500; }
    .detail-row { display:flex; gap:14px; align-items:flex-start; padding:12px 0; border-bottom:1px solid var(--border); }
    .detail-row:last-child { border-bottom:none; }
    .detail-icon { font-size:16px; flex-shrink:0; margin-top:1px; }
    .detail-lbl { font-size:11px; letter-spacing:.12em; text-transform:uppercase; color:var(--grey); margin-bottom:3px; }
    .detail-val { font-size:14px; color:var(--white); }
    .slot-bar { height:6px; background:rgba(255,255,255,0.08); border-radius:3px; margin-top:8px; overflow:hidden; }
    .slot-fill { height:100%; border-radius:3px; background:linear-gradient(to right,#5b9cf6,#4ade80); transition:width .4s; }
    #mini-map { height:200px; border-radius:10px; border:1px solid var(--border); margin-top:12px; }
    .konflik-box { background:rgba(251,146,60,.1); border:1px solid rgba(251,146,60,.3); border-radius:var(--radius); padding:20px 24px; margin-bottom:20px; }
    @media(max-width:900px){ .detail-grid{grid-template-columns:1fr} }
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
    <div style="font-size:13px;color:var(--grey);margin-bottom:24px">
      <a href="projects.php" style="color:var(--grey);text-decoration:none">← Back to Project List</a>
    </div>
    <?php if (isset($_GET['mohon']) && $_GET['mohon'] === 'ok'): ?>
    <div class="alert alert-success">✅ Your application has been successfully sent! Please wait for confirmation from the NGO.</div>
    <?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($konflik): ?>
    <div class="konflik-box">
      <div style="display:flex;gap:14px;align-items:flex-start">
        <span style="font-size:24px">⚠️</span>
        <div>
          <div style="font-size:15px;font-weight:500;color:var(--orange);margin-bottom:6px">Schedule Conflict Detected!</div>
          <div style="font-size:13px;color:var(--grey-light);line-height:1.65"><?= $konflik ?></div>
          <a href="schedule.php" class="btn btn-ghost btn-sm" style="margin-top:14px;color:var(--orange);border-color:rgba(251,146,60,.3)">View My Schedule →</a>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <div class="detail-grid">
      <div>
        <div class="info-block">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap">
            <span class="badge <?= $badge_map[$p['category']] ?? 'badge-grey' ?>"><?= $kat_map[$p['category']] ?? 'Others' ?></span>
            <?php if ($p['pm_status']): ?>
            <span class="badge <?= $p['pm_status']==='accepted' ? 'badge-green' : ($p['pm_status']==='pending' ? 'badge-orange' : 'badge-red') ?>">
              <?= ['pending'=>'Waiting for Confirmation','accepted'=>'✓ Accepted','rejected'=>'✗ Rejected','attended'=>'✓ Attended','absent'=>'✗ Absent'][$p['pm_status']] ?? $p['pm_status'] ?>
            </span>
            <?php endif; ?>
            <?php if ($penuh): ?><span class="badge badge-red">Full</span><?php endif; ?>
            <?php if (strtotime($p['date']) <= strtotime('+3 days')): ?><span style="font-size:11px;color:var(--orange)">● Urgent</span><?php endif; ?>
          </div>
          <h1 style="font-family:'Playfair Display',serif;font-size:28px;font-weight:900;line-height:1.2;margin-bottom:8px"><?= htmlspecialchars($p['project_name']) ?></h1>
          <div style="font-size:13px;color:var(--grey);margin-bottom:24px">🏢 <?= htmlspecialchars($p['ngo_name']) ?></div>
          <h3>Project Description</h3>
          <p style="font-size:14px;color:var(--grey-light);line-height:1.8;white-space:pre-line"><?= htmlspecialchars($p['description']) ?></p>
        </div>
        <div class="info-block">
          <h3>Project Information</h3>
          <div class="detail-row">
            <div class="detail-icon">📅</div>
            <div><div class="detail-lbl">Date</div><div class="detail-val"><?= date('l, d F Y', strtotime($p['date'])) ?></div></div>
          </div>
          <div class="detail-row">
            <div class="detail-icon">⏰</div>
            <div><div class="detail-lbl">Time</div><div class="detail-val"><?= date('H:i', strtotime($p['start_time'])) ?> – <?= date('H:i', strtotime($p['end_time'])) ?></div></div>
          </div>
          <div class="detail-row">
            <div class="detail-icon">📍</div>
            <div><div class="detail-lbl">Location</div><div class="detail-val"><?= htmlspecialchars($p['location']) ?><br><span style="color:var(--grey);font-size:12px"><?= htmlspecialchars($p['city']) ?>, <?= htmlspecialchars($p['state']) ?></span></div></div>
          </div>
          <?php if ($p['contact_phone']): ?>
          <div class="detail-row">
            <div class="detail-icon">📞</div>
            <div><div class="detail-lbl">Contact No.</div><div class="detail-val"><a href="tel:<?= $p['contact_phone'] ?>" style="color:var(--blue)"><?= htmlspecialchars($p['contact_phone']) ?></a></div></div>
          </div>
          <?php endif; ?>
          <div class="detail-row">
            <div class="detail-icon">👥</div>
            <div style="flex:1">
              <div class="detail-lbl">Volunteer Slots</div>
              <div class="detail-val"><?= $p['vol_count'] ?> / <?= $p['quota'] ?> filled</div>
              <div class="slot-bar"><div class="slot-fill" style="width:<?= min(100, ($p['vol_count']/$p['quota'])*100) ?>%"></div></div>
            </div>
          </div>
        </div>
        <?php if ($p['lat'] && $p['lng']): ?>
        <div class="info-block">
          <h3>Location on Map</h3>
          <div id="mini-map"></div>
        </div>
        <?php endif; ?>
      </div>
      <div>
        <div class="info-block" style="position:sticky;top:24px">
          <h3>Apply to Join Project</h3>
          <?php if ($p['pm_id']): ?>
            <div class="alert alert-<?= $p['pm_status']==='accepted' || $p['pm_status']==='attended' ? 'success' : ($p['pm_status']==='rejected' || $p['pm_status']==='absent' ? 'error' : 'info') ?>" style="margin-bottom:16px">
              <?php
              echo match($p['pm_status']) {
                'pending'   => '🕐 Your application is waiting for NGO confirmation.',
                'accepted'  => '✅ Congratulations! Your application has been accepted.',
                'rejected'  => '❌ Sorry, your application has been rejected.',
                'attended'  => '✅ You attended this project.',
                'absent'    => '❌ You were absent for this project.',
                default     => ''
              };
              ?>
            </div>
            <?php if ($p['pm_status'] === 'accepted' || $p['pm_status'] === 'attended'): ?>
            <a href="schedule.php" class="btn btn-blue btn-full">📅 View My Schedule</a>
            <?php endif; ?>
          <?php elseif ($penuh): ?>
            <div class="alert alert-error" style="margin-bottom:16px">😔 This project's slots are full.</div>
          <?php else: ?>
            <p style="font-size:13px;color:var(--grey-light);margin-bottom:20px;line-height:1.65">
              By clicking the button below, you are applying to join this project. The NGO will review your application.
            </p>
            <form method="POST">
              <input type="hidden" name="mohon" value="1"/>
              <button type="submit" class="btn btn-blue btn-full" id="btn-mohon">Send Application →</button>
            </form>
          <?php endif; ?>
          <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
            <a href="map.php" class="btn btn-ghost btn-full btn-sm">🗺️ View on Map</a>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<?php if ($p['lat'] && $p['lng']): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const miniMap = L.map('mini-map', {zoomControl:true, scrollWheelZoom:false}).setView([<?= $p['lat'] ?>, <?= $p['lng'] ?>], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap contributors',maxZoom:18}).addTo(miniMap);
const icon = L.divIcon({html:'<div style="width:14px;height:14px;background:#5b9cf6;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>',className:'',iconSize:[14,14],iconAnchor:[7,7]});
L.marker([<?= $p['lat'] ?>, <?= $p['lng'] ?>], {icon}).bindPopup('<?= htmlspecialchars(addslashes($p['location'])) ?>').addTo(miniMap);
</script>
<?php endif; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>
