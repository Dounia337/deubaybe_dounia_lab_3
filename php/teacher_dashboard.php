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

// Fetch pending enrollments for teacher's courses
$pending_enrollments_query = "SELECT e.*, u.first_name, u.last_name, u.email, c.course_name
                               FROM enrollments e
                               JOIN users u ON e.student_id = u.id
                               JOIN courses c ON e.course_id = c.id
                               WHERE e.status = 'pending' AND c.created_by = $teacher_id
                               ORDER BY e.enrolled_at DESC";
$pending_enrollments_result = $connection->query($pending_enrollments_query);

// Fetch all Faculty Interns
$fi_query = "SELECT id, first_name, last_name, email FROM users WHERE role = 'fI'";
$fi_result = $connection->query($fi_query);

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
                <a href="#assistants">Manage FI</a>
            </div>
            <div class="nav">
                <a href="#enrollments">Pending Enrollments 
                    <?php if ($pending_enrollments_result->num_rows > 0): ?>
                        <span style="background: red; color: white; padding: 2px 6px; border-radius: 10px; font-size: 12px;">
                            <?php echo $pending_enrollments_result->num_rows; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="nav">
                <a href="#session">Session Overview</a>
            </div>
            <div class="nav">
                <a href="#report">Reports</a>
            </div>
        </div>
    </div>
       
    <div class="list" id="courses"> 
        <p><h3>Course Management</h3></p>

        <div class="cards"> 
            <div class="card" onclick="showCreateCourseForm()" style="cursor: pointer;">
                <img src="../images/create.png" style="width:40px; height:40px;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23bb2929%22 stroke-width=%222%22%3E%3Cpath d=%22M12 5v14M5 12h14%22/%3E%3C/svg%3E';">
                <h4>Create Course</h4>
            </div>

            <div class="card" onclick="showEditCourseForm()" style="cursor: pointer;">
                <img src="../images/edit.png" style="width:40px; height:40px;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23bb2929%22 stroke-width=%222%22%3E%3Cpath d=%22M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7%22/%3E%3Cpath d=%22M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z%22/%3E%3C/svg%3E';">
                <h4>Edit Course</h4>
            </div>

            <div class="card" onclick="showDeleteCourseForm()" style="cursor: pointer;">
                <img src="../images/trash.png" style="width:40px; height:40px;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23bb2929%22 stroke-width=%222%22%3E%3Cpolyline points=%223 6 5 6 21 6%22/%3E%3Cpath d=%22M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2%22/%3E%3C/svg%3E';">
                <h4>Delete Course</h4>
            </div>

            <div class="card" onclick="viewStatistics()" style="cursor: pointer;">
                <img src="../images/view.png" style="width:40px; height:40px;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23bb2929%22 stroke-width=%222%22%3E%3Cpath d=%22M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z%22/%3E%3Ccircle cx=%2212%22 cy=%2212%22 r=%223%22/%3E%3C/svg%3E';">
                <h4>View Statistics</h4>
            </div>

            <div class="card" onclick="showManageFISection()" style="cursor: pointer; background-color: #e3d1cb;">
                <img src="../images/user.png" style="width:40px; height:40px;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23bb2929%22 stroke-width=%222%22%3E%3Cpath d=%22M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2%22/%3E%3Ccircle cx=%229%22 cy=%227%22 r=%224%22/%3E%3Cpath d=%22M23 21v-2a4 4 0 0 0-3-3.87%22/%3E%3Cpath d=%22M16 3.13a4 4 0 0 1 0 7.75%22/%3E%3C/svg%3E';">
                <h4>Manage FI</h4>
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
                
                <p style="color: red;"><strong>Warning: This will delete the course and all associated data!</strong></p>
                
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

    <!-- Manage Faculty Interns Section -->
    <div class="list" id="assistants">
        <div id="manageFISection" style="display: none;">
            <p><h3>Manage Faculty Interns</h3></p>
            
            <div style="margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 10px; max-width: 600px;">
                <h4>Assign Faculty Intern to Course</h4>
                <form onsubmit="assignFI(event)">
                    <label>Select Course:</label>
                    <select id="assign_course_id" required>
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
                    
                    <label>Select Faculty Intern:</label>
                    <select id="assign_fi_id" required>
                        <option value="">-- Select FI --</option>
                        <?php while($fi = $fi_result->fetch_assoc()): ?>
                            <option value="<?php echo $fi['id']; ?>">
                                <?php echo $fi['first_name'] . ' ' . $fi['last_name'] . ' (' . $fi['email'] . ')'; ?>
                            </option>
                        <?php endwhile; ?>
                    </select><br><br>
                    
                    <button type="submit">Assign FI</button>
                </form>
                <div id="assignMessage"></div>
            </div>

            <!-- View Current Assignments -->
            <div style="margin-top: 30px;">
                <h4>Current FI Assignments</h4>
                <div id="fiAssignmentsList"></div>
                <button onclick="loadFIAssignments()">Refresh Assignments</button>
            </div>
        </div>
    </div>

    <!-- Pending Enrollments Section -->
    <div class="list" id="enrollments">
        <p><h3>Pending Enrollments</h3></p>
        
        <?php if ($pending_enrollments_result->num_rows > 0): ?>
        <div>
            <table>
                <tr>
                    <th style="color: rgb(172, 80, 80);">Student Name</th>
                    <th style="color: rgb(172, 80, 80);">Email</th>
                    <th style="color: rgb(172, 80, 80);">Course</th>
                    <th style="color: rgb(172, 80, 80);">Requested</th>
                    <th style="color: rgb(172, 80, 80);">Actions</th>
                </tr>
                <?php while($enrollment = $pending_enrollments_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $enrollment['first_name'] . ' ' . $enrollment['last_name']; ?></td>
                    <td><?php echo $enrollment['email']; ?></td>
                    <td><?php echo $enrollment['course_name']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                    <td>
                        <button onclick="approveEnrollment(<?php echo $enrollment['id']; ?>)" style="background: green; margin-right: 5px;">Approve</button>
                        <button onclick="rejectEnrollment(<?php echo $enrollment['id']; ?>)" style="background: red;">Reject</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <?php else: ?>
        <p>No pending enrollment requests.</p>
        <?php endif; ?>
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
        $enrolled_students_query = "SELECT u.id, u.first_name, u.last_name 
                                    FROM users u
                                    JOIN enrollments e ON u.id = e.student_id
                                    WHERE e.course_id = {$current_session['course_id']}
                                    AND e.status = 'approved'
                                    AND u.id NOT IN (
                                        SELECT student_id FROM attendance WHERE session_id = {$current_session['id']}
                                    )
                                    ORDER BY u.last_name, u.first_name";
        $absent_students_result = $connection->query($enrolled_students_query);
        ?>

        <h4 style="margin-top: 30px; color: rgb(187, 41, 41);">Students Absent</h4>
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
        <div style="margin-bottom: 20px;">
            <label><strong>Select Course:</strong></label>
            <select id="report_course_id" onchange="loadCourseReport()">
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
        </div>
        <div id="reportContent">
            <p>Please select a course to view reports.</p>
        </div>
    </div>

    <script src="../js/teacher.js"></script>
    <script>
        // Load FI assignments on page load
        window.addEventListener('load', loadFIAssignments);
    </script>
</body>
</html>
<?php $connection->close(); ?>