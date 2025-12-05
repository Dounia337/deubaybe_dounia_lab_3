<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is Faculty Intern
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'fI') {
    header("Location: login.php");
    exit();
}

// Fetch all courses
$courses_query = "SELECT c.*, u.first_name, u.last_name 
                  FROM courses c 
                  JOIN users u ON c.created_by = u.id";
$courses_result = $connection->query($courses_query);

// Fetch latest session
$sessions_query = "SELECT s.*, c.course_name 
                   FROM sessions s 
                   JOIN courses c ON s.course_id = c.id 
                   ORDER BY s.session_date DESC, s.session_time DESC LIMIT 1";
$session_result = $connection->query($sessions_query);
$current_session = $session_result->fetch_assoc();

// Fetch attendance for current session
$attendance_data = [];
if ($current_session) {
    $session_id = $current_session['id'];
    $attendance_query = "SELECT a.*, u.first_name, u.last_name 
                         FROM attendance a 
                         JOIN users u ON a.student_id = u.id 
                         WHERE a.session_id = $session_id";
    $attendance_result = $connection->query($attendance_query);
    
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance_data[] = $row;
    }
}

// Calculate attendance statistics
$stats = [];
$stats['total_students'] = 0;
$stats['present'] = 0;
$stats['absent'] = 0;
$stats['late'] = 0;

if ($current_session) {
    $session_id = $current_session['id'];
    $stats_query = "SELECT 
        COUNT(DISTINCT student_id) as total_students,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
        FROM attendance WHERE session_id = $session_id";
    $stats_result = $connection->query($stats_query);
    $stats = $stats_result->fetch_assoc();
}
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <title>FI Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Alegreya">
    <link rel="stylesheet" href="../css/faculty_intern.css">
    <meta charset="UTF-8">
</head>
<body>
    <p><span style="float: right; border: 1px solid; z-index: 9999; position:relative; background-color: #f6f4f4;">
        <a href="logout.php">Log-out</a></span>
    </p>
    
    <div class="dashboard-container"> 
        <div class="user"> 
            <img src="../images/user.png" style="width:40px; height:40px;">
            <P><h4>Faculty-Intern Dashboard</h4></P> 
            <p><h4><?php echo $_SESSION['user_name']; ?></h4></p>
            <p><h5>ID: <?php echo $_SESSION['user_id']; ?></h5></p>
        </div>

        <div class="navigation">
            <div class="nav">
                <a href="#courses">Course List</a>
            </div>
            <div class="nav">
                <a href="#session">Session</a>
            </div>
            <div class="nav">
                <a href="#create-session">Create Session</a>
            </div>
            <div class="nav">
                <a href="#report">Reports</a>
            </div>
        </div>
    </div>
       
    <div class="list" id="courses"> 
        <div class="list_session"> 
            <p><h3>Course List</h3></p>
        </div>

        <div class="cards"> 
            <?php if ($courses_result->num_rows > 0): ?>
                <?php while($course = $courses_result->fetch_assoc()): ?>
                <div class="card">
                    <img src="../images/icon1.png" style="width:40px; height:40px;">
                    <h4><?php echo $course['course_name']; ?></h4>
                    <p><h4><?php echo $course['semester']; ?></h4></p>
                    <p><h5><?php echo $course['cohort']; ?></h5></p>
                    <p><small>By: <?php echo $course['first_name'] . ' ' . $course['last_name']; ?></small></p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No courses found.</p>
            <?php endif; ?>
        </div>  
    </div>

    <div class="list" id="create-session"> 
        <p><h3>Create Session</h3></p>
        
        <div style="margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 10px; max-width: 600px;">
            <h4>Create New Attendance Session</h4>
            <form id="createSessionForm">
                <label>Select Course:</label>
                <select id="session_course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php 
                    $courses_result->data_seek(0);
                    while($course = $courses_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $course['id']; ?>">
                            <?php echo $course['course_name'] . ' - ' . $course['semester']; ?>
                        </option>
                    <?php endwhile; ?>
                </select><br><br>
                
                <label>Session Date:</label>
                <input type="date" id="session_date" required><br><br>
                
                <label>Session Time:</label>
                <input type="time" id="session_time" required><br><br>
                
                <label>Hall:</label>
                <input type="text" id="hall" required><br><br>
                
                <label>Duration (minutes):</label>
                <input type="number" id="duration" min="15" max="300" value="60" required><br><br>
                
                <label>4-Digit PIN:</label>
                <input type="text" id="session_pin" pattern="[0-9]{4}" maxlength="4" placeholder="e.g., 1234" required><br><br>
                
                <button type="submit">Create Session</button>
            </form>
            <div id="sessionMessage" style="margin-top: 10px;"></div>
        </div>
    </div>

    <div class="list" id="session"> 
        <p><h3>Active Sessions</h3></p>

        <?php if ($current_session): ?>
        <div class="session"> 
            <h4>Latest Session</h4> 
            <p>Course: <?php echo $current_session['course_name']; ?></p>
            <p>Date: <?php echo $current_session['session_date']; ?></p>
            <p>Time: <?php echo $current_session['session_time']; ?></p>
            <?php if ($current_session['session_pin']): ?>
                <p><strong>PIN: <?php echo $current_session['session_pin']; ?></strong></p>
                <p>Status: <?php echo $current_session['is_active'] ? '<span style="color: green;">Active</span>' : '<span style="color: red;">Inactive</span>'; ?></p>
                <?php if ($current_session['is_active']): ?>
                    <button onclick="deactivateSession(<?php echo $current_session['id']; ?>)">Deactivate Session</button>
                <?php else: ?>
                    <button onclick="activateSession(<?php echo $current_session['id']; ?>)">Activate Session</button>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <h4 style="margin-top: 30px; color: rgb(187, 41, 41);">Students Present</h4>
        <div> 
            <table>
                <tr>
                    <th style="color: rgb(172, 80, 80);">Student Name</th>
                    <th style="color: rgb(172, 80, 80);">Student ID</th>
                    <th style="color: rgb(172, 80, 80);">Status</th>
                    <th style="color: rgb(172, 80, 80);">Time Marked</th>
                </tr>
                <?php if (!empty($attendance_data)): ?>
                    <?php foreach($attendance_data as $attendance): ?>
                    <tr style="background-color: <?php echo $attendance['status'] === 'present' ? '#d4edda' : ($attendance['status'] === 'late' ? '#fff3cd' : '#f8d7da'); ?>;">
                        <td><?php echo $attendance['first_name'] . ' ' . $attendance['last_name']; ?></td>
                        <td><?php echo $attendance['student_id']; ?></td>
                        <td><strong><?php echo ucfirst($attendance['status']); ?></strong></td>
                        <td><?php echo $attendance['time_marked'] ? date('g:i a', strtotime($attendance['time_marked'])) : '--'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No attendance data for this session</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <?php 
        // Fetch list of enrolled students who haven't marked attendance
        $enrolled_students_query = "SELECT u.id, u.first_name, u.last_name 
                                    FROM users u
                                    JOIN enrollments e ON u.id = e.student_id
                                    WHERE e.course_id = {$current_session['course_id']}
                                    AND u.id NOT IN (
                                        SELECT student_id FROM attendance WHERE session_id = {$current_session['id']}
                                    )
                                    ORDER BY u.last_name, u.first_name";
        $absent_students_result = $connection->query($enrolled_students_query);
        ?>

        <h4 style="margin-top: 30px; color: rgb(187, 41, 41);">Students Absent (Not Yet Marked)</h4>
        <div> 
            <table>
                <tr>
                    <th style="color: rgb(172, 80, 80);">Student Name</th>
                    <th style="color: rgb(172, 80, 80);">Student ID</th>
                    <th style="color: rgb(172, 80, 80);">Status</th>
                </tr>
                <?php if ($absent_students_result->num_rows > 0): ?>
                    <?php while($student = $absent_students_result->fetch_assoc()): ?>
                    <tr style="background-color: #f8d7da;">
                        <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                        <td><?php echo $student['id']; ?></td>
                        <td><strong>Absent</strong></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">All enrolled students have marked attendance</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php else: ?>
        <p>No sessions found.</p>
        <?php endif; ?>
    </div>

    <div class="list" id="report"> 
        <p><h3>Reports</h3></p>
        <div>
            <p><h4>Attendance Reports</h4></p>
            <table>
                <tr>
                    <th style="color: rgb(172, 80, 80);">Total Students</th>
                    <th style="color: rgb(172, 80, 80);">Present</th>
                    <th style="color: rgb(172, 80, 80);">Absent</th>
                    <th style="color: rgb(172, 80, 80);">Late</th>
                </tr>
                <tr>
                    <td><?php echo $stats['total_students']; ?></td>
                    <td><?php echo $stats['present']; ?></td>
                    <td><?php echo $stats['absent']; ?></td>
                    <td><?php echo $stats['late']; ?></td>
                </tr>
            </table>
        </div>
    </div>

    <script src="../js/faculty_intern.js"></script>
</body>
</html>
<?php 
$connection->close();
?>