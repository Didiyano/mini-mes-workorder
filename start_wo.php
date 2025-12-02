<?php
require 'db.php';
require 'auth.php';

if (!isset($_GET['id'])) {
    die("Missing ID");
}

$id = (int) $_GET['id'];
$operatorId = (int) $_SESSION['operator_id'];

// Update status, start time & operator
$stmt = $conn->prepare("
    UPDATE work_orders 
    SET status = 'IN_PROGRESS', 
        started_at = NOW(),
        start_operator_id = ?
    WHERE id = ? AND status = 'NEW'
");
$stmt->bind_param("ii", $operatorId, $id);

if ($stmt->execute()) {
    header("Location: index.php");
    exit;
} else {
    echo "Error: " . $conn->error;
}
