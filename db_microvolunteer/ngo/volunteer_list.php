<?php
session_start();
require_once '../config/db.php';
requireLogin('ngo');
$uid = $_SESSION['user_id'];
$pid = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$projek_list = $conn->query("SELECT id, project_name FROM projects WHERE ngo_id=$uid ORDER BY date DESC");
$where = "pr.ngo_id=$uid";
if ($pid) $where .= " AND p.project_id=$pid";
$vol_res = $conn->query("
    SELECT p.id as pm_id, p.status as pm_status, p.is_completed, p.volunteer_comment, p.volunteer_rating, p.created_at,
           u.id as volunteer_id, u.name as volunteer_name, u.email as volunteer_email,
           pr.id as project_id, pr.project_name, pr.date, pr.start_time, pr.end_time, pr.city, pr.state,
           vp.state as volunteer_state, vp.skills, vp.bio
    FROM applications p
    JOIN users u ON p.volunteer_id=u.id
    JOIN projects pr ON p.project_id=pr.id
    LEFT JOIN volunteer_profiles vp ON vp.user_id=u.id
    WHERE $where
    ORDER BY p.created_at DESC
");
$status_badge = ['pending'=>'badge-orange','accepted'=>'badge-green','rejected'=>'badge-red','attended'=>'badge-blue','absent'=>'badge-grey'];
$status_lbl   = ['pending'=>'Pending','accepted'=>'Accepted','rejected'=>'Rejected','attended'=>'Attended','absent'=>'Absent'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Volunteer List — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>
    .vol-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:22px 26px; margin-bottom:14px; }
    .vol-card:hover { border-color:rgba(255,255,255,.15); }
    .detail-drawer { display:none; margin-top:18px; padding-top:18px; border-top:1px solid var(--border); }
    .detail-drawer.open { display:block; }
    .komen-box { background:rgba(91,156,246,.07); border:1px solid rgba(91,156,246,.2); border-radius:10px; padding:14px 18px; margin-top:12px; font-size:13px; color:var(--white); line-height:1.7; }
    .attendance-btn { padding:7px 16px; border-radius:8px; font-size:12px; cursor:pointer; border:1px solid; background:transparent; letter-spacing:.08em; text-transform:uppercase; transition:all .2s; }
    .att-hadir { color:var(--green); border-color:rgba(74,222,128,.3); }
    .att-hadir:hover, .att-hadir.active { background:rgba(74,222,128,.12); }
    .att-tidak { color:var(--red); border-color:rgba(248,113,113,.3); }
    .att-tidak:hover, .att-tidak.active { background:rgba(248,113,113,.12); }
    .info-chip { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.05); border:1px solid var(--border); border-radius:8px; padding:5px 12px; font-size:12px; color:var(--grey-light); }
  </style>
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
      <a href="project_add.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 5v6M5 8h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Add Project</a>
      <a href="project_manage.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Manage Projects</a>
      <div class="sidebar-nav-title" style="margin-top:16px">Volunteers</div>
      <a href="volunteer_list.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.3"/><path d="M1 14c0-3 2.24-5 5-5s5 2 5 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Volunteer List</a>
      <div class="sidebar-nav-title" style="margin-top:16px">Information</div>
      <a href="help_guide.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help</a>
      <a href="about.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header"><h1 class="page-title">Volunteer <span class="italic-blue">List</span></h1><p class="page-sub">Review volunteer participation and attendance.</p></div>
    <form method="GET" style="margin-bottom:28px;display:flex;gap:12px;flex-wrap:wrap"><select name="project_id" class="form-control" style="max-width:360px" onchange="this.form.submit()"><option value="">All Projects</option><?php while ($pl = $projek_list->fetch_assoc()): ?><option value="<?= $pl['id'] ?>" <?= $pid == $pl['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pl['project_name']) ?></option><?php endwhile; ?></select></form>
    <?php if (isset($_GET['att_ok'])): ?><div class="alert alert-success">✅ Attendance updated.</div><?php endif; ?>
    <?php if (isset($_GET['pm_ok'])): ?><div class="alert alert-success">✅ Application status updated.</div><?php endif; ?>
    <?php $rows=[]; while($v=$vol_res->fetch_assoc()) $rows[]=$v; if(empty($rows)): ?>
    <div class="card" style="text-align:center;padding:60px"><div style="font-size:40px;margin-bottom:16px">👥</div><p style="color:var(--grey)">No volunteers found.</p></div>
    <?php else: foreach($rows as $i => $v): $sb=$status_badge[$v['pm_status']]??'badge-grey'; $sl=$status_lbl[$v['pm_status']]??$v['pm_status']; ?>
    <div class="vol-card">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div style="display:flex;gap:14px;align-items:center;flex:1"><div class="avatar" style="width:48px;height:48px;font-size:20px">🙋</div><div><div style="font-size:16px;font-weight:500;margin-bottom:4px"><?=htmlspecialchars($v['volunteer_name'])?></div><div style="display:flex;gap:10px;flex-wrap:wrap"><span class="info-chip">✉️ <?=htmlspecialchars($v['volunteer_email'])?></span><?php if($v['volunteer_state']):?><span class="info-chip">📍 <?=htmlspecialchars($v['volunteer_state'])?></span><?php endif;?></div></div></div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap"><div><div style="font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--grey);margin-bottom:4px">Project</div><div style="font-size:13px;max-width:200px"><?=htmlspecialchars($v['project_name'])?></div></div><span class="badge <?=$sb?>"><?=$sl?></span><button onclick="toggleDrawer(<?=$i?>)" class="btn btn-ghost btn-sm" id="toggle-<?=$i?>">Details ↓</button></div>
      </div>
      <div class="detail-drawer" id="drawer-<?=$i?>">
        <div class="grid-2" style="gap:14px;margin-bottom:16px">
          <div><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--grey);margin-bottom:6px">Project Info</div><div style="font-size:13px;color:var(--grey-light)">📅 <?=date('d M Y', strtotime($v['date']))?> ⏰ <?=date('H:i',strtotime($v['start_time']))?>–<?=date('H:i',strtotime($v['end_time']))?></div><div style="font-size:13px;color:var(--grey-light);margin-top:4px">📍 <?=htmlspecialchars($v['city'])?>, <?=htmlspecialchars($v['state'])?></div></div>
          <?php if($v['skills']):?><div><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--grey);margin-bottom:6px">Skills</div><div style="font-size:13px;color:var(--grey-light)"><?=htmlspecialchars($v['skills'])?></div></div><?php endif;?>
        </div>
        <?php if($v['pm_status'] === 'accepted' || $v['pm_status'] === 'attended' || $v['pm_status'] === 'absent'): ?>
        <div style="margin-bottom:16px"><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--grey);margin-bottom:10px">Mark Attendance</div>
          <form method="POST" action="process_application.php" style="display:inline"><input type="hidden" name="id" value="<?=$v['pm_id']?>"/><input type="hidden" name="action" value="attended"/><button type="submit" class="attendance-btn att-hadir <?=($v['pm_status']==='attended')?'active':''?>">✓ Present</button></form>
          <form method="POST" action="process_application.php" style="display:inline;margin-left:8px"><input type="hidden" name="id" value="<?=$v['pm_id']?>"/><input type="hidden" name="action" value="absent"/><button type="submit" class="attendance-btn att-tidak <?=($v['pm_status']==='absent')?'active':''?>">✗ Absent</button></form>
        </div>
        <?php endif; if($v['pm_status'] === 'pending'): ?>
        <div style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap">
          <form method="POST" action="process_application.php"><input type="hidden" name="id" value="<?=$v['pm_id']?>"/><input type="hidden" name="action" value="accept"/><button class="btn btn-ghost btn-sm" style="color:var(--green);border-color:rgba(74,222,128,.3)">✓ Accept Application</button></form>
          <form method="POST" action="process_application.php"><input type="hidden" name="id" value="<?=$v['pm_id']?>"/><input type="hidden" name="action" value="reject"/><button class="btn btn-danger btn-sm">✗ Reject</button></form>
        </div>
        <?php endif; if($v['is_completed'] && $v['volunteer_comment']): ?>
        <div><div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--grey);margin-bottom:8px">Feedback <?php if($v['volunteer_rating']):?>&nbsp;<?=str_repeat('⭐', $v['volunteer_rating'])?><?php endif;?></div><div class="komen-box"><?=htmlspecialchars($v['volunteer_comment'])?></div></div>
        <?php elseif($v['is_completed']):?><div style="font-size:13px;color:var(--grey)">Project completed. No comments.</div><?php endif;?>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </main>
</div>
<script src="../assets/js/main.js"></script>
<script>
function toggleDrawer(i) { const d = document.getElementById('drawer-' + i), btn = document.getElementById('toggle-' + i), open = d.classList.toggle('open'); btn.textContent = open ? 'Close ↑' : 'Details ↓'; }
</script>
</body>
</html>
