<?php
require 'db.php';
require 'auth.php'; 

// Baca filter dari URL
$statusFilter = $_GET['status'] ?? 'ALL';
$search       = trim($_GET['search'] ?? '');

// Status yang dibenarkan
$allowedStatuses = ['NEW', 'IN_PROGRESS', 'COMPLETED'];

// Base SQL + JOIN operator & reject reason
$sql = "
    SELECT w.*, 
           r.code AS reject_code, r.description AS reject_desc,
           os.full_name AS start_operator,
           oc.full_name AS complete_operator
    FROM work_orders w
    LEFT JOIN reject_reasons r ON w.reject_reason_id = r.id
    LEFT JOIN operators os ON w.start_operator_id = os.id
    LEFT JOIN operators oc ON w.complete_operator_id = oc.id
    WHERE 1=1
";

$params = [];
$types  = "";

// Filter status
if (in_array($statusFilter, $allowedStatuses)) {
    $sql .= " AND w.status = ?";
    $params[] = $statusFilter;
    $types   .= "s";
}

// Filter search (WO Number / Product)
if ($search !== '') {
    $sql .= " AND (w.wo_number LIKE ? OR w.product_name LIKE ?)";
    $like = "%".$search."%";
    $params[] = $like;
    $params[] = $like;
    $types   .= "ss";
}

$sql .= " ORDER BY w.created_at ASC";

// Prepared statement
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mini MES - Work Orders List</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f5fb;
            margin: 0;
            padding: 40px;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .page-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px 32px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        a {
            color: #2c3e50;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn-link {
            color: #1a73e8;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            font-size: 14px;
        }

        th, td {
            padding: 10px 8px;
            border: 1px solid #dde2f1;
            text-align: left;
        }

        th {
            background-color: #f0f2ff;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #fafbff;
        }

        tr:hover {
            background-color: #eef3ff;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-new {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .badge-inprogress {
            background-color: #fff4e5;
            color: #ef6c00;
        }

        .badge-completed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .btn-action {
            padding: 4px 10px;
            border-radius: 4px;
            border: none;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-start {
            background-color: #1a73e8;
            color: white;
        }

        .btn-complete {
            background-color: #2e7d32;
            color: white;
        }

        .btn-action-link {
            text-decoration: none;
        }

        .filter-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .filter-form {
            font-size: 14px;
        }

        .filter-form select {
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #c3c9e6;
            background-color: #ffffff;
        }

        .search-form {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            margin-top: 4px;
        }

        .search-form input[type="text"] {
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #c3c9e6;
        }

        .search-form button {
            padding: 4px 10px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            background-color: #1a73e8;
            color: white;
        }

        .clear-search {
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="page-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
        <div style="font-size:14px; color:#555;">
            Logged in as <strong><?= htmlspecialchars($_SESSION['operator_name']); ?></strong>
        </div>
        <div style="font-size:14px;">
            <a href="report.php" style="margin-right:12px;">View Report</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h1>Mini MES - Work Orders List</h1>

    <div class="filter-row">
        <div>
            <p>
                <a class="btn-link" href="create_wo.php">+ Create New Work Order</a>
            </p>

            <form method="get" class="search-form">
                <!-- Kekalkan status semasa bila user search -->
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter); ?>">

                <input type="text" name="search"
                       placeholder="Search WO Number or Product"
                       value="<?= htmlspecialchars($search); ?>">

                <button type="submit">Search</button>

                <?php if ($search !== ''): ?>
                    <a href="index.php" class="clear-search">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <form method="get" class="filter-form">
            <!-- Kekalkan search bila user tukar status -->
            <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">

            <label for="status">Filter by status:</label>
            <select name="status" id="status" onchange="this.form.submit()">
                <option value="ALL" <?php if ($statusFilter === 'ALL') echo 'selected'; ?>>All</option>
                <option value="NEW" <?php if ($statusFilter === 'NEW') echo 'selected'; ?>>NEW</option>
                <option value="IN_PROGRESS" <?php if ($statusFilter === 'IN_PROGRESS') echo 'selected'; ?>>IN_PROGRESS</option>
                <option value="COMPLETED" <?php if ($statusFilter === 'COMPLETED') echo 'selected'; ?>>COMPLETED</option>
            </select>
            <noscript><button type="submit">Apply</button></noscript>
        </form>
    </div>

    <table>
        <tr>
            <th class="text-center">ID</th>
            <th class="text-center">WO Number</th>
            <th class="text-center">Product</th>
            <th class="text-center">Planned Qty</th>
            <th class="text-center">Status</th>
            <th class="text-center">Start Time</th>
            <th class="text-center">Start By</th>
            <th class="text-center">Complete Time</th>
            <th class="text-center">Complete By</th>
            <th class="text-center">Good</th>
            <th class="text-center">Reject</th>
            <th class="text-center">Reject Reason</th>
            <th class="text-center">Action</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {

                // Tentukan class badge ikut status
                $statusClass = '';
                if ($row['status'] === 'NEW') {
                    $statusClass = 'badge-new';
                } elseif ($row['status'] === 'IN_PROGRESS') {
                    $statusClass = 'badge-inprogress';
                } elseif ($row['status'] === 'COMPLETED') {
                    $statusClass = 'badge-completed';
                }

                echo "<tr>";

                echo "<td class='text-center'>" . $row['id'] . "</td>";
                echo "<td class='text-center'>" . htmlspecialchars($row['wo_number']) . "</td>";
                echo "<td class='text-center'>" . htmlspecialchars($row['product_name']) . "</td>";
                echo "<td class='text-center'>" . $row['qty_planned'] . "</td>";

                echo "<td style='text-align: center;'><span class='badge {$statusClass}'>" . $row['status'] . "</span></td>";

                echo "<td class='text-center'>" . $row['started_at'] . "</td>";
                echo "<td class='text-center'>" . htmlspecialchars($row['start_operator'] ?? '') . "</td>";
                echo "<td class='text-center'>" . $row['completed_at'] . "</td>";
                echo "<td class='text-center'>" . htmlspecialchars($row['complete_operator'] ?? '') . "</td>";
                echo "<td class='text-center'>" . $row['total_good'] . "</td>";
                echo "<td class='text-center'>" . $row['total_reject'] . "</td>";

                $rejectText = '';
                if (!empty($row['reject_code'])) {
                    $rejectText = $row['reject_code'] . ' - ' . $row['reject_desc'];
                }

                echo "<td>" . htmlspecialchars($rejectText) . "</td>";
                echo "<td class='text-center'>";
                if ($row['status'] === 'NEW') {
                    echo "<a class='btn-action-link' href='start_wo.php?id=" . $row['id'] . "'>
                            <button class='btn-action btn-start'>Start</button>
                          </a>";
                } elseif ($row['status'] === 'IN_PROGRESS') {
                    echo "<a class='btn-action-link' href='complete_wo.php?id=" . $row['id'] . "'>
                            <button class='btn-action btn-complete'>Complete</button>
                          </a>";
                } else {
                    echo "-";
                }
                echo "</td>";

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='10' class='text-center'>No work orders yet.</td></tr>";
        }
        ?>
    </table>
</div>
</body>
</html>
