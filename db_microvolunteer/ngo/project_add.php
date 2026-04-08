<?php
session_start();
require_once '../config/db.php';
requireLogin('ngo');
$uid = $_SESSION['user_id'];
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name    = clean($_POST['project_name'] ?? '');
    $description     = clean($_POST['description'] ?? '');
    $category        = clean($_POST['category'] ?? 'other');
    $date            = clean($_POST['date'] ?? '');
    $start_time      = clean($_POST['start_time'] ?? '');
    $end_time        = clean($_POST['end_time'] ?? '');
    $location        = clean($_POST['location'] ?? '');
    $state           = clean($_POST['state'] ?? '');
    $city            = clean($_POST['city'] ?? '');
    $quota           = (int)($_POST['quota'] ?? 10);
    $contact_phone   = clean($_POST['contact_phone'] ?? '');
    $lat             = !empty($_POST['lat']) ? (float)$_POST['lat'] : null;
    $lng             = !empty($_POST['lng']) ? (float)$_POST['lng'] : null;
    if (empty($project_name) || empty($description) || empty($date) || empty($start_time) || empty($end_time) || empty($location) || empty($state)) {
        $error = 'Please complete all mandatory fields.';
    } elseif ($end_time <= $start_time) {
        $error = 'End time must be after start time.';
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $error = 'Project date cannot be in the past.';
    } else {
        $stmt = $conn->prepare("INSERT INTO projects (ngo_id, project_name, description, category, date, start_time, end_time, location, state, city, lat, lng, quota, contact_phone) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("issssssssssdis", $uid, $project_name, $description, $category, $date, $start_time, $end_time, $location, $state, $city, $lat, $lng, $quota, $contact_phone);
        if ($stmt->execute()) { $success = 'Project added successfully!'; }
        else { $error = 'An error occurred. Please try again.'; }
    }
}
$negeri_list = ['Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Perak','Perlis','Pulau Pinang','Sabah','Sarawak','Selangor','Terengganu','W.P. Kuala Lumpur','W.P. Labuan','W.P. Putrajaya'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Project — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>#map-picker { height:280px; border-radius:10px; border:1px solid var(--border); margin-top:8px; } .map-coords { font-size:12px; color:var(--grey); margin-top:8px; }</style>
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
      <a href="project_add.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 5v6M5 8h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Add Project</a>
      <a href="project_manage.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Manage Projects</a>
      <a href="volunteer_list.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.3"/><path d="M1 14c0-3 2.24-5 5-5s5 2 5 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Volunteer List</a>
      <div class="sidebar-nav-title" style="margin-top:16px">Information</div>
      <a href="help_guide.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help</a>
      <a href="about.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header"><h1 class="page-title">Add <span class="italic-blue">New Project</span></h1><p class="page-sub">Fill in the project information completely.</p></div>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="project_manage.php" style="color:inherit;font-weight:500">View all projects →</a></div><?php endif; ?>
    <form method="POST" style="max-width:760px">
      <div class="card" style="margin-bottom:20px">
        <h3 style="font-size:16px;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--border)">📋 Project Information</h3>
        <div class="form-group"><label class="form-label">Project Name <span style="color:var(--red)">*</span></label><input type="text" name="project_name" class="form-control" placeholder="Project Name" value="<?= htmlspecialchars($_POST['project_name'] ?? '') ?>" required/></div>
        <div class="form-group"><label class="form-label">Project Description <span style="color:var(--red)">*</span></label><textarea name="description" class="form-control" rows="4" placeholder="Description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea></div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">Category</label><select name="category" class="form-control"><?php foreach (['education'=>'Education','environment'=>'Environment','social'=>'Social','health'=>'Health','technology'=>'Technology','other'=>'Others'] as $k => $v): ?><option value="<?= $k ?>" <?= ($_POST['category'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">Volunteer Quota</label><input type="number" name="quota" class="form-control" min="1" max="500" value="<?= htmlspecialchars($_POST['quota'] ?? '10') ?>"/></div>
        </div>
      </div>
      <div class="card" style="margin-bottom:20px">
        <h3 style="font-size:16px;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--border)">📅 Date & Time</h3>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">Date <span style="color:var(--red)">*</span></label><input type="date" name="date" class="form-control" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>" min="<?= date('Y-m-d') ?>" required/></div>
          <div class="form-group"><label class="form-label">Contact No.</label><input type="tel" name="contact_phone" class="form-control" placeholder="01X-XXXXXXX" value="<?= htmlspecialchars($_POST['contact_phone'] ?? '') ?>"/></div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">Start Time <span style="color:var(--red)">*</span></label><input type="time" name="start_time" class="form-control" value="<?= htmlspecialchars($_POST['start_time'] ?? '') ?>" required/></div>
          <div class="form-group"><label class="form-label">End Time <span style="color:var(--red)">*</span></label><input type="time" name="end_time" class="form-control" value="<?= htmlspecialchars($_POST['end_time'] ?? '') ?>" required/></div>
        </div>
      </div>
      <div class="card" style="margin-bottom:20px">
        <h3 style="font-size:16px;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--border)">📍 Location</h3>
        <div class="form-group"><label class="form-label">Address <span style="color:var(--red)">*</span></label><input type="text" name="location" class="form-control" placeholder="Venue" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required/></div>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">State <span style="color:var(--red)">*</span></label><select name="state" class="form-control" required><option value="">-- Select State --</option><?php foreach ($negeri_list as $n): ?><option value="<?= $n ?>" <?= ($_POST['state'] ?? '') === $n ? 'selected' : '' ?>><?= $n ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label">City</label><input type="text" name="city" class="form-control" placeholder="City" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"/></div>
        </div>
        <div class="form-group">
          <label class="form-label">Map Pin</label><div id="map-picker"></div><div class="map-coords" id="coordsDisplay">Click map to pin.</div>
          <input type="hidden" name="lat" id="latInput" value="<?= $_POST['lat'] ?? '' ?>"/><input type="hidden" name="lng" id="lngInput" value="<?= $_POST['lng'] ?? '' ?>"/>
        </div>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap"><button type="submit" class="btn btn-blue">✓ &nbsp;Post Project</button><a href="project_manage.php" class="btn btn-ghost">Cancel</a></div>
    </form>
  </main>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../assets/js/main.js"></script>
<script>
const map = L.map('map-picker').setView([4.2105, 108.9758], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
const markerIcon = L.divIcon({ html:'<div style="width:16px;height:16px;background:#5b9cf6;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,0.4)"></div>', className:'',iconSize:[16,16],iconAnchor:[8,8] });
let marker = null;
const latIn = document.getElementById('latInput'), lngIn = document.getElementById('lngInput');
if (latIn.value && lngIn.value) { const latlng = [parseFloat(latIn.value), parseFloat(lngIn.value)]; marker = L.marker(latlng, {icon: markerIcon}).addTo(map); map.setView(latlng, 13); document.getElementById('coordsDisplay').textContent = `📌 Coordinates: ${latlng[0].toFixed(6)}, ${latlng[1].toFixed(6)}`; }
map.on('click', function(e) { if (marker) map.removeLayer(marker); marker = L.marker(e.latlng, {icon: markerIcon}).addTo(map); latIn.value = e.latlng.lat.toFixed(8); lngIn.value = e.latlng.lng.toFixed(8); document.getElementById('coordsDisplay').textContent = `📌 Coordinates: ${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}`; });
</script>
</body>
</html>
