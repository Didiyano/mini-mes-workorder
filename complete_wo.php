<?php
require 'db.php';
require 'auth.php';

if (!isset($_GET['id'])) {
    die("Missing ID");
}

$id = (int) $_GET['id'];
$message = "";
$operatorId = (int) $_SESSION['operator_id'];

// Ambil info work order
$stmt = $conn->prepare("SELECT * FROM work_orders WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$wo = $stmt->get_result()->fetch_assoc();

if (!$wo) {
    die("Work order not found.");
}

// Ambil senarai reject reasons (active sahaja)
$reasons = [];
$resultReasons = $conn->query("SELECT id, code, description FROM reject_reasons WHERE is_active = 1 ORDER BY code ASC");
if ($resultReasons && $resultReasons->num_rows > 0) {
    while ($r = $resultReasons->fetch_assoc()) {
        $reasons[] = $r;
    }
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_good   = (int) ($_POST['total_good'] ?? 0);
    $total_reject = (int) ($_POST['total_reject'] ?? 0);
    $reject_reason_id = (int) ($_POST['reject_reason_id'] ?? 0);

    if ($total_good < 0 || $total_reject < 0) {
        $message = "Quantity cannot be negative.";
    } elseif ($reject_reason_id <= 0) {
        $message = "Please select a reject reason (use NONE if no reject).";
    } else {
        $stmt2 = $conn->prepare("
            UPDATE work_orders 
            SET total_good = ?, 
                total_reject = ?, 
                reject_reason_id = ?, 
                status = 'COMPLETED', 
                completed_at = NOW(),
                complete_operator_id = ?
            WHERE id = ? AND status = 'IN_PROGRESS'
        ");

        $stmt2->bind_param("iiiii", $total_good, $total_reject, $reject_reason_id, $operatorId, $id);

        if ($stmt2->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complete Work Order</title>
</head>
<body>
    <h1>Complete Work Order #<?= htmlspecialchars($wo['wo_number']); ?></h1>

    <p>Product: <strong><?= htmlspecialchars($wo['product_name']); ?></strong></p>
    <p>Planned Qty: <strong><?= $wo['qty_planned']; ?></strong></p>
    <p>Status: <strong><?= $wo['status']; ?></strong></p>
    <p>Operator: <strong><?= htmlspecialchars($_SESSION['operator_name']); ?></strong></p>

    <?php if ($message): ?>
        <p style="color:red;"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Total Good:</label><br>
        <input type="number" name="total_good" min="0" required><br><br>

        <label>Total Reject:</label><br>
        <input type="number" name="total_reject" min="0" required><br><br>

        <label>Reject Reason:</label><br>
        <select name="reject_reason_id" required>
            <option value="">-- Select reason (NONE if no reject) --</option>
            <?php foreach ($reasons as $reason): ?>
                <option value="<?= $reason['id']; ?>">
                    <?= htmlspecialchars($reason['code'] . ' - ' . $reason['description']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <button type="submit">Complete</button>
        <a href="index.php">Cancel</a>
    </form>
</body>
</html>
