<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Fetch teacher's courses
$courses_query = "SELECT * FROM courses WHERE created_by = $teacher_id";
$courses_result = $connection->query($courses_query);

// Fetch latest session
$session_query = "SELECT s.*, c.course_name 
                 FROM sessions s 
                 JOIN courses c ON s.course_id = c.id 
                 WHERE s.created_by = $teacher_id 
                 ORDER BY s.session_date DESC LIMIT 1";
$session_result = $connection->query($session_query);
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

// Calculate stats
$stats = [];
$stats['total_students'] = 0;
$stats['present'] = 0;
$stats['absent'] = 0;
$stats['late'] = 0;

if ($current_session) {
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
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Alegreya">
    <link rel="stylesheet" href="../css/teacher.css">
    <meta charset="UTF-8">
</head>
<body>
    <p><span style="float: right; border: 1px solid; z-index: 9999; position:relative; background-color: #f6f4f4;">
        <a href="logout.php">Log-out</a></span>
    </p>
    
    <div class="dashboard-container"> 
        <div class="user"> 
            <img src="../images/user.png" style="width:40px; height:40px;">
            <P><h4>Teacher Dashboard</h4></P> 
            <p><h4><?php echo $_SESSION['user_name']; ?></h4></p>
            <p><h5>ID: <?php echo $_SESSION['user_id']; ?></h5></p>
        </div>

        <div class="navigation">
            <div class="nav">
                <a href="#courses">Course Management</a>
            </div>
            <div class="nav">
                <a href="#session">Session Overview</a>
            </div>
            <div class="nav">
                <a href="#report">Attendance Reports</a>
            </div>
        </div>
    </div>
       
    <div class="list" id="courses"> 
        <p><h3>Course Management</h3></p>

        <div class="cards"> 
            <div class="card" onclick="showCreateCourseForm()">
                <img src="../images/create.png" style="width:40px; height:40px;">
                <h4>Create Course</h4>
            </div>

            <div class="card" onclick="showEditCourseForm()">
                <img src="../images/edit.png" style="width:40px; height:40px;">
                <h4>Edit Course</h4>
            </div>

            <div class="card" onclick="showDeleteCourseForm()">
                <img src="../images/trash.png" style="width:40px; height:40px;">
                <h4>Delete Course</h4>
            </div>

            <div class="card" onclick="viewStatistics()">
                <img src="../images/view.png" style="width:40px; height:40px;">
                <h4>View Statistics</h4>
            </div>
        </div>  

        <!-- Create Course Form -->
        <div id="createCourseForm" style="display: none; margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 10px;">
            <h4>Create New Course</h4>
            <form onsubmit="createCourse(event)">
                <label>Course Name:</label>
                <input type="text" id="course_name" required><br><br>
                
                <label>Semester:</label>
                <input type="text" id="semester" required><br><br>
                
                <label>Cohort:</label>
                <input type="text" id="cohort" required><br><br>
                
                <button type="submit">Create Course</button>
                <button type="button" onclick="hideCreateCourseForm()">Cancel</button>
            </form>
            <div id="courseMessage"></div>
        </div>

        <!-- Edit Course Form -->
        <div id="editCourseForm" style="display: none; margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 10px;">
            <h4>Edit Course</h4>
            <form onsubmit="updateCourse(event)">
                <label>Select Course:</label>
                <select id="edit_course_id" required onchange="loadCourseData()">
                    <option value="">-- Select Course --</option>
                    <?php 
                    $courses_result->data_seek(0);
                    while($course = $courses_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $course['id']; ?>" 
                                data-name="<?php echo $course['course_name']; ?>"
                                data-semester="<?php echo $course['semester']; ?>"
                                data-cohort="<?php echo $course['cohort']; ?>">
                            <?php echo $course['course_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select><br><br>
                
                <label>Course Name:</label>
                <input type="text" id="edit_course_name" required><br><br>
                
                <label>Semester:</label>
                <input type="text" id="edit_semester" required><br><br>
                
                <label>Cohort:</label>
                <input type="text" id="edit_cohort" required><br><br>
                
                <button type="submit">Update Course</button>
                <button type="button" onclick="hideEditCourseForm()">Cancel</button>
            </form>
            <div id="editCourseMessage"></div>
        </div>

        <!-- Delete Course Form -->
        <div id="deleteCourseForm" style="display: none; margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 10px;">
            <h4>Delete Course</h4>
            <form onsubmit="deleteCourse(event)">
                <label>Select Course to Delete:</label>
                <select id="delete_course_id" required>
                    <option value="">-- Select Course --</option>
                    <?php 
                    $courses_result->data_seek(0);
                    while($course = $courses_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $course['id']; ?>">
                            <?php echo $course['course_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select><br><br>
                
                <p style="color: red;"><strong>Warning: This will delete the course and all associated sessions and attendance records!</strong></p>
                
                <button type="submit" style="background-color: #d32f2f;">Delete Course</button>
                <button type="button" onclick="hideDeleteCourseForm()">Cancel</button>
            </form>
            <div id="deleteCourseMessage"></div>
        </div>

        <!-- Statistics View -->
        <div id="statisticsView" style="display: none; margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 10px;">
            <h4>Course Statistics</h4>
            <select id="stats_course_id" onchange="loadCourseStats()">
                <option value="">-- Select Course --</option>
                <?php 
                $courses_result->data_seek(0);
                while($course = $courses_result->fetch_assoc()): 
                ?>
                    <option value="<?php echo $course['id']; ?>">
                        <?php echo $course['course_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <div id="statsContent"></div>
            <button type="button" onclick="hideStatistics()">Close</button>
        </div>

        <!-- Teacher's Courses -->
        <div style="margin-top: 30px;">
            <h4>My Courses</h4>
            <div class="cards">
                <?php 
                $courses_result->data_seek(0);
                if ($courses_result->num_rows > 0): 
                ?>
                    <?php while($course = $courses_result->fetch_assoc()): ?>
                    <div class="card">
                        <img src="../images/icon1.png" style="width:40px; height:40px;">
                        <h4><?php echo $course['course_name']; ?></h4>
                        <p><h4><?php echo $course['semester']; ?></h4></p>
                        <p><h5><?php echo $course['cohort']; ?></h5></p>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No courses found. Create your first course!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="list" id="session"> 
        <p><h3>Sessions</h3></p>

        <?php if ($current_session): ?>
        <div class="session"> 
            <h4>Latest Session</h4> 
            <p>Course: <?php echo $current_session['course_name']; ?></p>
            <p>Date: <?php echo $current_session['session_date']; ?></p>
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

    <script src="../js/teacher.js"></script>
</body>
</html>
<?php $connection->close(); ?>