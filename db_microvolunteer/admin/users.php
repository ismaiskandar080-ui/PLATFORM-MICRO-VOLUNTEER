<?php
session_start();
require_once '../config/db.php';
requireLogin('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $target_id = (int) ($_POST['user_id'] ?? 0);
  $action = $_POST['action'] ?? '';
  if ($target_id > 0 && $target_id != $_SESSION['user_id']) {
    if ($action === 'block') { $conn->query("UPDATE users SET status='blocked' WHERE id=$target_id"); }
    elseif ($action === 'restore') { $conn->query("UPDATE users SET status='active' WHERE id=$target_id"); }
    elseif ($action === 'delete') { $conn->query("DELETE FROM users WHERE id=$target_id"); }
  }
  header("Location: users.php?msg=success"); exit;
}
$search = clean($_GET['q'] ?? '');
$filter_role = clean($_GET['role'] ?? '');
$where = "1=1";
if ($search) $where .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
if ($filter_role) $where .= " AND role='$filter_role'";
$users_res = $conn->query("SELECT * FROM users WHERE $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/><title>Manage Users — Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <style>
    .user-table { width:100%; border-collapse:separate; border-spacing:0 8px; margin-top:20px; }
    .user-table th { text-align:left; padding:12px 16px; font-size:11px; letter-spacing:.1em; text-transform:uppercase; color:var(--grey); border-bottom:1px solid var(--border); }
    .user-table td { background:var(--card); border-top:1px solid var(--border); border-bottom:1px solid var(--border); padding:16px; }
    .user-table td:first-child { border-left:1px solid var(--border); border-radius:12px 0 0 12px; } .user-table td:last-child { border-right:1px solid var(--border); border-radius:0 12px 12px 0; }
    .user-table tr:hover td { border-color:rgba(255,255,255,.15); } .avatar-sm { width:32px; height:32px; border-radius:50%; background:var(--blue-dim); color:var(--blue); display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:12px; }
  </style>
</head>
<body>
<div class="dash-wrap">
  <aside class="sidebar">
    <a href="../index.php" class="sidebar-brand"><span style="width:12px;height:12px;background:var(--white);transform:rotate(45deg);display:inline-block"></span>&nbsp;MicroVolunteer</a>
    <div class="sidebar-user"><div class="avatar">⚙️</div><div><div class="sidebar-user-name">Administrator</div><div class="sidebar-user-role">Super Control</div></div></div>
    <nav class="sidebar-nav">
      <div class="sidebar-nav-title">System Console</div>
      <a href="dashboard.php" class="sidebar-link"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>Dashboard</a>
      <a href="users.php" class="sidebar-link active"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.3"/><path d="M1 14c0-3 2.24-5 5-5s5 2 5 5" stroke="currentColor" stroke-width="1.3"/></svg>Manage Users</a>
    </nav>
    <div class="sidebar-bottom"><a href="../auth/logout.php" class="sidebar-link" style="color:var(--red)"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px">
      <div><h1 class="page-title">User <span class="italic-blue">Management</span></h1><p class="page-sub">Review system accounts.</p></div>
      <form method="GET" style="display:flex;gap:10px"><input type="text" name="q" placeholder="Search..." class="form-control" style="width:240px;margin:0" value="<?= htmlspecialchars($search) ?>"><select name="role" class="form-control" style="width:140px;margin:0"><option value="">All Roles</option><option value="volunteer" <?= $filter_role === 'volunteer' ? 'selected' :'' ?>>Volunteer</option><option value="ngo" <?= $filter_role === 'ngo' ? 'selected' :'' ?>>NGO</option><option value="admin" <?= $filter_role === 'admin' ? 'selected' :'' ?>>Admin</option></select><button type="submit" class="btn btn-blue">Filter</button></form>
    </div>
    <?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Success.</div><?php endif; ?>
    <div class="card p-0" style="background:transparent;border:none">
      <table class="user-table">
        <thead><tr><th>User</th><th>Email & Joined</th><th>Role</th><th>Status</th><th style="text-align:right">Action</th></tr></thead>
        <tbody>
          <?php while ($u = $users_res->fetch_assoc()): $s_badge = $u['status'] === 'active' ? 'badge-green' : ($u['status'] === 'blocked' ? 'badge-red' : 'badge-orange'); ?>
            <tr>
              <td style="display:flex;align-items:center;gap:12px"><div class="avatar-sm"><?= strtoupper(substr($u['name'],0,1)) ?></div><div style="font-weight:600"><?= htmlspecialchars($u['name']) ?></div></td>
              <td><div style="font-size:13px"><?= htmlspecialchars($u['email']) ?></div><div style="font-size:11px;color:var(--grey);margin-top:4px"><?= date('d M Y', strtotime($u['created_at'])) ?></div></td>
              <td><span class="badge badge-grey"><?= strtoupper($u['role']) ?></span></td>
              <td><span class="badge <?= $s_badge ?>"><?= strtoupper($u['status']) ?></span></td>
              <td style="text-align:right"><div style="display:flex;justify-content:flex-end;gap:8px"><?php if ($u['id'] != $_SESSION['user_id']): ?>
                    <form method="POST" onsubmit="return confirm('Confirm?')"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><?php if ($u['status'] === 'active'): ?><button type="submit" name="action" value="block" class="btn btn-ghost btn-sm" style="color:var(--orange)">Block</button><?php else: ?><button type="submit" name="action" value="restore" class="btn btn-ghost btn-sm" style="color:var(--green)">Unblock</button><?php endif; ?><button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" style="background:transparent;color:var(--red);border-color:rgba(239,68,68,.2)">Delete</button></form>
                  <?php else: ?><span style="font-size:11px;color:var(--grey-light)">Current Admin</span><?php endif; ?></div></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>