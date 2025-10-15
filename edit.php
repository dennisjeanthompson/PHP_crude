<?php
require 'db.php';

// Helper: normalize date to YYYY-MM-DD or return false
function normalizeDate($input) {
    $input = trim((string)$input);
    if ($input === '') return false;
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
        list($y, $m, $d) = explode('-', $input);
        if (checkdate((int)$m, (int)$d, (int)$y)) return $input;
    }
    $formats = ['Y-m-d','Y/m/d','m/d/Y','d/m/Y','m-d-Y','d-m-Y'];
    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $input);
        if ($dt) {
            $norm = $dt->format('Y-m-d');
            list($y,$m,$d) = explode('-', $norm);
            if (checkdate((int)$m,(int)$d,(int)$y)) return $norm;
        }
    }
    $ts = strtotime($input);
    if ($ts !== false && $ts !== -1) {
        $norm = date('Y-m-d', $ts);
        list($y,$m,$d) = explode('-', $norm);
        if (checkdate((int)$m,(int)$d,(int)$y)) return $norm;
    }
    return false;
}

// Strict validator for YYYY-MM-DD
function isValidYmd($date) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
    list($y,$m,$d) = explode('-', $date);
    return checkdate((int)$m,(int)$d,(int)$y);
}

$id = $_GET['id'] ?? null;
if (!$id) die("No user ID specified.");

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) die("User not found.");

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');

    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($birthday)) $errors[] = "Birthday is required.";

    // Normalize/validate birthday
    if (!$errors) {
        $normalized = normalizeDate($birthday);
        if ($normalized === false) {
            $errors[] = "Birthday must be a valid date (e.g. YYYY-MM-DD).";
        } else {
            $birthday = $normalized;
        }
    }

    // Check unique email (excluding current user)
    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) $errors[] = "Email already exists.";
    }

    if (!$errors) {
        if (!isValidYmd($birthday)) {
            $errors[] = "Birthday is not a valid date in YYYY-MM-DD format. (Value received: '" . htmlspecialchars($birthday, ENT_QUOTES) . "')";
        } else {
            // sanitize birthday length
            $birthday = substr($birthday, 0, 10);
            try {
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, birthday=? WHERE id=?");
                $stmt->execute([$name, $email, $birthday, $id]);
                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                $msg = $e->getMessage();
                if (stripos($msg, 'truncated') !== false || strpos($msg, '1265') !== false) {
                    try {
                        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, birthday=STR_TO_DATE(?, '%Y-%m-%d') WHERE id=?");
                        $stmt->execute([$name, $email, $birthday, $id]);
                        header("Location: index.php");
                        exit;
                    } catch (PDOException $e2) {
                        $errors[] = "Database error after conversion attempt: " . $e2->getMessage() . " — attempted birthday value: " . htmlspecialchars($birthday, ENT_QUOTES);
                    }
                } elseif (strpos($msg, 'Duplicate') !== false || stripos($msg, 'unique') !== false) {
                    $errors[] = "Email already exists.";
                } else {
                    $errors[] = "Database error: " . $msg . " — attempted birthday value: " . htmlspecialchars($birthday, ENT_QUOTES);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
</head>
<body>
    <h1>Edit User</h1>
    <?php if ($errors): ?>
        <div style="color:red;">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday']) ?>" required>
        <button type="submit">Update</button>
    </form>
    <p><a href="index.php">Back to list</a></p>
</body>
</html>