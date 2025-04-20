<?php
session_start();
// DB connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'startup_india';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Query to check user
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Password correct, set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];

            $notification_title = "User Login";
$notification_message = "User " . $_SESSION['user_name'] . " has logged in";
$notification_sql = "INSERT INTO notifications (title, message, type, is_read, created_at) 
                     VALUES (?, ?, 'login', 0, NOW())";

$stmt = $conn->prepare($notification_sql);
$stmt->bind_param("ss", $notification_title, $notification_message);
$stmt->execute();
$stmt->close();
            
            // Redirect to index.php
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with this email.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Startup India</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-bg {
            background: linear-gradient(135deg, rgba(237, 137, 54, 0.1) 0%, rgba(66, 153, 225, 0.1) 100%);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Left Column - Illustration -->
        <div class="md:w-1/2 auth-bg flex items-center justify-center p-12">
            <div class="max-w-md text-center">
                <img src="india.png" class="h-55 w-100 mx-auto mb-6" alt="Startup India Logo">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">Startup India</h1>
                <p class="text-xl text-gray-600">Empowering the next generation of entrepreneurs</p>
            </div>
        </div>

        <!-- Right Column - Form -->
        <div class="md:w-1/2 flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <div class="bg-white p-8 rounded-2xl shadow-lg">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back</h2>
                        <p class="text-gray-500">Sign in to manage your startup profile</p>
                    </div>

                    <form action="login.php" method="POST" class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input id="email" name="email" type="email" autocomplete="email" required class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="you@example.com">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input id="password" name="password" type="password" autocomplete="current-password" required class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="••••••••">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="togglePasswordVisibility()" aria-label="Show/Hide Password">
                                        <i class="fas fa-eye" id="toggleEye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300 rounded">
                                <label for="remember-me" class="ml-2 block text-sm text-gray-700">Remember me</label>
                            </div>
                            <div class="text-sm">
                                <a href="#" class="font-medium text-orange-600 hover:text-orange-700">Forgot password?</a>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-black bg-yellow-400 hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Log In
                            </button>
                        </div>
                    </form>

                    <div class="mt-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-gray-500">
                                    Or continue with
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-3 gap-3">
                            <div>
                                <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fab fa-google text-red-500"></i>
                                </a>
                            </div>
                            <div>
                                <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fab fa-linkedin-in text-blue-600"></i>
                                </a>
                            </div>
                            <div>
                                <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fab fa-twitter text-blue-400"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Don't have an account? 
                            <a href="signup.php" class="font-medium text-primary hover:text-orange-700">
                                Sign up
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.getElementById("toggleEye");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>