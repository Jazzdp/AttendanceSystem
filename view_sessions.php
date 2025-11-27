<?php

require_once 'db_connect.php';

$conn = connectDB();
$courses = [];
$selectedCourse = $_GET['course_id'] ?? null;
$selectedSession = $_GET['session_id'] ?? null;
$sessions = [];
$attendance = [];

if ($conn) {
    try {
        // Get all courses with session counts
        $sql = "SELECT c.id, c.course_code, c.course_name, 
                COUNT(DISTINCT s.id) as session_count
                FROM courses c
                LEFT JOIN attendance_sessions s ON c.id = s.course_id
                GROUP BY c.id
                ORDER BY c.course_code";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If course selected, get its sessions
        if ($selectedCourse) {
            $sql = "SELECT * FROM attendance_sessions 
                    WHERE course_id = :course_id 
                    ORDER BY session_date DESC, status ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':course_id', $selectedCourse);
            $stmt->execute();
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // If session selected, try to load JSON attendance
        if ($selectedSession) {
    $stmt = $conn->prepare("SELECT * FROM attendance_sessions WHERE id = :id");
    $stmt->execute([':id' => $selectedSession]);
    $sessionData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sessionData) {
        $date = $sessionData['session_date'];
        $jsonFile = "attendance_session_{$selectedSession}.json";


        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $allAttendance = json_decode($jsonData, true) ?? [];

            // Filter by session_id and optionally by group for extra safety
            foreach ($allAttendance as $rec) {
                if ((string)($rec['session_id'] ?? '') === (string)$selectedSession) {
                    // Optional extra check: if json has group, ensure it matches session group
                    if (isset($rec['group']) && $rec['group'] !== $sessionData['group_id']) {
                        continue;
                    }
                    $attendance[] = $rec;
                }
            }
        }
    }
}

      
        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Sessions</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 30px auto; padding: 30px; background: white; border-radius: 8px; }
        h2 { color: #333; border-bottom: 3px solid ##770a92ff; padding-bottom: 10px; }
        .course-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
        .course-card { padding: 20px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .course-card:hover { border-color: ##770a92ff; background: #e3f2fd; transform: translateY(-2px); }
        .course-card.selected { border-color: ##770a92ff; background: #e3f2fd; }
        .course-code { font-weight: bold; font-size: 18px; color: #770a92ff; }
        .session-count { color: #666; font-size: 14px; margin-top: 5px; }
        .sessions-dropdown { margin: 30px 0; }
        select { padding: 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 4px; width: 100%; max-width: 500px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-open { color: green; font-weight: bold; }
        .status-closed { color: red; }
        .attendance-present { background: #c8e6c9; }
        .attendance-absent { background: #ffcdd2; }
        .attendance-late { background: #fff9c4; }
    
        .empty-state { text-align: center; padding: 40px; color: #666; }
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>View Attendance Sessions</h2>
        
        <h3>Select a Course:</h3>
        <div class="course-list">
            <?php foreach ($courses as $course): ?>
                <div class="course-card <?php echo $selectedCourse == $course['id'] ? 'selected' : ''; ?>" 
                     onclick="location.href='?course_id=<?php echo $course['id']; ?>'">
                    <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                    <div><?php echo htmlspecialchars($course['course_name']); ?></div>
                    <div class="session-count"><?php echo $course['session_count']; ?> session(s)</div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($selectedCourse && !empty($sessions)): ?>
            <h3>Sessions for this Course:</h3>
            <div class="sessions-dropdown">
                <select onchange="if(this.value) location.href='?course_id=<?php echo $selectedCourse; ?>&session_id=' + this.value">
                    <option value="">-- Select a session to view attendance --</option>
                    <?php foreach ($sessions as $session): ?>
                        <option value="<?php echo $session['id']; ?>" <?php echo $selectedSession == $session['id'] ? 'selected' : ''; ?>>
                            Session #<?php echo $session['id']; ?> - 
                            <?php echo $session['session_date']; ?> - 
                            Group <?php echo $session['group_id']; ?> - 
                            <?php echo strtoupper($session['status']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php elseif ($selectedCourse): ?>
            <div class="empty-state">
                <p>No sessions found for this course.</p>
            </div>
        <?php endif; ?>
        
        <?php if ($selectedSession && !empty($attendance)): ?>
            <h3>Attendance Records for Session #<?php echo $selectedSession; ?></h3>
            <table>
                <thead>
                    <tr>
                          <th>Student ID</th>
                          <th>Name</th>
                         <th>Status</th>
                         <th>Participation</th>
                       
                    
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $record): ?>
                        <tr class="attendance-<?php echo $record['status'] ?? 'absent'; ?>">
                    <td><?php echo htmlspecialchars($record['student_id']); ?></td>
 
                    <td><?php echo htmlspecialchars($record['name']); ?></td>

                   <td><strong><?php echo strtoupper($record['status']); ?></strong></td>

                   <td><?php echo htmlspecialchars($record['participation'] ?? 'N/A'); ?></td>
                        </tr>

                     
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($selectedSession): ?>
            <div class="empty-state">
                <p>No attendance records found for this session.</p>
                <p>The JSON file for this date may not exist.</p>
            </div>
        <?php endif; ?>
        
        <br>
        <a href="create_session.php" >Create New Session</a> | 
        <a href="close_session.php" >Close Session</a> | 
        <a href="index.php" class="back-link">Back to Home</a>
    </div>
</body>
</html>