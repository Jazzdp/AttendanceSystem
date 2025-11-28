<?php 



require_once 'db_connect.php';

$conn = connectDB();
$students = []; 

// --- Load DB students ---
if ($conn) {
    try {
        $sql = "SELECT fullname, matricule, group_id, email FROM students ORDER BY fullname";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $dbStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dbStudents as $s) {
            $id = $s['matricule'];

            $students[$id] = [
                'fullname'   => $s['fullname'],
                'student_id' => $id,
                'group'      => $s['group_id'],
                'email'      => $s['email'] ?? '',
                'source'     => 'db'
            ];
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
$conn = null;

// --- Load JSON students ---
$jsonFile = 'students.json';

if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $jsonStudents = json_decode($jsonData, true) ?? [];

    foreach ($jsonStudents as $s) {

        $id = $s['student_id'];

        // If this student already exists from DB → skip JSON version
        if (isset($students[$id])) continue;

        $fullname = $s['name'] 
                  ?? trim(($s['firstName'] ?? '') . ' ' . ($s['lastName'] ?? ''));

        $students[$id] = [
            'fullname'   => $fullname,
            'student_id' => $id,
            'group'      => $s['group'] ?? '',
            'email'      => $s['email'] ?? '',
            'source'     => 'json'
        ];
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <section>
            <h2>📚 Student List</h2>
            
            <?php if (!empty($students)): ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Matricule</th>
                                <th>Group ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td data-label="Full Name">
                                        <?php echo htmlspecialchars($student['fullname'] ?? $student['name']); ?>
                                    </td>
                                    <td data-label="Matricule">
                                        <?php echo htmlspecialchars($student['matricule'] ?? $student['student_id']); ?>
                                    </td>
                                    <td data-label="Group ID">
                                        <?php echo htmlspecialchars($student['group_id'] ?? $student['group']); ?>
                                    </td>
                                    <td data-label="Actions">
                                        <a  class="back-link" href="update_student.php?matricule=<?php echo urlencode($student['student_id'] ?? $student['matricule']); ?>">
                                            ✏️ Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>📭 No students found. Start by adding your first student!</p>
            <?php endif; ?>
            
            <div style="margin-top: 2rem; display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center;">
                <a href="index.php" class="back-link">← Back to Home</a>
                <a href="add_student.php">➕ Add New Student</a>
            </div>
        </section>
    </div>
</body>
</html>