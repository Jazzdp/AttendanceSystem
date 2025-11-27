<?php
// db_init.php - Run this ONCE to create all tables
// This will set up your complete attendance management database

require_once 'db_connect.php';

$conn = connectDB();

if (!$conn) {
    die("✗ Connection failed. Check db_connect.php and config.php");
}

echo "<h2>Database Initialization</h2>";
echo "<p>Creating tables...</p><hr>";

try {
    // ========================================
    // 1. Students Table (Exercise 4)
    // ========================================
    $sql_students = "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(100) NOT NULL,
        matricule VARCHAR(20) UNIQUE NOT NULL,
        group_id INT NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_group (group_id),
        INDEX idx_matricule (matricule)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_students);
    echo "✓ <strong>students</strong> table created<br>";

    // ========================================
    // 2. Groups Table (Optional but recommended)
    // ========================================
    $sql_groups = "CREATE TABLE IF NOT EXISTS `groups` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        group_name VARCHAR(50) NOT NULL,
        level VARCHAR(20),
        year VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_groups);
    echo "✓ <strong>groups</strong> table created<br>";

    // ========================================
    // 3. Courses Table
    // ========================================
    $sql_courses = "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_code VARCHAR(20) NOT NULL,
        course_name VARCHAR(100) NOT NULL,
        professor_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_code (course_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_courses);
    echo "✓ <strong>courses</strong> table created<br>";

    // ========================================
    // 4. Professors Table
    // ========================================
    $sql_professors = "CREATE TABLE IF NOT EXISTS professors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        department VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_professors);
    echo "✓ <strong>professors</strong> table created<br>";

    // ========================================
    // 5. Attendance Sessions Table (Exercise 5)
    // ========================================
    $sql_sessions = "CREATE TABLE IF NOT EXISTS attendance_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        group_id INT NOT NULL,
        session_date DATE NOT NULL,
        opened_by INT NOT NULL,
        status ENUM('open', 'closed') DEFAULT 'open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        closed_at TIMESTAMP NULL,
        INDEX idx_course (course_id),
        INDEX idx_group (group_id),
        INDEX idx_date (session_date),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_sessions);
    echo "✓ <strong>attendance_sessions</strong> table created<br>";

    // ========================================
    // 6. Attendance Records Table
    // ========================================
    $sql_attendance = "CREATE TABLE IF NOT EXISTS attendance_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT NOT NULL,
        student_id INT NOT NULL,
        status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'absent',
        participation BOOLEAN DEFAULT FALSE,
        remarks TEXT,
        recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        UNIQUE KEY unique_session_student (session_id, student_id),
        INDEX idx_session (session_id),
        INDEX idx_student (student_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_attendance);
    echo "✓ <strong>attendance_records</strong> table created<br>";

    // ========================================
    // 7. Users Table (for login/authentication - optional)
    // ========================================
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'professor', 'student') DEFAULT 'student',
        student_id INT NULL,
        professor_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_username (username),
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL,
        FOREIGN KEY (professor_id) REFERENCES professors(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql_users);
    echo "✓ <strong>users</strong> table created<br>";

    echo "<hr>";
    echo "<h3>✓ All tables created successfully!</h3>";
    
    // ========================================
    // Insert sample data (optional)
    // ========================================
    echo "<hr><h3>Sample Data</h3>";
    
    // Check if groups table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM `groups`");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert sample groups
        $sql_sample_groups = "INSERT INTO `groups` (group_name, level, year) VALUES
            ('Group A', '3rd Year', '2024/2025'),
            ('Group B', '3rd Year', '2024/2025'),
            ('Group C', '2nd Year', '2024/2025')";
        $conn->exec($sql_sample_groups);
        echo "✓ Sample groups inserted<br>";
    }
    
    // Check if courses table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM courses");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert sample courses
        $sql_sample_courses = "INSERT INTO courses (course_code, course_name, professor_name) VALUES
            ('AWP', 'Advanced Web Programming', 'Dr. Smith'),
            ('DB', 'Database Systems', 'Dr. Johnson'),
            ('AI', 'Artificial Intelligence', 'Dr. Brown')";
        $conn->exec($sql_sample_courses);
        echo "✓ Sample courses inserted<br>";
    }
    
    // Check if professors table is empty
    $stmt = $conn->query("SELECT COUNT(*) FROM professors");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert sample professors
        $sql_sample_professors = "INSERT INTO professors (fullname, email, department) VALUES
            ('Dr. John Smith', 'john.smith@university.edu', 'Computer Science'),
            ('Dr. Sarah Johnson', 'sarah.johnson@university.edu', 'Computer Science'),
            ('Dr. Michael Brown', 'michael.brown@university.edu', 'Computer Science')";
        $conn->exec($sql_sample_professors);
        echo "✓ Sample professors inserted<br>";
    }

    echo "<hr>";
    echo "<h3>Database Structure:</h3>";
    echo "<ul>";
    echo "<li><strong>students</strong> - Student information</li>";
    echo "<li><strong>groups</strong> - Class groups</li>";
    echo "<li><strong>courses</strong> - Course information</li>";
    echo "<li><strong>professors</strong> - Professor information</li>";
    echo "<li><strong>attendance_sessions</strong> - Attendance session tracking</li>";
    echo "<li><strong>attendance_records</strong> - Individual attendance records</li>";
    echo "<li><strong>users</strong> - User authentication (optional)</li>";
    echo "</ul>";

    echo "<hr>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Use <a href='add_student.php'>add_student.php</a> to add students</li>";
    echo "<li>Use <a href='create_session.php'>create_session.php</a> to create attendance sessions</li>";
    echo "<li>Use <a href='take_attendance.php'>take_attendance.php</a> to record attendance</li>";
    echo "<li>Use <a href='index.php'>index.php</a> to view attendance reports</li>";
    echo "</ol>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error creating tables: " . $e->getMessage() . "</p>";
    echo "<p>Make sure your database exists and credentials in config.php are correct.</p>";
}

$conn = null;
?>

<style>
    body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
    h2 { color: #333; }
    h3 { color: #555; margin-top: 20px; }
    hr { margin: 20px 0; border: none; border-top: 2px solid #ddd; }
    ul, ol { line-height: 1.8; }
    a { color: #2196F3; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>