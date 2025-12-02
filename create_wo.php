<?php
require 'db.php';
require 'auth.php'; // pastikan pengguna login

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wo_number    = trim($_POST['wo_number'] ?? '');
    $product_name = trim($_POST['product_name'] ?? '');
    $qty_planned  = (int) ($_POST['qty_planned'] ?? 0);

    if ($wo_number === '' || $product_name === '' || $qty_planned <= 0) {
        $message = "Please fill in all fields correctly.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO work_orders (wo_number, product_name, qty_planned) 
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param("ssi", $wo_number, $product_name, $qty_planned);

        if ($stmt->execute()) {
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
    <title>Create Work Order</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f5fb;
            margin: 0;
            padding: 40px;
        }

        .page-container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px 36px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 26px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 5px;
            border: 1px solid #c3c9e6;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .btn-save {
            background-color: #1a73e8;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            margin-right: 8px;
        }

        .btn-cancel {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            background-color: #e0e0e0;
            cursor: pointer;
        }

        .btn-cancel a {
            color: #333;
            text-decoration: none;
        }

        .btn-cancel a:hover {
            text-decoration: underline;
        }

        .btn-back {
            background-color: #fbbc04; /* soft yellow */
            color: #000;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            transition: 0.2s ease;
        }

        .btn-back:hover {
            background-color: #f9a602;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
        }

        .error {
            color: #d32f2f;
            font-size: 13px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>
<div class="page-container">

    <h1>Create New Work Order</h1>

    <?php if ($message): ?>
        <div class="error"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post">
        <label>WO Number</label>
        <input type="text" name="wo_number" required>

        <label>Product Name</label>
        <input type="text" name="product_name" required>

        <label>Planned Quantity</label>
        <input type="number" name="qty_planned" min="1" required>

        <div class="form-footer">
            <div>
                <button type="submit" class="btn-save">Save</button>

                <button type="button" class="btn-cancel">
                    <a href="index.php">Cancel</a>
                </button>
            </div>

            <a href="index.php" class="btn-back">Back</a>
        </div>
    </form>
</div>
</body>
</html>
