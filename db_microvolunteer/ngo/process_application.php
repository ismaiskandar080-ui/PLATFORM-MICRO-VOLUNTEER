<?php
session_start();
require_once '../config/db.php';
requireLogin('ngo');
$uid = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'], $_POST['action'])) {
    header('Location: dashboard.php'); exit;
}
$id     = (int)$_POST['id'];
$action = $_POST['action'];
$chk = $conn->prepare("SELECT p.id FROM applications p JOIN projects pr ON p.project_id=pr.id WHERE p.id=? AND pr.ngo_id=?");
$chk->bind_param('ii', $id, $uid);
$chk->execute();
if ($chk->get_result()->num_rows === 0) {
    header('Location: dashboard.php'); exit;
}
$redirect = 'volunteer_list.php?pm_ok=1';
switch ($action) {
    case 'accept':
        $stmt = $conn->prepare("UPDATE applications SET status='accepted' WHERE id=?");
        $stmt->bind_param('i', $id); $stmt->execute();
        $info = $conn->prepare("SELECT p.volunteer_id, pr.project_name FROM applications p JOIN projects pr ON p.project_id=pr.id WHERE p.id=?");
        $info->bind_param('i', $id); $info->execute();
        $row = $info->get_result()->fetch_assoc();
        if ($row) {
            $title = 'Application Accepted! 🎉';
            $message = "Congratulations! Your application for project \"{$row['project_name']}\" has been accepted by the NGO.";
            $not = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?,?,?)");
            $not->bind_param('iss', $row['volunteer_id'], $title, $message); $not->execute();
        }
        break;
    case 'reject':
        $stmt = $conn->prepare("UPDATE applications SET status='rejected' WHERE id=?");
        $stmt->bind_param('i', $id); $stmt->execute();
        $info = $conn->prepare("SELECT p.volunteer_id, pr.project_name FROM applications p JOIN projects pr ON p.project_id=pr.id WHERE p.id=?");
        $info->bind_param('i', $id); $info->execute();
        $row = $info->get_result()->fetch_assoc();
        if ($row) {
            $title = 'Application Status Updated';
            $message = "Sorry, your application for project \"{$row['project_name']}\" could not be accepted this time.";
            $not = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?,?,?)");
            $not->bind_param('iss', $row['volunteer_id'], $title, $message); $not->execute();
        }
        break;
    case 'attended':
        $stmt = $conn->prepare("UPDATE applications SET status='attended' WHERE id=?");
        $stmt->bind_param('i', $id); $stmt->execute();
        break;
    case 'absent':
        $stmt = $conn->prepare("UPDATE applications SET status='absent' WHERE id=?");
        $stmt->bind_param('i', $id); $stmt->execute();
        break;
}
header("Location: $redirect"); exit;
?>
