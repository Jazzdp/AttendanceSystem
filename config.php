
 <?php

/* define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'attendance_db'); */

/*[database]
     host = localhost 
     username = root
     password = 
     dbname = Attendance */

     $host = "localhost";
$port = 3306; // default MySQL port
$user = "root"; 
$pass = "";    // empty for XAMPP
$db   = "attendance"; // your database name


$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully!";
?>

