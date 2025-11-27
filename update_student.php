<?php

require_once 'db_connect.php';

$message = '';
$error = '';
$student = null;

// ----------------------------
// 1️⃣ Get matricule from URL
// ----------------------------
$matricule = $_GET['matricule'] ?? null;

if (!$matricule) {
    die("Missing student matricule.");
}

// ----------------------------
// JSON helpers
// ----------------------------
function loadJsonStudents() {
    $file = 'students.json';
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function saveJsonStudents($students) {
    file_put_contents('students.json', json_encode($students, JSON_PRETTY_PRINT));
}

// ------------------------------------------------------------
// 2️⃣ Try to fetch student from DATABASE using matricule
// ------------------------------------------------------------
$conn = connectDB();
$dbStudent = null;

if ($conn) {
    try {
        $sql = "SELECT * FROM students WHERE matricule = :matricule";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':matricule', $matricule);
        $stmt->execute();
        $dbStudent = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("DB Error: " . $e->getMessage());
    }
}

// ------------------------------------------------------------
// 3️⃣ Try to fetch from JSON if not found in DB
// ------------------------------------------------------------
$jsonStudents = loadJsonStudents();
$jsonStudent = null;

foreach ($jsonStudents as $s) {
    if ($s['student_id'] == $matricule) {
        $jsonStudent = $s;
        break;
    }
}

// ------------------------------------------------------------
// 4️⃣ Combine both to one unified student
// ------------------------------------------------------------
if ($dbStudent) {
     $student = [
        "fullname"  => $dbStudent['fullname'] ?? '',
        "matricule" => $dbStudent['matricule'] ?? '',
        "email"     => $dbStudent['email'] ?? '',
        "group_id"  => $dbStudent['group_id'] ?? '',
        "source"    => "db"
    ];
} elseif ($jsonStudent) {
    // Build fullname safely: prefer 'name', otherwise join firstName + lastName
    $jsonFullname = $jsonStudent['name'] ?? null;
    if ($jsonFullname === null) {
        $fn = trim(($jsonStudent['firstName'] ?? '') . ' ' . ($jsonStudent['lastName'] ?? ''));
        $jsonFullname = $fn !== '' ? $fn : '';
    }

    $student = [
        "fullname"  => $jsonFullname,
        "matricule" => $jsonStudent['student_id'] ?? '',
        "email"     => $jsonStudent['email'] ?? '',
        "group_id"  => $jsonStudent['group'] ?? $jsonStudent['group_id'] ?? '',
        "source"    => "json"
    ];
} else {
    die("Student not found in DB or JSON.");
}

// ------------------------------------------------------------
// 5️⃣ Handle DELETE REQUEST (AJAX)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['action'] ?? '') == 'delete') {

    // Delete from DB if exists
    if ($dbStudent) {
        try {
            $sql = "DELETE FROM students WHERE matricule = :matricule";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':matricule', $matricule);
            $stmt->execute();
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Delete from JSON
    $jsonStudents = array_filter($jsonStudents, function ($item) use ($matricule) {
        return $item['student_id'] != $matricule;
    });
    saveJsonStudents(array_values($jsonStudents));

    echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
    exit;
}

// ------------------------------------------------------------
// 6️⃣ Handle UPDATE REQUEST
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['action'])) {

    $fullname = trim($_POST['fullname']);
    $newMatricule = trim($_POST['matricule']);
    $email = trim($_POST['email']);
    $group_id = trim($_POST['group_id']);

    if (!$fullname || !$newMatricule || !$group_id) {
        $error = "Full name, matricule, and group ID are required.";
    } else {
        // Update DB if exists
        if ($dbStudent) {
            try {
                $sql = "UPDATE students SET fullname = :fullname, matricule = :matricule, email = :email, group_id = :group_id WHERE matricule = :oldMatricule";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':fullname', $fullname);
                $stmt->bindParam(':matricule', $newMatricule);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':group_id', $group_id);
                $stmt->bindParam(':oldMatricule', $matricule);
                $stmt->execute();
            } catch (PDOException $e) {
                $error = $e->getMessage();
            }
        }

        // Update JSON
        foreach ($jsonStudents as &$item) {
            if ($item['student_id'] == $matricule) {

                // Split fullname
                $parts = explode(' ', trim($fullname), 2);
                $firstName = $parts[0];
                $lastName = $parts[1] ?? "";

                $item['student_id'] = $newMatricule;
                $item['firstName'] = $firstName;
                $item['lastName'] = $lastName;
                $item['name'] = $firstName . " " . $lastName;
                $item['group'] = $group_id;
                $item['email'] = $email;
            }
        }

        saveJsonStudents($jsonStudents);

        $message = "Student updated successfully!";
        $matricule = $newMatricule; // refresh for reload
        $student['fullname'] = $fullname;
        $student['matricule'] = $newMatricule;
        $student['email'] = $email;
        $student['group_id'] = $group_id;
    }
}

$conn = null;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Student</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 400px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-bottom: 8px; }
        input { width: 50%; padding: 10px; }
        .message { padding: 12px; margin: 10px 0; border-radius: 4px; }
        .success { background: #4CAF50; color: white; }
        .error { background: #f44336; color: white; }
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Update Student</h2>

    <?php if ($message): ?>
        <div class="message success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name:</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($student['fullname']) ?>" required>
        </div>

        <div class="form-group">
            <label>Matricule:</label>
            <input type="text" name="matricule" value="<?= htmlspecialchars($student['matricule']) ?>" required>
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>">
        </div>

        <div class="form-group">
            <label>Group:</label>
            <input type="number" name="group_id" value="<?= htmlspecialchars($student['group_id']) ?>" required>
        </div>

        <button type="submit">Update Student</button>
        <button type="button" onclick="deleteStudent()" style="background:#f44336;color:white;padding:10px 25px;border:none;border-radius:4px;">Delete Student</button>
        <a href="list_students.php">Cancel</a>
    </form>
</div>

<script>
function deleteStudent() {
    if (!confirm("Are you sure you want to delete this student?")) return;

    fetch("", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "action=delete"
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) window.location = "list_students.php";
    });
}
</script>

</body>
</html>
