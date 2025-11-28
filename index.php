<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management System</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="func.js" ></script>
    
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="#attendance">Attendance List</a></li>
            <li><a href="add_student.php">Add Student</a></li>
            <li><a href="take_attendance.php">Take Attendance</a></li>
            <li><a href="list_students.php">Manage Students</a></li>
            <li><a href="view_sessions.php">Sessions</a></li>
        </ul>
    </nav>

       <div class="container">
        <section id="attendance">
            <h2>Student Attendance List</h2>
            
            <!-- Exo7 Search -->
            <input type="text" id="searchBox" class="search-box" placeholder="Search by Name...">
            
            <!-- Buttons -->
            <div class="button-group">
                <button id="showReport">Show Report</button>
                <button id="highlightExcellent">Highlight Excellent Students</button>
                <button id="resetColors">Reset Colors</button>
                <button id="sortByAbsences">Sort by Absences (Ascending)</button>
                <button id="sortByParticipation">Sort by Participation (Descending)</button>
            </div>

            <!-- Exo7SortMessage -->
            <div id="sortMessage" class="sort-message" style="display:none;"></div>

            <!-- Exo4 Report Section -->
            <div id="reportSection" class="report-section"></div>


            <div class="table-wrapper">
                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th rowspan="2">Last Name</th>
                            <th rowspan="2">First Name</th>
                            <th colspan="2">S1</th>
                            <th colspan="2">S2</th>
                            <th colspan="2">S3</th>
                            <th colspan="2">S4</th>
                            <th colspan="2">S5</th>
                            <th colspan="2">S6</th>
                            <th rowspan="2">Absences</th>
                            <th rowspan="2">Participation</th>
                            <th rowspan="2">Message</th>
                        </tr>
                        <tr>
                            <th>P</th><th>Pa</th>
                            <th>P</th><th>Pa</th>
                            <th>P</th><th>Pa</th>
                            <th>P</th><th>Pa</th>
                            <th>P</th><th>Pa</th>
                            <th>P</th><th>Pa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Zioueche</td>
                            <td>Hasnaa Nour</td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td></td>
                            <td></td>
                            <td class="message-col"></td>
                        </tr>
                        <tr>
                            <td>Zeghmar</td>
                            <td>Douaa</td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td><input type="checkbox"></td>
                            <td></td>
                            <td></td>
                            <td class="message-col"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
     <!-- Exo 2/ 3: Student Form -->
      <div>
        <section id="add-student" style="width: 85%; margin: 20px auto;">
            <h2>Add New Student</h2>
            <div id="confirmationMessage" class="confirmation"></div>
            <form id="studentForm">
                <div class="form-group">
                    <label for="studentId">Student ID:</label>
                    <input type="text" id="studentId" name="studentId" required>
                    <span class="error-message" id="idError"></span>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name:</label>
                    <input type="text" id="lastName" name="lastName" required>
                    <span class="error-message" id="lastError"></span>
                </div>
                <div class="form-group">
                    <label for="firstName">First Name:</label>
                    <input type="text" id="firstName" name="firstName" required>
                    <span class="error-message" id="firstError"></span>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <span class="error-message" id="emailError"></span>
                </div>
                <button type="submit">Submit</button>
            </form>
        </section>
    </div>


       


   
</body>
</html>
