<?php

require_once 'db_connect.php';

$message = '';
$error = '';
$session_id = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = trim($_POST['course_id'] ?? '');
    $group_id = trim($_POST['group_id'] ?? '');
    $professor_id = trim($_POST['professor_id'] ?? '');
    
    if (empty($course_id) || empty($group_id) || empty($professor_id)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($course_id) || !is_numeric($group_id) || !is_numeric($professor_id)) {
        $error = "All IDs must be numeric.";
    } else {
        $conn = connectDB();
        
        if ($conn) {
            try {
                $date = date('Y-m-d');
                
                $sql = "INSERT INTO attendance_sessions (course_id, group_id, session_date, opened_by, status) 
                        VALUES (:course_id, :group_id, :session_date, :professor_id, 'open')";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':course_id', $course_id);
                $stmt->bindParam(':group_id', $group_id);
                $stmt->bindParam(':session_date', $date);
                $stmt->bindParam(':professor_id', $professor_id);
                
                $stmt->execute();
                $session_id = $conn->lastInsertId();
                $message = "Session created successfully! Session ID: $session_id";
                
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Session</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; }
        .form-group { margin: 20px 0; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .message { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .message.success { background: #4CAF50; color: white; }
        .message.error { background: #f44336; color: white; }
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Create Attendance Session</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Course ID:</label>
                <input type="number" name="course_id" required>
            </div>
            <div class="form-group">
                <label>Group ID:</label>
                <input type="number" name="group_id" required>
            </div>
            <div class="form-group">
                <label>Professor ID:</label>
                <input type="number" name="professor_id" required>
            </div>
            <button type="submit">Create Session</button>
        </form>
        
        <br>
        <a href="view_sessions.php">View All Sessions</a> | 
        <a href="close_session.php">Close Session</a> | 
        <a href="index.php">Back to Home</a>
    </div>
</body>
</html>

