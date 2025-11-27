<?php
// ========================================
// FILE 2: add_student.php (Handles BOTH JSON and Database)
// ========================================

require_once 'db_connect.php';

$message = '';
$error = '';
$saveType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['studentId'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $firstName = trim($_POST['firstName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $group_id = trim($_POST['group_id'] ?? '');
    $saveType = $_POST['save_type'] ?? 'both'; // json, db, or both
    
    // Validation
    $idPattern = '/^[0-9]+$/';
    $namePattern = '/^[A-Za-zÀ-ÿ\s\'-]+$/';
    $emailPattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    
    $errors = [];
    
    if (empty($student_id) || !preg_match($idPattern, $student_id)) {
        $errors[] = "Student ID must contain only numbers.";
    }
    if (empty($lastName) || !preg_match($namePattern, $lastName)) {
        $errors[] = "Last name must contain only letters.";
    }
    if (empty($firstName) || !preg_match($namePattern, $firstName)) {
        $errors[] = "First name must contain only letters.";
    }
    if (empty($email) || !preg_match($emailPattern, $email)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (($saveType == 'db' || $saveType == 'both') && (empty($group_id) || !is_numeric($group_id))) {
        $errors[] = "Group ID is required and must be numeric for database storage.";
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    } else {
        $savedTo = [];
        
        // Save to JSON
       
            $jsonFile = 'students.json';
            $students = [];
            
            if (file_exists($jsonFile)) {
                $jsonData = file_get_contents($jsonFile);
                $students = json_decode($jsonData, true) ?? [];
            }
            
            // Check duplicate in JSON
            $duplicate = false;
            foreach ($students as $student) {
                if ($student['student_id'] == $student_id) {
                    $duplicate = true;
                    break;
                }
            }
            
            if (!$duplicate) {
                $students[] = [
                    'student_id' => $student_id,
                    'lastName' => $lastName,
                    'firstName' => $firstName,
                    'name' => $firstName . ' ' . $lastName,  // Add full name for compatibility
                    'group' => $group_id,  // Add group for compatibility
                    'email' => $email
                ];
                
                file_put_contents($jsonFile, json_encode($students, JSON_PRETTY_PRINT));
                $savedTo[] = 'JSON file';
            } else {
                $error = "Student ID already exists in JSON.";
            }
        }
        
        // Save to Database
            $conn = connectDB();
            if ($conn) {
                try {
                    $fullname = $firstName . ' ' . $lastName;
                    $sql = "INSERT INTO students (fullname, matricule, group_id, email) 
                            VALUES (:fullname, :matricule, :group_id, :email)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':fullname', $fullname);
                    $stmt->bindParam(':matricule', $student_id);
                    $stmt->bindParam(':group_id', $group_id);
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();
                    
                    $savedTo[] = 'Database';
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $error = "Student ID already exists in database.";
                    } else {
                        $error = "Database error: " . $e->getMessage();
                    }
                }
                $conn = null;
            } else {
                $error = "Database connection failed.";
            }
        
        
        if (!empty($savedTo) && empty($error)) {
            $message = "Student '$firstName $lastName' added successfully to: " . implode(' and ', $savedTo);
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-container { max-width: 600px; margin: 50px auto; padding: 30px; background: #f9f9f9; border-radius: 8px; }
        .form-group { margin: 20px 0; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-group input, .form-group select { width: 50%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px; }
        .radio-group { display: flex; gap: 10px; margin: 5px 0; }
        .radio-group label { font-weight: normal; font-size: 15px; gap:2px; display: flex; align-items: center; }
        .message { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .message.success { background: #4CAF50; color: white; }
        .message.error { background: #f44336; color: white; }
        .submit-btn { background: #4CAF50; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .submit-btn:hover { background: #45a049; }
        
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>Add New Student</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="studentId">Student ID / Matricule:</label>
                <input type="text" id="studentId" name="studentId" required 
                       value="<?php echo htmlspecialchars($_POST['studentId'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName" required 
                       value="<?php echo htmlspecialchars($_POST['lastName'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName" required 
                       value="<?php echo htmlspecialchars($_POST['firstName'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="group_id">Group ID:</label>
                <input type="number" id="group_id" name="group_id" 
                       value="<?php echo htmlspecialchars($_POST['group_id'] ?? ''); ?>"
                          >
                
            </div>
            
        
            
            <button type="submit" class="submit-btn">Add Student</button>
        </form>
        
        <a href="index.php" class="back-link">← Back to Home</a> | 
        <a href="list_students.php" class="back-link">View All Students</a>
    </div>
</body>
</html>