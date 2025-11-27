<?php
// take_attendance.php - Integrated with session system

require_once 'db_connect.php';

$message = '';
$error = '';
$openSessions = [];
$students = [];
$selectedSession = null;

$conn = connectDB();

// Load open sessions
if ($conn) {
    try {
        $sql = "SELECT s.*, c.course_name, c.course_code 
                FROM attendance_sessions s
                LEFT JOIN courses c ON s.course_id = c.id
                WHERE s.status = 'open'
                ORDER BY s.session_date DESC, s.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $openSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error loading sessions: " . $e->getMessage();
    }
}

// If session selected, load students
$session_id = $_GET['session_id'] ?? null;

if ($session_id && $conn) {
    try {
        // Get session details
        $sql = "SELECT * FROM attendance_sessions WHERE id = :id AND status = 'open'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $session_id);
        $stmt->execute();
        $selectedSession = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$selectedSession) {
            $error = "Session not found or already closed.";
        } else {
            $group_id = $selectedSession['group_id'];
            $date = $selectedSession['session_date'];
            $jsonFile = "attendance_{$date}.json";
            
            // Check if attendance already taken
            if (file_exists($jsonFile)) {
                $error = "Attendance for this session has already been taken on {$date}.";
            } else {
                // Load students from database for this group
                $sql = "SELECT id, fullname, matricule, email, group_id 
                        FROM students 
                        WHERE group_id = :group_id 
                        ORDER BY fullname";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':group_id', $group_id);
                $stmt->execute();
                $dbStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Load students from JSON
                $jsonStudents = [];
                if (file_exists('students.json')) {
                    $jsonData = file_get_contents('students.json');
                    $allJsonStudents = json_decode($jsonData, true) ?? [];
                    
                    // Filter by group
                    foreach ($allJsonStudents as $student) {
                        $studentGroup = $student['group'] ?? $student['group_id'] ?? null;
                        if ($studentGroup == $group_id) {
                            $jsonStudents[] = $student;
                        }
                    }
                }
                
                // Merge students (avoid duplicates by matricule/student_id)
                $studentMap = [];
                
                // Add DB students
                foreach ($dbStudents as $student) {
                    $key = $student['matricule'];
                    $studentMap[$key] = [
                        'id' => $student['id'],
                        'student_id' => $student['matricule'],
                        'name' => $student['fullname'],
                        'email' => $student['email'],
                        'group' => $student['group_id'],
                        'source' => 'db'
                    ];
                }
                
                // Add JSON students (if not already in DB)
                foreach ($jsonStudents as $student) {
                    $key = $student['student_id'] ?? '';
                    if (!isset($studentMap[$key])) {
                        $studentMap[$key] = [
                            'id' => null,
                            'student_id' => $key,
                            'name' => $student['name'] ?? ($student['firstName'] ?? '') . ' ' . ($student['lastName'] ?? ''),
                            'email' => $student['email'] ?? '',
                            'group' => $student['group'] ?? $student['group_id'] ?? '',
                            'source' => 'json'
                        ];
                    }
                }
                
                $students = array_values($studentMap);
            }
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_attendance'])) {
    $session_id = $_POST['session_id'];
    
    try {
        // Get session details
        $sql = "SELECT * FROM attendance_sessions WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $session_id);
        $stmt->execute();
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($session) {
            $date = $session['session_date'];
            $jsonFile = "attendance_session_{$session_id}.json";

            
            // Check again if file exists
           

              if (file_exists($jsonFile)) {
              echo "Attendance for this session has already been taken!";
                 exit;
                  } else {

                
                $attendanceData = [];
                $attendanceRecords = $_POST['attendance'] ?? [];
                $nameMap = [];
                foreach ($students as $s) {
                  $nameMap[$s['student_id']] = $s['name'] ?? ($s['firstName'] ?? '') . ' ' . ($s['lastName'] ?? '');
                      }
                // Save to database (attendance_records table)
                foreach ($attendanceRecords as $student_id => $status) {

    // Get participation for this student (posted from form)
    $participationValue = $_POST['participation'][$student_id] ?? 'no';

    // Get student's DB id if exists
    $sql = "SELECT id FROM students WHERE matricule = :matricule";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':matricule', $student_id);
    $stmt->execute();
    $studentRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($studentRecord) {
        // Save to attendance_records table
        $sql = "INSERT INTO attendance_records (session_id, student_id, status, participation) 
                VALUES (:session_id, :student_id, :status, :participation)";
        $ins = $conn->prepare($sql);
        $ins->bindParam(':session_id', $session_id);
        $ins->bindParam(':student_id', $studentRecord['id']);
        $ins->bindParam(':status', $status);
        $ins->bindParam(':participation', $participationValue);
        $ins->execute();
    }

    // Use name map (fall back to posted name if needed)
    $nameForJson = $nameMap[$student_id] ?? ($_POST['name'][$student_id] ?? '');

    $attendanceData[] = [
        'session_id'   => (string)$session_id,
        'student_id'   => (string)$student_id,
        'name'         => $nameForJson,
        'status'       => $status,
        'participation'=> $participationValue,
        'group'        => $session['group_id'] ?? ''   // include session group for safe filtering
    ];
}

// Save to JSON file (attendance_{date}.json)
file_put_contents($jsonFile, json_encode($attendanceData, JSON_PRETTY_PRINT));
             
                // Close the session
                $sql = "UPDATE attendance_sessions SET status = 'closed', closed_at = NOW() WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $session_id);
                $stmt->execute();
                
                $message = "Attendance saved successfully and session closed!";
                
                // Clear variables to hide form
                $students = [];
                $selectedSession = null;
            }
        }
    } catch (PDOException $e) {
        $error = "Error saving attendance: " . $e->getMessage();
    }
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h2 { color: #333; border-bottom: 3px solid #770a92ff; padding-bottom: 10px; }
        h3 { color: #555; margin-top: 30px; }
        .message { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .message.success { background: #4CAF50; color: white; }
        .message.error { background: #f44336; color: white; }
        .session-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .session-card { padding: 20px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .session-card:hover { border-color: #770a92ff; background: #f1f8f4; transform: translateY(-2px); }
        .session-info { margin: 5px 0; }
        .session-date { font-weight: bold; color: #770a92ff; font-size: 16px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #770a92ff; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .radio-group { display: flex; gap: 15px; }
        .radio-group label { cursor: pointer; display: flex; align-items: center; gap: 5px; }
        button { padding: 12px 30px; background: #770a92ff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin: 10px 5px; }
        button:hover { background: #770a92ff; }
    
        .back-link:hover { text-decoration: underline; }
        .empty-state { text-align: center; padding: 40px; color: #666; }
        .session-header { background: #e3f2fd; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-db { background: #770a92ff; color: white; }
        .badge-json { background: #FF9800; color: white; }
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Take Attendance</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <a href="view_sessions.php" class="back-link">View All Sessions</a> | 
            <a href="index.php" class="back-link">Back to Home</a>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!$selectedSession && !$message): ?>
            <h3>Select an Open Session:</h3>
            
            <?php if (!empty($openSessions)): ?>
                <div class="session-list">
                    <?php foreach ($openSessions as $session): ?>
                        <div class="session-card" onclick="location.href='?session_id=<?php echo $session['id']; ?>'">
                            <div class="session-date">Session #<?php echo $session['id']; ?></div>
                            <div class="session-info">
                                <strong>Course:</strong> <?php echo htmlspecialchars($session['course_name'] ?? 'Course ' . $session['course_id']); ?>
                            </div>
                            <div class="session-info">
                                <strong>Date:</strong> <?php echo $session['session_date']; ?>
                            </div>
                            <div class="session-info">
                                <strong>Group:</strong> <?php echo $session['group_id']; ?>
                            </div>
                            <div class="session-info">
                                <strong>Status:</strong> <span style="color: green;">OPEN</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No Open Sessions</h3>
                    <p>Please create a session first before taking attendance.</p>
                    <a href="create_session.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Create Session</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($selectedSession && !empty($students) && !$message): ?>
            <div class="session-header">
                <h3 style="margin: 0;">Taking Attendance for Session #<?php echo $selectedSession['id']; ?></h3>
                <div style="margin-top: 10px;">
                    <strong>Date:</strong> <?php echo $selectedSession['session_date']; ?> | 
                    <strong>Course:</strong> <?php echo $selectedSession['course_id']; ?> | 
                    <strong>Group:</strong> <?php echo $selectedSession['group_id']; ?>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="session_id" value="<?php echo $selectedSession['id']; ?>">
                
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
        
                            <th>Attendance</th>
                            <th>Participated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                               
                                <td>
                                    <div class="radio-group">
                                        <label>
                                            <input type="radio" name="attendance[<?php echo htmlspecialchars($student['student_id']); ?>]" value="present" checked>
                                            Present
                                        </label>
                                        <label>
                                            <input type="radio" name="attendance[<?php echo htmlspecialchars($student['student_id']); ?>]" value="absent">
                                            Absent
                                        </label>
                                       
                                    </div>
                                </td>
                                <td>
                                    <div class="radio-group">
                                        <label>
                                            <input type="radio" name="participation[<?php echo htmlspecialchars($student['student_id']); ?>]" value="yes" checked>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="radio" name="participation[<?php echo htmlspecialchars($student['student_id']); ?>]" value="no">
                                            No
                                        </label>
                                    </div>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px;">
                    <button type="submit" name="submit_attendance" onclick="return confirm('Submit attendance and close this session?');">
                        Submit Attendance & Close Session
                    </button>
                    <a href="take_attendance.php" style="padding: 12px 30px; background: #9E9E9E; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">Cancel</a>
                </div>
            </form>
            
            <div style="margin-top: 20px; padding: 10px; background: #770a92ff; border-radius: 4px;">
                <strong>Note:</strong> Submitting attendance will automatically close this session and save records.
            </div>
        <?php endif; ?>
        
        <?php if (!$message): ?>
            <br>
            <a href="create_session.php" class="back-link">Create New Session</a> | 
            <a href="view_sessions.php" class="back-link">View All Sessions</a> | 
            <a href="index.php" class="back-link">Back to Home</a>
        <?php endif; ?>
    </div>
</body>
</html>