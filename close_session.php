<?php

require_once 'db_connect.php';

$message = '';
$error = '';
$sessions = [];

$conn = connectDB();
if ($conn) {
    try {
        $sql = "SELECT * FROM attendance_sessions WHERE status = 'open' ORDER BY session_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $session_id = $_POST['session_id'] ?? '';
    
    if (empty($session_id)) {
        $error = "Please select a session.";
    } else {
        try {
            $sql = "UPDATE attendance_sessions SET status = 'closed', closed_at = NOW() WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $session_id);
            $stmt->execute();
            
            $message = "Session #$session_id closed successfully!";
            
            // Refresh sessions list
            $sql = "SELECT * FROM attendance_sessions WHERE status = 'open' ORDER BY session_date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Close Session</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f44336; color: white; }
        button { padding: 10px 20px; background: #f44336; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .message { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .message.success { background: #4CAF50; color: white; }
        .message.error { background: #f44336; color: white; }
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Close Attendance Session</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($sessions)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>Course ID</th>
                        <th>Group ID</th>
                        <th>Date</th>
                        <th>Opened By</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?php echo $session['id']; ?></td>
                            <td><?php echo $session['course_id']; ?></td>
                            <td><?php echo $session['group_id']; ?></td>
                            <td><?php echo $session['session_date']; ?></td>
                            <td><?php echo $session['opened_by']; ?></td>
                            <td><?php echo $session['status']; ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                    <button type="submit" onclick="return confirm('Close this session?');">Close</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No open sessions found.</p>
        <?php endif; ?>
        
        <br>
        <a href="create_session.php">Create New Session</a> | 
        <a href="view_sessions.php">View All Sessions</a> | 
        <a href="index.php">Back to Home</a>
    </div>
</body>
</html>
