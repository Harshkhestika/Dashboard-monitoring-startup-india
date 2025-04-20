<?php
// PHPMailer namespaces should be at the top
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load the PHPMailer files manually
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// DB connection
$conn = new mysqli("localhost", "root", "", "startup_india");
$success = $error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST['first-name']);
    $last_name = trim($_POST['last-name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);
            if ($stmt->execute()) {
                $success = "Account created successfully!";
                
                // Now send the welcome email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';  // Replace with your SMTP server
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'hkhestika@gmail.com';  // Replace with your email address
                    $mail->Password   = 'erijsrkrkdeyzqbr';  // Replace with your email password
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port       = 465;

                    // Recipients
                    $mail->setFrom('hkhestika@gmail.com', 'Startup India'); // Sender email and name
                    $mail->addAddress($email, $first_name . ' ' . $last_name);  // Recipient email

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Welcome to Startup India!";
                    $mail->Body    = "
                        <h2>🎉 Congratulations, $first_name!</h2>
                        <p>Welcome to Startup India! We're excited to have you on board.</p>
                        <p>Your account has been successfully created with the email: <strong>$email</strong>.</p>
                        <p>If you have any questions or need help, feel free to reach out to us anytime.</p>
                        <p>Cheers,<br>The Startup India Team</p>
                    ";

                    // Send the email
                    $mail->send();
                } catch (Exception $e) {
                    $error = "Error sending email: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up | Startup India</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Left Column -->
        <div class="md:w-1/2 bg-gradient-to-br from-orange-50 to-blue-50 flex items-center justify-center p-12">
            <div class="max-w-md text-center">
                <img src="india.png" class="h-55 w-100 mx-auto mb-6" alt="Startup India Logo">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">Join Startup India</h1>
                <p class="text-xl text-gray-600">Be part of India's growing startup ecosystem</p>
            </div>
        </div>

        <!-- Right Column -->
        <div class="md:w-1/2 flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <div class="bg-white p-8 rounded-2xl shadow-lg">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h2>
                        <p class="text-gray-500">Get started with your startup journey</p>
                    </div>

                    <?php if ($success): ?>
                        <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-center">
                            <?= $success ?>
                        </div>
                    <?php elseif ($error): ?>
                        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form class="space-y-6" method="POST" action="">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input name="first-name" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="John">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input name="last-name" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="Doe">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input name="email" type="email" required class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="you@example.com">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input name="password" type="password" required class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="••••••••">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input name="confirm-password" type="password" required class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="terms" type="checkbox" required class="h-4 w-4 text-primary border-gray-300 rounded">
                            <label for="terms" class="ml-2 text-sm text-gray-700">
                                I agree to the <a href="#" class="font-medium text-primary hover:text-orange-700">Terms</a> and <a href="#" class="font-medium text-primary hover:text-orange-700">Privacy Policy</a>
                            </label>
                        </div>

                        <div>
                            <button type="submit" class="w-full py-3 px-4 rounded-lg shadow-sm text-sm font-medium text-black bg-yellow-400 hover:bg-yellow-500">
                                Create Account
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Already have an account? 
                            <a href="login.php" class="font-medium text-primary hover:text-orange-700">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
