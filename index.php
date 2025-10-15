<?php
require 'db.php';

// Helper: normalize date to YYYY-MM-DD or return false
function normalizeDate($input) {
    $input = trim((string)$input);
    if ($input === '') return false;
    // If already valid Y-m-d
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
        list($y, $m, $d) = explode('-', $input);
        if (checkdate((int)$m, (int)$d, (int)$y)) return $input;
    }
    // Try common formats
    $formats = ['Y-m-d','Y/m/d','m/d/Y','d/m/Y','m-d-Y','d-m-Y'];
    foreach ($formats as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $input);
        if ($dt) {
            $norm = $dt->format('Y-m-d');
            list($y,$m,$d) = explode('-', $norm);
            if (checkdate((int)$m,(int)$d,(int)$y)) return $norm;
        }
    }
    // Fallback to strtotime
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

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');

    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($birthday)) {
        $errors[] = "Birthday is required.";
    } else {
        $normalized = normalizeDate($birthday);
        if ($normalized === false) {
            $errors[] = "Birthday must be a valid date (e.g. YYYY-MM-DD).";
        } else {
            $birthday = $normalized;
        }
    }

    // Check unique email
    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = "Email already exists.";
    }

    if (!$errors) {
        // Enforce strict Y-m-d before inserting
        if (!isValidYmd($birthday)) {
            $errors[] = "Birthday is not a valid date in YYYY-MM-DD format. (Value received: '" . htmlspecialchars($birthday, ENT_QUOTES) . "')";
        } else {
            // Ensure birthday is exactly 10 chars (YYYY-MM-DD)
            $birthday = substr($birthday, 0, 10);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, birthday, email) VALUES (?, ?, ?)");
                $stmt->execute([$name, $birthday, $email]);
                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                $msg = $e->getMessage();
                // If truncation warning, attempt to insert using STR_TO_DATE
                if (stripos($msg, 'truncated') !== false || strpos($msg, '1265') !== false) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO users (name, birthday, email) VALUES (?, STR_TO_DATE(?, '%Y-%m-%d'), ?)");
                        $stmt->execute([$name, $birthday, $email]);
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

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

function computeAge($birthday) {
    $dob = new DateTime($birthday);
    $today = new DateTime();
    return $dob->diff($today)->y;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Directory</title>
</head>
<body>
    <h1>Student Directory</h1>
    <?php if ($errors): ?>
        <div style="color:red;">
            <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="date" name="birthday" required>
        <button type="submit">Add User</button>
    </form>
    <h2>Users</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Birthday</th>
            <th>Age</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['birthday']) ?></td>
                <td><?= computeAge($u['birthday']) ?></td>
                <td>
                    <a href="edit.php?id=<?= $u['id'] ?>">Edit</a>
                    <form action="delete.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" onclick="return confirm('Delete this user?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>