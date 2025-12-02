<?php
require 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = "Please enter username and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM operators WHERE username = ? AND is_active = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $op = $result->fetch_assoc();

        // DEMO: password plain text
        if ($op && $op['password'] === $password) {
            $_SESSION['operator_id']   = $op['id'];
            $_SESSION['operator_name'] = $op['full_name'];

            header("Location: index.php");
            exit;
        } else {
            $message = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mini MES - Operator Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: radial-gradient(circle at top, #e3f2ff, #f4f5fb 55%);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-wrapper {
            max-width: 380px;
            width: 100%;
            padding: 24px;
        }
        .app-title {
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #5f6368;
            margin-bottom: 6px;
            text-align: center;
        }
        .login-box {
            background: #ffffff;
            padding: 24px 28px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        h2 {
            margin-top: 0;
            margin-bottom: 4px;
            text-align: center;
        }
        .subtitle {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            margin-bottom: 18px;
        }
        label {
            font-size: 14px;
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 12px;
            border-radius: 5px;
            border: 1px solid #c3c9e6;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 9px 0;
            border: none;
            border-radius: 5px;
            background-color: #1a73e8;
            color: white;
            font-size: 15px;
            cursor: pointer;
            margin-top: 4px;
        }
        button:hover {
            background-color: #1558b3;
        }
        .error {
            color: #d32f2f;
            font-size: 13px;
            margin-bottom: 10px;
            text-align: center;
        }
        .hint {
            font-size: 12px;
            color: #9ca3af;
            text-align: center;
            margin-top: 10px;
        }
        .hint code {
            background: #f3f4ff;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="app-title">Mini MES</div>
    <div class="login-box">
        <h2>Operator Login</h2>
        <div class="subtitle">Sign in to start or complete work orders.</div>

        <?php if ($message): ?>
            <div class="error"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <div class="hint">
            Demo users: <code>op1 / password1</code> or <code>op2 / password2</code>
        </div>
    </div>
</div>
</body>
</html>
