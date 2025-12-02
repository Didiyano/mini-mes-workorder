<?php
require 'db.php';
require 'auth.php';

// Baca filter
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to']   ?? '';
$status   = $_GET['status']    ?? 'ALL';

$allowedStatuses = ['NEW','IN_PROGRESS','COMPLETED'];

$where  = " WHERE 1=1 ";
$params = [];
$types  = "";

// Filter date ikut completed_at kalau ada, kalau takde guna created_at
if ($dateFrom !== '') {
    $where .= " AND DATE(w.created_at) >= ? ";
    $params[] = $dateFrom;
    $types .= "s";
}
if ($dateTo !== '') {
    $where .= " AND DATE(w.created_at) <= ? ";
    $params[] = $dateTo;
    $types .= "s";
}

// Filter status
if (in_array($status, $allowedStatuses)) {
    $where .= " AND w.status = ? ";
    $params[] = $status;
    $types .= "s";
}

// ---------- Summary query ----------
$summarySql = "
    SELECT 
        COUNT(*) AS total_wo,
        SUM(CASE WHEN w.status = 'COMPLETED' THEN 1 ELSE 0 END) AS completed_wo,
        SUM(w.qty_planned) AS qty_planned,
        SUM(w.total_good) AS total_good,
        SUM(w.total_reject) AS total_reject
    FROM work_orders w
    $where
";

$stmt = $conn->prepare($summarySql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

$totalWo      = (int) ($summary['total_wo'] ?? 0);
$completedWo  = (int) ($summary['completed_wo'] ?? 0);
$plannedTotal = (int) ($summary['qty_planned'] ?? 0);
$totalGood    = (int) ($summary['total_good'] ?? 0);
$totalReject  = (int) ($summary['total_reject'] ?? 0);

$efficiency = 0;
if ($plannedTotal > 0) {
    $efficiency = round(($totalGood / $plannedTotal) * 100, 1);
}

// ---------- Detail list (completed only by default for table) ----------
$detailSql = "
    SELECT 
        w.*, 
        os.full_name AS start_operator,
        oc.full_name AS complete_operator
    FROM work_orders w
    LEFT JOIN operators os ON w.start_operator_id = os.id
    LEFT JOIN operators oc ON w.complete_operator_id = oc.id
    $where
    ORDER BY w.created_at DESC
";

$stmt2 = $conn->prepare($detailSql);
if (!empty($params)) {
    $stmt2->bind_param($types, ...$params);
}
$stmt2->execute();
$detailResult = $stmt2->get_result();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mini MES - Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f5fb;
            margin: 0;
            padding: 40px;
        }
        .page-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px 32px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        h1 {
            margin-top: 0;
            margin-bottom: 4px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
        }
        a {
            text-decoration: none;
            color: #1a73e8;
        }
        a:hover {
            text-decoration: underline;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
            margin-bottom: 20px;
        }
        .summary-card {
            background-color: #f8f9ff;
            border-radius: 8px;
            padding: 10px 12px;
        }
        .summary-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: 700;
        }
        .summary-sub {
            font-size: 12px;
            color: #6b7280;
        }
        .summary-card.highlight {
            background-color: #e8f5e9;
        }
        .filter-form {
            display: flex;
            gap: 8px;
            align-items: flex-end;
            margin-top: 8px;
            font-size: 14px;
        }
        .filter-form label {
            font-size: 12px;
            color: #555;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        input[type="date"],
        select {
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #c3c9e6;
            font-size: 13px;
        }
        .btn-filter {
            padding: 6px 14px;
            border-radius: 4px;
            border: none;
            background-color: #1a73e8;
            color: white;
            cursor: pointer;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }
        th, td {
            padding: 8px 6px;
            border: 1px solid #dde2f1;
            text-align: left;
        }
        th {
            background-color: #f0f2ff;
        }
        tr:nth-child(even) {
            background-color: #fafbff;
        }
        .text-center {
            text-align: center;
        }
        .badge-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-completed { background:#e8f5e9; color:#2e7d32; }
        .badge-new { background:#e3f2fd; color:#1565c0; }
        .badge-inprogress { background:#fff4e5; color:#ef6c00; }
    </style>
</head>
<body>
<div class="page-container">
    <div class="top-bar">
        <div>
            <a href="index.php">&larr; Back to Work Orders</a>
        </div>
        <div>Logged in as <strong><?= htmlspecialchars($_SESSION['operator_name']); ?></strong></div>
    </div>

    <h1>Production Report</h1>
    <div style="font-size:13px; color:#6b7280;">Overview of work orders and output.</div>

    <form method="get" class="filter-form">
        <div class="filter-group">
            <label>Date from</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom); ?>">
        </div>
        <div class="filter-group">
            <label>Date to</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo); ?>">
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="ALL" <?= $status==='ALL' ? 'selected' : ''; ?>>All</option>
                <option value="NEW" <?= $status==='NEW' ? 'selected' : ''; ?>>NEW</option>
                <option value="IN_PROGRESS" <?= $status==='IN_PROGRESS' ? 'selected' : ''; ?>>IN PROGRESS</option>
                <option value="COMPLETED" <?= $status==='COMPLETED' ? 'selected' : ''; ?>>COMPLETED</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn-filter">Apply</button>
        </div>
    </form>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total Work Orders</div>
            <div class="summary-value"><?= $totalWo; ?></div>
            <div class="summary-sub"><?= $completedWo; ?> completed</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Planned Quantity</div>
            <div class="summary-value"><?= $plannedTotal; ?></div>
            <div class="summary-sub">Sum of all WO planned qty</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Output (Good)</div>
            <div class="summary-value"><?= $totalGood; ?></div>
            <div class="summary-sub">Reject: <?= $totalReject; ?></div>
        </div>
        <div class="summary-card highlight">
            <div class="summary-label">Overall Efficiency</div>
            <div class="summary-value"><?= $efficiency; ?>%</div>
            <div class="summary-sub">
                <?= $plannedTotal > 0 ? 'Good / Planned qty' : 'No planned quantity'; ?>
            </div>
        </div>
    </div>

    <h3>Work Orders Detail</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>WO Number</th>
            <th>Product</th>
            <th class="text-center">Planned</th>
            <th class="text-center">Good</th>
            <th class="text-center">Reject</th>
            <th class="text-center">Eff%</th>
            <th>Status</th>
            <th>Start Time</th>
            <th>Start By</th>
            <th>Complete Time</th>
            <th>Completed By</th>
        </tr>
        <?php if ($detailResult->num_rows > 0): ?>
            <?php while ($row = $detailResult->fetch_assoc()): ?>
                <?php
                    $eff = 0;
                    if ($row['qty_planned'] > 0) {
                        $eff = round(($row['total_good'] / $row['qty_planned']) * 100, 1);
                    }
                    $badgeClass = 'badge-new';
                    if ($row['status'] === 'IN_PROGRESS') $badgeClass = 'badge-inprogress';
                    if ($row['status'] === 'COMPLETED') $badgeClass = 'badge-completed';
                ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['wo_number']); ?></td>
                    <td><?= htmlspecialchars($row['product_name']); ?></td>
                    <td class="text-center"><?= $row['qty_planned']; ?></td>
                    <td class="text-center"><?= $row['total_good']; ?></td>
                    <td class="text-center"><?= $row['total_reject']; ?></td>
                    <td class="text-center"><?= $eff; ?>%</td>
                    <td><span class="badge-status <?= $badgeClass; ?>"><?= $row['status']; ?></span></td>
                    <td><?= $row['started_at']; ?></td>
                    <td><?= htmlspecialchars($row['start_operator'] ?? ''); ?></td>
                    <td><?= $row['completed_at']; ?></td>
                    <td><?= htmlspecialchars($row['complete_operator'] ?? ''); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="12" class="text-center">No work orders found for this filter.</td></tr>
        <?php endif; ?>
    </table>

</div>
</body>
</html>
