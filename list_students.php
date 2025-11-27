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
<html>
<head>
    <title>List Students</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
    </style>
     <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Student List</h2>
    
    <?php if (!empty($students)): ?>
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
                        
                        <td><?php echo htmlspecialchars($student['fullname'] ?? $student['name']); ?></td>
                        <td><?php echo $student['matricule'] ?? $student['student_id']; ?></td>
                        <td><?php echo $student['group_id']?? $student['group']; ?></td>
                        <td>
                            
                          <a href="update_student.php?matricule=<?php echo $student['student_id']; ?>">Edit</a> 
                          
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No students found.</p>
    <?php endif; ?>
    
    <br>
    <a href="index.php" class="back-link">← Back to Home</a> | 
    <a href="add_student.php">Add New Student</a>
</body>
</html>



