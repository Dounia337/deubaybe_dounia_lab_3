<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    error_log("Login attempt: $email");
    
    // Try prepared statement first
    $stmt = $connection->prepare("SELECT id, first_name, last_name, email, password_hash, role FROM users WHERE email = ?");
    
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            error_log("User found: " . $user['email']);
            
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                
                error_log("Login successful for: " . $user['email']);
                
                // Redirect based on role
                switch ($user['role']) {

                    case 'student':
                        header("Location: student_dashboard.php");
                        break;
                    case 'teacher':
                        header("Location: teacher_dashboard.php");
                        break;
                    case 'fI':
                        header("Location: faculty_intern_dashboard.php");
                        break;
                }
                exit();
            } else {
                $error = "Invalid password";
                error_log("Password verification failed for: $email");
            }
        } else {

            $error = "User not found";
            error_log("User not found: $email");
            
        }
        $stmt->close();
    } else {
        $error = "Database error";
        error_log("Prepare statement failed in login");
    }
}
?>

<!DOCTYPE html>
<html>
<head>  
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Alegreya">
    <link rel="stylesheet" href="../css/login.css">
    <title>Attendance tracker</title> 
</head>
<body>
    <div class="singin">  
        <p><h4><span><a href="login.php">Login</a></span> or <span><a href="signup.php">Sign up</a></span>?</h4></p>
    </div>

    <?php if (isset($error)): ?>
        <div style="color: red; text-align: center; margin: 20px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email Address:</label>
        <input type="text" id="email" name="email" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Log In">
    </form>

</body>

<script>
const form = document.querySelector("form");

form.addEventListener("submit", function(event) {
    const email = document.getElementById("email");
    const password = document.getElementById("password");

    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-z]{2,}$/;
    const passwordPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;

    if (!emailPattern.test(email.value)) {
        alert("Please enter a valid email address.");
        event.preventDefault();
        return;
    }

    if (!passwordPattern.test(password.value)) {
        alert("Password must contain one uppercase, one lowercase, one number, and be at least 8 characters long.");
        event.preventDefault();
        return;
    }
});
</script>
</html>
<?php 
$connection->close();
?>