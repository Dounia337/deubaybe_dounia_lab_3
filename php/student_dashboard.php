<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is Student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch student's enrolled courses (approved only)
$enrolled_courses_query = "SELECT c.* 
                          FROM courses c 
                          JOIN enrollments e ON c.id = e.course_id 
                          WHERE e.student_id = $student_id AND e.status = 'approved'";
$enrolled_courses_result = $connection->query($enrolled_courses_query);

// Fetch pending enrollments
$pending_courses_query = "SELECT c.course_name, c.semester, c.cohort, e.enrolled_at
                         FROM courses c
                         JOIN enrollments e ON c.id = e.course_id
                         WHERE e.student_id = $student_id AND e.status = 'pending'";
$pending_courses_result = $connection->query($pending_courses_query);

// Fetch other courses (not enrolled or pending)
$other_courses_query = "SELECT c.* 
                       FROM courses c 
                       WHERE c.id NOT IN (
                           SELECT course_id FROM enrollments WHERE student_id = $student_id
                       )";
$other_courses_result = $connection->query($other_courses_query);

// Fetch active sessions for enrolled courses (approved only)
$active_sessions_query = "SELECT s.*, c.course_name 
                         FROM sessions s 
                         JOIN courses c ON s.course_id = c.id 
                         JOIN enrollments e ON c.id = e.course_id 
                         WHERE e.student_id = $student_id 
                         AND e.status = 'approved'
                         AND s.is_active = TRUE
                         ORDER BY s.session_date DESC, s.session_time DESC";
$active_sessions_result = $connection->query($active_sessions_query);

// Fetch session schedule
$sessions_query = "SELECT s.*, c.course_name 
                   FROM sessions s 
                   JOIN courses c ON s.course_id = c.id 
                   JOIN enrollments e ON c.id = e.course_id 
                   WHERE e.student_id = $student_id
                   AND e.status = 'approved'
                   ORDER BY s.session_date DESC";
$sessions_result = $connection->query($sessions_query);

// Fetch attendance statistics
$attendance_stats_query = "SELECT 
    COUNT(*) as total_sessions,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as attended,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as missed
    FROM sessions s
    JOIN courses c ON s.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = $student_id
    WHERE e.student_id = $student_id AND e.status = 'approved'";
    
$stats_result = $connection->query($attendance_stats_query);
$stats = $stats_result->fetch_assoc();

// Calculate grade
$grade = 0;
if ($stats['total_sessions'] > 0) {
    $grade = round(($stats['attended'] / $stats['total_sessions']) * 100, 2);
}
?>

<!DOCTYPE html> 
<html lang="en">
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Alegreya">
    <link rel="stylesheet" href="../css/student.css">
    <meta charset="UTF-8">
</head>
<body>
    <p><span style="float: right; border: 1px solid; z-index: 9999; position:relative; background-color: #f6f4f4;">
        <a href="logout.php">Log-out</a></span>
    </p>
    
    <div class="dashboard-container"> 
        <div class="user"> 
            <img src="../images/user.png" style="width:40px; height:40px;">
            <P><h4>Student Dashboard</h4></P> 
            <p><h4><?php echo $_SESSION['user_name']; ?></h4></p>
            <p><h5>ID: <?php echo $_SESSION['user_id']; ?></h5></p>
        </div>

        <div class="navigation">
            <div class="nav">
                <a href="#courses">My Course</a>
            </div>
            <div class="nav">
                <a href="#attendance">Mark Attendance</a>
            </div>
            <div class="nav">
                <a href="#session">Session Schedule</a>
            </div>
            <div class="nav">
                <a href="#report">Grade/Reports</a>
            </div>
            <div class="nav">
                <a href="#othercourse">Other Courses</a>
            </div>
        </div>
    </div>

    <!-- Mark Attendance Section -->
    <div class="list" id="attendance">
        <div class="list_session">
            <p><h3>Mark Attendance</h3></p>
        </div>

        <?php if ($active_sessions_result->num_rows > 0): ?>
            <?php while($session = $active_sessions_result->fetch_assoc()): ?>
                <div class="session" style="margin-bottom: 20px;">
                    <h4 style="color: rgb(187, 41, 41);">Active Session</h4>
                    <p><strong>Course:</strong> <?php echo $session['course_name']; ?></p>
                    <p><strong>Date:</strong> <?php echo $session['session_date']; ?></p>
                    <p><strong>Time:</strong> <?php echo $session['session_time']; ?></p>
                    <p><strong>Hall:</strong> <?php echo $session['hall']; ?></p>
                    
                    <?php
                    // Check if already marked attendance
                    $check_attendance = "SELECT * FROM attendance WHERE session_id = {$session['id']} AND student_id = $student_id";
                    $check_result = $connection->query($check_attendance);
                    
                    if ($check_result->num_rows > 0):
                        $att = $check_result->fetch_assoc();
                    ?>
                        <p style="color: green;"><strong>✓ Attendance already marked as: <?php echo ucfirst($att['status']); ?></strong></p>
                    <?php else: ?>
                        <form onsubmit="markAttendance(event, <?php echo $session['id']; ?>)" style="margin-top: 15px;">
                            <label><strong>Enter 4-Digit PIN:</strong></label>
                            <input type="text" id="pin_<?php echo $session['id']; ?>" pattern="[0-9]{4}" maxlength="4" placeholder="1234" required style="padding: 8px; margin: 10px 0;">
                            <button type="submit" style="padding: 10px 20px;">Record Attendance</button>
                        </form>
                        <div id="message_<?php echo $session['id']; ?>" style="margin-top: 10px;"></div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No active sessions available for attendance.</p>
        <?php endif; ?>
    </div>
       
    <div class="list"> 
        <div class="list_session" id="courses"> 
            <p><h3>My Courses</h3></p>
        </div>

        <div class="cards"> 
            <?php if ($enrolled_courses_result->num_rows > 0): ?>
                <?php while($course = $enrolled_courses_result->fetch_assoc()): ?>
                <div class="card">
                    <img src="../images/icon1.png" style="width:40px; height:40px;">
                    <h4><?php echo $course['course_name']; ?></h4>
                    <p><h4><?php echo $course['semester']; ?></h4></p>
                    <p><h5><?php echo $course['cohort']; ?></h5></p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No courses enrolled yet.</p>
            <?php endif; ?>
        </div>

        <!-- Pending Enrollments -->
        <?php if ($pending_courses_result->num_rows > 0): ?>
        <div style="margin-top: 30px;">
            <h4 style="color: orange;">⏳ Pending Course Approvals</h4>
            <div class="cards">
                <?php while($course = $pending_courses_result->fetch_assoc()): ?>
                <div class="card" style="opacity: 0.6; border: 2px dashed orange;">
                    <img src="../images/icon1.png" style="width:40px; height:40px;">
                    <h4><?php echo $course['course_name']; ?></h4>
                    <p><h4><?php echo $course['semester']; ?></h4></p>
                    <p><h5><?php echo $course['cohort']; ?></h5></p>
                    <p style="color: orange;"><small>Awaiting approval</small></p>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="list"> 
        <div class="list_session" id="session">
            <p><h3>Session Schedule</h3></p>
        </div>
        
        <?php if ($sessions_result->num_rows > 0): ?>
        <div> 
            <table>
                <tr>
                    <th style="color: rgb(172, 80, 80);">Date</th>
                    <th style="color: rgb(172, 80, 80);">Time</th>
                    <th style="color: rgb(172, 80, 80);">Course</th>
                    <th style="color: rgb(172, 80, 80);">Hall</th>
                    <th style="color: rgb(172, 80, 80);">Status</th>
                </tr>
                <?php while($session = $sessions_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $session['session_date']; ?></td>
                    <td><?php echo $session['session_time']; ?></td>
                    <td><?php echo $session['course_name']; ?></td>
                    <td><?php echo $session['hall']; ?></td>
                    <td><?php echo $session['is_active'] ? '<span style="color: green;">Active</span>' : 'Closed'; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <?php else: ?>
        <p>No sessions scheduled.</p>
        <?php endif; ?>
    </div>

    <div class="list"> 
        <div>
            <div class="list_session" id="report">
                <p><h3>Grade / Reports</h3></p>
            </div>
        
            <table>
                <tr>
                    <th style="color: rgb(172, 80, 80);">Session Attended</th>
                    <th style="color: rgb(172, 80, 80);">Session Missed</th>
                    <th style="color: rgb(172, 80, 80);">Grade</th>
                </tr>
                <tr>
                    <td><?php echo $stats['attended']; ?></td>
                    <td><?php echo $stats['missed']; ?></td>
                    <td><?php echo $grade; ?>%</td>
                </tr>
            </table>
        </div>

        <div class="list_session" id="othercourse"> 
            <p><h3>Other Courses</h3></p>
        </div>
        
        <div class="cards"> 
            <?php if ($other_courses_result->num_rows > 0): ?>
                <?php while($course = $other_courses_result->fetch_assoc()): ?>
                <div class="card">
                    <img src="../images/icon1.png" style="width:40px; height:40px;">
                    <h4><?php echo $course['course_name']; ?></h4>
                    <p><h4><?php echo $course['semester']; ?></h4></p>
                    <p><h5><?php echo $course['cohort']; ?></h5></p>
                    <button onclick="joinCourse(<?php echo $course['id']; ?>)">Request to Join</button>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No other courses available.</p>
            <?php endif; ?>
        </div> 
    </div>

    <script src="../js/student.js"></script>
</body>
</html>
<?php 
$connection->close();
?>