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
  <title>Project Map — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    #map { height: calc(100vh - 180px); min-height: 500px; border-radius: 14px; border: 1px solid var(--border); }
    .search-bar { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
    .search-bar input, .search-bar select { flex: 1; min-width: 160px; }
    .sidebar-info {
      position: absolute; top: 16px; right: 16px; z-index: 999;
      background: var(--card); border: 1px solid var(--border);
      border-radius: 14px; padding: 20px; width: 300px;
      display: none; max-height: calc(100vh - 220px); overflow-y: auto;
    }
    .sidebar-info.show { display: block; }
    .close-info { position: absolute; top: 12px; right: 14px; background: none; border: none; color: var(--grey); cursor: pointer; font-size: 18px; }
    .info-title { font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 700; margin-bottom: 8px; padding-right: 20px; }
    .info-ngo { font-size: 12px; color: var(--grey); margin-bottom: 14px; }
    .info-row { display: flex; gap: 8px; font-size: 13px; margin-bottom: 8px; color: var(--grey-light); }
    .info-desc { font-size: 13px; color: var(--grey-light); line-height: 1.6; margin: 14px 0; }
    .map-wrap { position: relative; }
    .legend { display: flex; gap: 16px; margin-bottom: 12px; flex-wrap: wrap; font-size: 12px; color: var(--grey-light); }
    .legend-item { display: flex; align-items: center; gap: 6px; }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; }
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
      <a href="map.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1C5.24 1 3 3.24 3 6c0 4.25 5 9 5 9s5-4.75 5-9c0-2.76-2.24-5-5-5z" stroke="currentColor" stroke-width="1.3"/><circle cx="8" cy="6" r="1.8" stroke="currentColor" stroke-width="1.3"/></svg>Project Map</a>
      <a href="projects.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>All Projects</a>
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
      <h1 class="page-title">Project <span class="italic-blue">Map</span></h1>
      <p class="page-sub">Find projects near you. Click on a pin to view project information.</p>
    </div>
    <div class="search-bar">
      <input type="text" id="searchInput" class="form-control" placeholder="🔍  Search project name or city..." oninput="filterMarkers()"/>
      <select id="negeriFilter" class="form-control" style="max-width:200px" onchange="filterMarkers()">
        <option value="">All States</option>
        <?php
        $negeri_list = ['Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Perak','Perlis','Pulau Pinang','Sabah','Sarawak','Selangor','Terengganu','W.P. Kuala Lumpur','W.P. Labuan','W.P. Putrajaya'];
        foreach ($negeri_list as $n) echo "<option value=\"$n\">$n</option>";
        ?>
      </select>
      <select id="katFilter" class="form-control" style="max-width:180px" onchange="filterMarkers()">
        <option value="">All Categories</option>
        <option value="education">Education</option>
        <option value="environment">Environment</option>
        <option value="social">Social</option>
        <option value="health">Health</option>
        <option value="technology">Technology</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div class="legend">
      <div class="legend-item"><div class="legend-dot" style="background:#5b9cf6"></div> Education</div>
      <div class="legend-item"><div class="legend-dot" style="background:#4ade80"></div> Environment</div>
      <div class="legend-item"><div class="legend-dot" style="background:#fb923c"></div> Social</div>
      <div class="legend-item"><div class="legend-dot" style="background:#f87171"></div> Health</div>
      <div class="legend-item"><div class="legend-dot" style="background:#c084fc"></div> Technology</div>
      <div class="legend-item"><div class="legend-dot" style="background:#aaa"></div> Others</div>
    </div>
    <div class="map-wrap">
      <div id="map"></div>
      <div class="sidebar-info" id="projekInfo">
        <button class="close-info" onclick="closeInfo()">✕</button>
        <div id="infoContent"></div>
      </div>
    </div>
  </main>
</div>
<?php
$projects_res = $conn->query("SELECT pr.id, pr.project_name, pr.description, pr.category, pr.date, pr.start_time, pr.end_time, pr.location, pr.state, pr.city, pr.quota, pr.contact_phone, pr.lat, pr.lng, u.name as ngo_name FROM projects pr JOIN users u ON pr.ngo_id=u.id WHERE pr.status='active' AND pr.date >= CURDATE() AND pr.lat IS NOT NULL AND pr.lng IS NOT NULL ORDER BY pr.date ASC");
$projects_data = [];
while ($p = $projects_res->fetch_assoc()) $projects_data[] = $p;
?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../assets/js/main.js"></script>
<script>
const projectsData = <?= json_encode($projects_data, JSON_UNESCAPED_UNICODE) ?>;
const katColor = { education:'#5b9cf6', environment:'#4ade80', social:'#fb923c', health:'#f87171', technology:'#c084fc', other:'#aaa' };
const map = L.map('map').setView([4.2105, 108.9758], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors', maxZoom: 18 }).addTo(map);
let markers = [];
function makeIcon(kat) {
  const col = katColor[kat] || '#aaa';
  return L.divIcon({ html: `<div style="width:14px;height:14px;background:${col};border:3px solid rgba(255,255,255,0.9);border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,0.5);cursor:pointer"></div>`, className: '', iconSize:[14,14], iconAnchor:[7,7] });
}
function addMarkers(data) {
  markers.forEach(m => map.removeLayer(m.marker));
  markers = [];
  data.forEach(p => {
    const m = L.marker([p.lat, p.lng], {icon: makeIcon(p.category)}).addTo(map);
    m.on('click', () => showInfo(p));
    markers.push({marker: m, data: p});
  });
}
function showInfo(p) {
  const katLbl = {education:'Education',environment:'Environment',social:'Social',health:'Health',technology:'Technology',other:'Other'};
  const d = new Date(p.date), bln = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  const tarikhFmt = d.getDate() + ' ' + bln[d.getMonth()] + ' ' + d.getFullYear();
  document.getElementById('infoContent').innerHTML = `
    <span style="font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:${katColor[p.category] || '#aaa'}">${katLbl[p.category] || 'Other'}</span>
    <div class="info-title">${p.project_name}</div>
    <div class="info-ngo">🏢 ${p.ngo_name}</div>
    <div class="info-row">📅 ${tarikhFmt}</div>
    <div class="info-row">⏰ ${p.start_time.slice(0,5)} – ${p.end_time.slice(0,5)}</div>
    <div class="info-row">📍 ${p.location}, ${p.city}</div>
    <div class="info-row">👥 ${p.quota} slots available</div>
    ${p.contact_phone ? `<div class="info-row">📞 ${p.contact_phone}</div>` : ''}
    <div class="info-desc">${p.description.substring(0,160)}${p.description.length > 160 ? '...' : ''}</div>
    <a href="project_details.php?id=${p.id}" class="btn btn-blue btn-sm" style="width:100%;justify-content:center;margin-top:8px">View Details & Apply →</a>
  `;
  document.getElementById('projekInfo').classList.add('show');
  map.setView([p.lat, p.lng], 13);
}
function closeInfo() { document.getElementById('projekInfo').classList.remove('show'); }
function filterMarkers() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  const negeri = document.getElementById('negeriFilter').value;
  const kat = document.getElementById('katFilter').value;
  const filtered = projectsData.filter(p => (!q || p.project_name.toLowerCase().includes(q) || p.city.toLowerCase().includes(q) || p.state.toLowerCase().includes(q)) && (!negeri || p.state === negeri) && (!kat || p.category === kat));
  addMarkers(filtered);
  document.getElementById('projekInfo').classList.remove('show');
}
addMarkers(projectsData);
if (projectsData.length === 0) { L.popup().setLatLng([4.2105,108.9758]).setContent('<p style="color:#333;font-size:13px">No active projects with locations at the moment.</p>').openOn(map); }
</script>
</body>
</html>
