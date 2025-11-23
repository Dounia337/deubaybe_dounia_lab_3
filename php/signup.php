<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['fname']);
    $last_name = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm'];
    
    error_log("Signup attempt: $first_name $last_name, email: $email, role: $role");
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if user exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $connection->prepare($check_sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "User with this email already exists";
            } else {
                // Create user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_sql = "INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $connection->prepare($insert_sql);
                
                if ($insert_stmt) {
                    $insert_stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);
                    
                    if ($insert_stmt->execute()) {
                        $success = "Account created successfully! You can now login.";
                        error_log("User created successfully: $email");
                    } else {
                        $error = "Error creating account: " . $insert_stmt->error;
                        error_log("Database error: " . $insert_stmt->error);
                    }
                    $insert_stmt->close();
                } else {
                    $error = "Database error: Unable to prepare statement";
                    error_log("Prepare statement failed for insert");
                }
            }
            $stmt->close();
        } else {
            $error = "Database error: Unable to prepare statement";
            error_log("Prepare statement failed for check");
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>  
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Alegreya">
    <link rel="stylesheet" href="../css/signup.css">
    <title>Attendance tracker</title> 
</head>
<body>
    <div class="singin">  
        <p><h4><span><a href="login.php">Login</a></span> or <span><a href="#">Sign up</a></span>?</h4></p>
    </div>

    <?php if (isset($error)): ?>
        <div style="color: red; text-align: center; margin: 20px;"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div style="color: green; text-align: center; margin: 20px;"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="signup.php" id="signupForm">
        <label for="fname">First Name</label>
        <input type="text" id="fname" name="fname" required> 

        <label for="lname">Last Name</label>
        <input type="text" id="lname" name="lname" required ><br><br>

        <label for="email">Email Address:</label> 
        <input type="text" id="email" name="email" required>

        <label for="role">Role:</label> 
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="student">Student</option>
            <option value="teacher">Faculty</option>
            <option value="fI">Faculty Intern</option>
        </select><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm">Confirm password:</label>
        <input type="password" id="confirm" name="confirm" required><br><br>

        <input type="submit" value="Sign Up">
    </form>
</body>
<script src="../js/signup.js">
</script>
</html>
<?php 
$connection->close();
?>