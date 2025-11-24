<?php
// FILE 1: create_table.php
// Run this ONCE to create the students table

require_once 'db_connect.php';

$conn = connectDB();

if ($conn) {
    try {
        $sql = "CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fullname VARCHAR(100) NOT NULL,
            matricule VARCHAR(20) UNIQUE NOT NULL,
            group_id INT NOT NULL
        )";
        
        $conn->exec($sql);
        echo "✓ Students table created successfully!";
    } catch (PDOException $e) {
        echo "✗ Error creating table: " . $e->getMessage();
    }
} else {
    echo "Connection failed";
}
$conn = null;
?>

<?php
// FILE 2: add_student.php
// Create a new student

require_once 'db_connect.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    $group_id = trim($_POST['group_id'] ?? '');
    
    // Validation
    if (empty($fullname) || empty($matricule) || empty($group_id)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($group_id)) {
        $error = "Group ID must be a number.";
    } else {
        $conn = connectDB();
        
        if ($conn) {
            try {
                $sql = "INSERT INTO students (fullname, matricule, group_id) 
                        VALUES (:fullname, :matricule, :group_id)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':fullname', $fullname);
                $stmt->bindParam(':matricule', $matricule);
                $stmt->bindParam(':group_id', $group_id);
                
                $stmt->execute();
                $message = "Student '$fullname' added successfully!";
                
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        } else {
            $error = "Database connection failed.";
        }
        $conn = null;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
</head>
<body>
    <h2>Add Student</h2>
    
    <?php if ($message): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="text" name="matricule" placeholder="Matricule" required>
        <input type="number" name="group_id" placeholder="Group ID" required>
        <button type="submit">Add Student</button>
    </form>
</body>
</html>

<?php
// FILE 3: list_students.php
// Display all students

require_once 'db_connect.php';

$conn = connectDB();
$students = [];

if ($conn) {
    try {
        $sql = "SELECT id, fullname, matricule, group_id FROM students ORDER BY fullname";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
$conn = null;
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
</head>
<body>
    <h2>Student List</h2>
    
    <?php if (!empty($students)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Matricule</th>
                    <th>Group ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['id']; ?></td>
                        <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                        <td><?php echo $student['matricule']; ?></td>
                        <td><?php echo $student['group_id']; ?></td>
                        <td>
                            <a href="update_student.php?id=<?php echo $student['id']; ?>">Edit</a> |
                            <a href="delete_student.php?id=<?php echo $student['id']; ?>" onclick="return confirm('Delete this student?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No students found.</p>
    <?php endif; ?>
    
    <br>
    <a href="add_student.php">Add New Student</a>
</body>
</html>

<?php
// FILE 4: update_student.php
// Edit student information

require_once 'db_connect.php';

$message = '';
$error = '';
$student = null;
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Invalid student ID.");
}

$conn = connectDB();

if ($conn) {
    // Fetch student data
    try {
        $sql = "SELECT * FROM students WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            die("Student not found.");
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
    
    // Update student
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $fullname = trim($_POST['fullname'] ?? '');
        $matricule = trim($_POST['matricule'] ?? '');
        $group_id = trim($_POST['group_id'] ?? '');
        
        if (empty($fullname) || empty($matricule) || empty($group_id)) {
            $error = "All fields are required.";
        } else {
            try {
                $sql = "UPDATE students SET fullname = :fullname, matricule = :matricule, group_id = :group_id WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':fullname', $fullname);
                $stmt->bindParam(':matricule', $matricule);
                $stmt->bindParam(':group_id', $group_id);
                $stmt->bindParam(':id', $id);
                
                $stmt->execute();
                $message = "Student updated successfully!";
                $student['fullname'] = $fullname;
                $student['matricule'] = $matricule;
                $student['group_id'] = $group_id;
                
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
$conn = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Student</title>
</head>
<body>
    <h2>Update Student</h2>
    
    <?php if ($message): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    
    <?php if ($student): ?>
        <form method="POST">
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($student['fullname']); ?>" required>
            <input type="text" name="matricule" value="<?php echo $student['matricule']; ?>" required>
            <input type="number" name="group_id" value="<?php echo $student['group_id']; ?>" required>
            <button type="submit">Update Student</button>
        </form>
    <?php endif; ?>
    
    <br>
    <a href="list_students.php">Back to List</a>
</body>
</html>

<?php
// FILE 5: delete_student.php
// Delete a student

require_once 'db_connect.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Invalid student ID.");
}

$conn = connectDB();

if ($conn) {
    try {
        $sql = "DELETE FROM students WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        echo "✓ Student deleted successfully!<br>";
        echo "<a href='list_students.php'>Back to List</a>";
        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Connection failed.";
}
$conn = null;
?>