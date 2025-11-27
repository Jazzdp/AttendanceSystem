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

$conn = null;

?>





