<?php
session_start();

// If user is already logged in, redirect to appropriate dashboard

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'student':
            header("Location: student_dashboard.php");
            break;
        case 'teacher':
            header("Location: teacher_dashboard.php");
            break;
        case 'fI':
            header("Location: faculty_intern_dashboard.php");
            break;
        default:
            // If role is not recognized, show the index page
            break;
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Attendance Management System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Alegreya">
    <link rel="stylesheet" href="../css/index.css">

</head>
<body>
    <div class="welcome-container">
        <div class="logo">
            <img src="../images/user.png" alt="Attendance System Logo">
            <h1>Attendance System</h1>
        </div>
        

        <div class="features">
            <div class="feature">
                <div class="feature-text">
                    <h4>Attendance Tracking</h4>
                    <p>Real-time attendance monitoring</p>
                </div>
            </div>
            <div class="feature">
                <div class="feature-text">
                    <h4>Course Management</h4>
                    <p>Create and manage courses</p>
                </div>
            </div>
            <div class="feature">
                <div class="feature-text">
                    <h4>Student Portal</h4>
                    <p>View attendance and reports</p>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="login.php" class="btn btn-primary">Login to Your Account</a>
            <a href="signup.php" class="btn btn-secondary">Create New Account</a>
        </div>
    </div>
</body>
</html>