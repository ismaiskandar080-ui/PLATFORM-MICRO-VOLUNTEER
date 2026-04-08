<?php
session_start();
require_once '../config/db.php';
requireLogin('ngo');
$uid = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pid = (int) ($_POST['project_id'] ?? 0);
  $action = $_POST['action'] ?? '';
  $chk = $conn->prepare("SELECT id FROM projects WHERE id=? AND ngo_id=?");
  $chk->bind_param('ii', $pid, $uid);
  $chk->execute();
  if ($chk->get_result()->num_rows > 0) {
    if ($action === 'cancel') {
      $stmt = $conn->prepare("UPDATE projects SET status='cancelled' WHERE id=?");
      $stmt->bind_param('i', $pid); $stmt->execute();
      header("Location: project_manage.php?msg=cancelled"); exit;
    } elseif ($action === 'restore') {
      $stmt = $conn->prepare("UPDATE projects SET status='active' WHERE id=?");
      $stmt->bind_param('i', $pid); $stmt->execute();
      header("Location: project_manage.php?msg=restored"); exit;
    } elseif ($action === 'complete') {
      $stmt = $conn->prepare("UPDATE projects SET status='completed' WHERE id=?");
      $stmt->bind_param('i', $pid); $stmt->execute();
      header("Location: project_manage.php?msg=completed"); exit;
    } elseif ($action === 'delete') {
      $stmt = $conn->prepare("DELETE FROM projects WHERE id=? AND ngo_id=?");
      $stmt->bind_param('ii', $pid, $uid); $stmt->execute();
      header("Location: project_manage.php?msg=deleted"); exit;
    }
  }
}
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = "ngo_id=$uid";
if ($filter === 'active') $where .= " AND status='active'";
if ($filter === 'completed') $where .= " AND status='completed'";
if ($filter === 'cancelled') $where .= " AND status='cancelled'";
if ($filter === 'full') $where .= " AND status='full'";
$projects_res = $conn->query("SELECT pr.*, (SELECT COUNT(*) FROM applications p WHERE p.project_id=pr.id AND p.status IN ('accepted','attended')) as vol_count, (SELECT COUNT(*) FROM applications p WHERE p.project_id=pr.id AND p.status='pending') as vol_pending FROM projects pr WHERE $where ORDER BY pr.date DESC, pr.created_at DESC");
$kat_map = ['education'=>'Education','environment'=>'Environment','social'=>'Social','health'=>'Health','technology'=>'Technology','other'=>'Others'];
$status_badge = ['active'=>'badge-green','full'=>'badge-orange','completed'=>'badge-grey','cancelled'=>'badge-red'];
$status_lbl = ['active'=>'Active','full'=>'Full','completed'=>'Completed','cancelled'=>'Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/><title>Manage Projects — MicroVolunteer</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>.filter-tabs { display:flex; gap:8px; margin-bottom:28px; flex-wrap:wrap; } .filter-tab { padding:8px 18px; border-radius:100px; font-size:12px; letter-spacing:.1em; text-transform:uppercase; text-decoration:none; color:var(--grey-light); border:1px solid var(--border); transition:all .2s; } .filter-tab:hover { background:rgba(255,255,255,.05); color:var(--white); } .filter-tab.active { background:var(--blue-dim); color:var(--blue); border-color:rgba(91,156,246,.3); } .projek-row { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:22px 26px; margin-bottom:14px; transition:border-color .25s; } .projek-row:hover { border-color:rgba(255,255,255,.15); } .action-buttons { display:flex; gap:8px; flex-wrap:wrap; } .confirm-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.75); backdrop-filter:blur(4px); z-index:300; align-items:center; justify-content:center; padding:24px; } .confirm-modal.show { display:flex; }</style>
</head>
<body>
<div class="dash-wrap">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><span style="width:12px;height:12px;background:var(--white);transform:rotate(45deg);display:inline-block"></span>&nbsp;MicroVolunteer</a>
    <div class="sidebar-user">
      <div class="avatar">🏢</div>
      <div>
        <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['name']) ?></div>
        <div class="sidebar-user-role">NGO Organization</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-nav-title">Menu</div>
      <a href="dashboard.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>Dashboard</a>
      <a href="project_add.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 5v6M5 8h6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Add Project</a>
      <a href="project_manage.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.3"/><path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Manage Projects</a>
      <div class="sidebar-nav-title" style="margin-top:16px">Volunteers</div>
      <a href="volunteer_list.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.3"/><path d="M1 14c0-3 2.24-5 5-5s5 2 5 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>Volunteer List</a>
      <div class="sidebar-nav-title" style="margin-top:16px">Information</div>
      <a href="help_guide.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3"/><path d="M8 11V8M8 5.5v-.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>How to Help</a>
      <a href="about.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2 4.5H15l-3.5 3 1.5 5L8 12l-5 2.5 1.5-5L1 6.5h5z" stroke="currentColor" stroke-width="1.2"/></svg>About Us</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px"><div><h1 class="page-title">Manage <span class="italic-blue">Projects</span></h1><p class="page-sub">Review, update, or cancel projects.</p></div><a href="project_add.php" class="btn btn-blue">＋ &nbsp;Add New Project</a></div>
    <?php if (isset($_GET['msg'])): $msgs=['cancelled'=>'Project has been cancelled.','restored'=>'Project reactivated.','completed'=>'Project completed.','deleted'=>'Project deleted.']; ?><div class="alert alert-success"><?= $msgs[$_GET['msg']]??'' ?></div><?php endif; ?>
    <div class="filter-tabs"><?php foreach(['all'=>'All','active'=>'Active','full'=>'Full','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$v):?><a href="?filter=<?=$k?>" class="filter-tab <?=$filter===$k?'active':''?>"><?=$v?></a><?php endforeach;?></div>
    <?php $rows=[]; while($p=$projects_res->fetch_assoc()) $rows[]=$p; if(empty($rows)): ?>
    <div class="card" style="text-align:center;padding:60px"><div style="font-size:40px;margin-bottom:16px">📭</div><p style="color:var(--grey)">No projects found.</p><a href="project_add.php" class="btn btn-blue" style="margin-top:20px">Add Project</a></div>
    <?php else: foreach($rows as $p): $sb=$status_badge[$p['status']]??'badge-grey'; $sl=$status_lbl[$p['status']]??$p['status']; ?>
    <div class="projek-row">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div style="flex:1;min-width:240px">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;flex-wrap:wrap"><span class="badge <?=$sb?>"><?=$sl?></span><span class="badge badge-grey"><?=$kat_map[$p['category']]??'Others'?></span><?php if($p['vol_pending']>0):?><span class="badge badge-orange">⏳ <?=$p['vol_pending']?> pending</span><?php endif;?></div>
          <div style="font-family:'Playfair Display',serif;font-size:18px;font-weight:700;margin-bottom:6px"><?=htmlspecialchars($p['project_name'])?></div>
          <div style="display:flex;gap:20px;font-size:13px;color:var(--grey-light);flex-wrap:wrap"><span>📅 <?=date('d M Y', strtotime($p['date']))?></span><span>⏰ <?=date('H:i', strtotime($p['start_time']))?>–<?=date('H:i', strtotime($p['end_time']))?></span><span>📍 <?=htmlspecialchars($p['city'])?>, <?=htmlspecialchars($p['state'])?></span><span>👥 <?=$p['vol_count']?>/<?=$p['quota']?> volunteers</span></div>
        </div>
        <div class="action-buttons">
          <a href="volunteer_list.php?project_id=<?=$p['id']?>" class="btn btn-ghost btn-sm">👥 Volunteers</a>
          <?php if($p['status']==='active' || $p['status']==='full'): ?>
          <a href="project_edit.php?id=<?=$p['id']?>" class="btn btn-ghost btn-sm" style="color:var(--blue);border-color:rgba(91,156,246,.3)">✎ Edit</a>
            <button onclick="confirmAction(<?=$p['id']?>, 'complete', 'Mark as Completed?')" class="btn btn-ghost btn-sm" style="color:var(--green);border-color:rgba(74,222,128,.3)">✓ Complete</button>
            <button onclick="confirmAction(<?=$p['id']?>, 'cancel', 'Cancel this project?')" class="btn btn-danger btn-sm">✗ Cancel</button>
          <?php elseif($p['status']==='cancelled'): ?>
            <button onclick="confirmAction(<?=$p['id']?>, 'restore', 'Reactivate this project?')" class="btn btn-ghost btn-sm" style="color:var(--blue)">↺ Reactivate</button>
            <button onclick="confirmAction(<?=$p['id']?>, 'delete', 'Delete permanently?')" class="btn btn-danger btn-sm">🗑 Delete</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </main>
</div>
<div class="confirm-modal" id="confirmModal">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-title" id="modalTitle">Confirm Action</div><p class="modal-sub" id="modalMsg"></p>
    <form method="POST" id="confirmForm">
      <input type="hidden" name="project_id" id="modalProjekId"/><input type="hidden" name="action" id="modalAction"/>
      <div style="display:flex;gap:12px"><button type="submit" class="btn btn-blue" style="flex:1">Continue</button><button type="button" onclick="closeModal()" class="btn btn-ghost" style="flex:1">Cancel</button></div>
    </form>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
function confirmAction(id, action, msg) { document.getElementById('modalProjekId').value = id; document.getElementById('modalAction').value = action; document.getElementById('modalMsg').textContent = msg; document.getElementById('confirmModal').classList.add('show'); }
function closeModal() { document.getElementById('confirmModal').classList.remove('show'); }
document.getElementById('confirmModal').addEventListener('click', function (e) { if (e.target === this) closeModal(); });
</script>
</body>
</html>
