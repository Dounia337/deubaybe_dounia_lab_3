<?php
session_start();
include '../config/database.php';

// Check if user is logged in and is Student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Fetch student's enrolled courses

$student_id = $_SESSION['user_id'];
$enrolled_courses_query = "SELECT c.* 
                          FROM courses c 
                          JOIN enrollments e ON c.id = e.course_id 
                          WHERE e.student_id = $student_id";

$enrolled_courses_result = $connection->query($enrolled_courses_query);

// Fetch other courses (not enrolled)
$other_courses_query = "SELECT c.* 
                       FROM courses c 
                       WHERE c.id NOT IN (
                           SELECT course_id FROM enrollments WHERE student_id = $student_id
                       )";

$other_courses_result = $connection->query($other_courses_query);

// Fetch session schedule

$sessions_query = "SELECT s.*, c.course_name 
                   FROM sessions s 
                   JOIN courses c ON s.course_id = c.id 
                   JOIN enrollments e ON c.id = e.course_id 
                   WHERE e.student_id = $student_id
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
    WHERE e.student_id = $student_id";
    
$stats_result = $connection->query($attendance_stats_query);
$stats = $stats_result->fetch_assoc();

// Calculate grade (simple percentage)
$grade = $stats['total_sessions'] > 0 ? round(($stats['attended'] / $stats['total_sessions']) * 100, 2) : 0;
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
    </div>

    <div class="list"> 
        <div class="list_session" id="session">
            <p><h3>Session Schedule</h3></p>
        </div>
        
        <?php if ($sessions_result->num_rows > 0): ?>
        <div class="session"> 
            <h4 style="color: rgb(187, 41, 41);">Attendance Session</h4> 
            <p>Course: Various</p>
            <p>Date: Various</p>
        </div>

        <div> 
            <table>
                <tr>
                    <th style="color: rgb(172, 80, 80);">Date</th>
                    <th style="color: rgb(172, 80, 80);">Time</th>
                    <th style="color: rgb(172, 80, 80);">Course</th>
                    <th style="color: rgb(172, 80, 80);">Hall</th>
                </tr>
                <?php while($session = $sessions_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $session['session_date']; ?></td>
                    <td><?php echo $session['session_time']; ?></td>
                    <td><?php echo $session['course_name']; ?></td>
                    <td><?php echo $session['hall']; ?></td>
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
                    <button onclick="joinCourse(<?php echo $course['id']; ?>)">Join</button>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No other courses available.</p>
            <?php endif; ?>
        </div> 
        
        <div class="list_session" id="view"> 
            <p><h3>View Feedbacks</h3></p>
        </div> 
    </div>

    <script src="../js/student.js"> >
    </script>
</body>
</html>
<?php 
$connection->close();
?>