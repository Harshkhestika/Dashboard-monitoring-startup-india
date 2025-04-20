<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// DB connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'startup_india';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$success_message = '';
$error_message = '';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $target_dir = "uploads/profile_pics/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $new_filename = "user_" . $_SESSION['user_id'] . "." . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is a actual image
    $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
    if ($check === false) {
        $error_message = "File is not an image.";
    } 
    // Check file size (max 2MB)
    elseif ($_FILES["profile_pic"]["size"] > 2000000) {
        $error_message = "Sorry, your file is too large (max 2MB).";
    }
    // Allow certain file formats
    elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }
    // Try to upload file
    elseif (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
        // Update database
        $stmt = $conn->prepare("UPDATE user SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $target_file, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['profile_pic'] = $target_file;
            $success_message = "Profile picture updated successfully!";
        } else {
            $error_message = "Error updating profile picture in database.";
        }
        $stmt->close();
    } else {
        $error_message = "Sorry, there was an error uploading your file.";
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($conn->real_escape_string($_POST['first_name']));
    $last_name = trim($conn->real_escape_string($_POST['last_name']));
    $email = trim($conn->real_escape_string($_POST['email']));
    
    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error_message = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
        $stmt = $conn->prepare("UPDATE user SET first_name=?, last_name=?, email=? WHERE id=?");
        $stmt->bind_param("sssi", $first_name, $last_name, $email, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Update session data
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $_SESSION['user_email'] = $email;
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password strength
    $password_strength = validatePasswordStrength($new_password);
    if ($password_strength !== true) {
        $error_message = $password_strength;
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords don't match!";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM user WHERE id=?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE user SET password=? WHERE id=?");
            $update_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($update_stmt->execute()) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Error changing password: " . $conn->error;
            }
            $update_stmt->close();
        } else {
            $error_message = "Current password is incorrect!";
        }
        $stmt->close();
    }
}

// Password strength validation function
function validatePasswordStrength($password) {
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long!";
    }
    if (!preg_match("#[0-9]+#", $password)) {
        return "Password must include at least one number!";
    }
    if (!preg_match("#[a-zA-Z]+#", $password)) {
        return "Password must include at least one letter!";
    }
    if (!preg_match("#[^a-zA-Z0-9]+#", $password)) {
        return "Password must include at least one special character!";
    }
    return true;
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Set page title
$page_title = "User Profile";

// Get user data - ensure created_at is selected
// $stmt = $conn->prepare("SELECT id, first_name, last_name, email, profile_pic, created_at FROM user WHERE id = ?");
// $stmt->bind_param("i", $_SESSION['user_id']);
// $stmt->execute();
// $result = $stmt->get_result();
// $user = $result->fetch_assoc();
// $stmt->close();

// // Set default for created_at if not present
// if (!isset($user['created_at']) || empty($user['created_at'])) {
//     $user['created_at'] = date('Y-m-d H:i:s'); // Current time as default
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | Startup India</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <style>
        .profile-pic-upload {
            position: relative;
            display: inline-block;
        }
        .profile-pic-upload input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
        }
        .transition-all {
            transition: all 0.3s ease;
        }
        .password-strength-meter {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            background-color: #e0e0e0;
        }
        .password-strength-meter-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include('sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto ml-0 md:ml-64 bg-gray-100">
            <!-- Header -->
            <?php include('header.php'); ?>

            <!-- Profile Content -->
            <div class="p-6">
                <!-- Success/Error Messages -->
                <?php if ($success_message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline"><?php echo $success_message; ?></span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                            <i class="fas fa-times cursor-pointer"></i>
                        </span>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline"><?php echo $error_message; ?></span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                            <i class="fas fa-times cursor-pointer"></i>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">User Profile</h2>
                    <div class="mt-4 md:mt-0">
                        <button onclick="window.print()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-print mr-2"></i> Print Profile
                        </button>
                    </div>
                </div>

                <!-- Profile Card -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6 transition-all hover:shadow-md">
                    <div class="flex flex-col md:flex-row items-center">
                        <form method="POST" action="profile.php" enctype="multipart/form-data" class="profile-pic-upload mb-4 md:mb-0 md:mr-6">
                            <img class="h-32 w-32 rounded-full object-cover shadow-md" src="<?php echo !empty($user['profile_pic']) ? $user['profile_pic'] : 'https://randomuser.me/api/portraits/men/32.jpg'; ?>" alt="User Profile" id="profile-pic-preview">
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*" onchange="previewImage(this)">
                            <button type="button" class="absolute bottom-2 right-2 bg-gray-800 text-white p-2 rounded-full hover:bg-gray-700 transition-all" onclick="document.getElementById('profile_pic').click()">
                                <i class="fas fa-camera"></i>
                            </button>
                        </form>
                        <div class="text-center md:text-left">
                            <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <p class="text-sm text-gray-500 mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                            <div class="flex justify-center md:justify-start space-x-2">
    <!-- <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
        Member Since: <?php echo date('M Y', strtotime($user['created_at'])); ?>
    </span> -->
    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Personal Information Form -->
                    <div class="bg-white rounded-lg shadow-sm p-6 transition-all hover:shadow-md">
                        <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user-circle mr-2 text-blue-500"></i> Personal Information
                        </h3>
                        <form method="POST" action="profile.php">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name*</label>
                                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name*</label>
                                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address*</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <button type="submit" name="update_profile" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center justify-center transition-all">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </form>
                    </div>

                    <!-- Change Password Form -->
                    <div class="bg-white rounded-lg shadow-sm p-6 transition-all hover:shadow-md">
                        <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-key mr-2 text-orange-500"></i> Change Password
                        </h3>
                        <form method="POST" action="profile.php">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password*</label>
                                <div class="relative">
                                    <input type="password" name="current_password" placeholder="••••••••" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 pl-10 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <i class="fas fa-lock absolute left-3 top-3 text-gray-400"></i>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Password*</label>
                                <div class="relative">
                                    <input type="password" id="new_password" name="new_password" placeholder="••••••••" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 pl-10 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                        oninput="checkPasswordStrength(this.value)">
                                    <i class="fas fa-key absolute left-3 top-3 text-gray-400"></i>
                                </div>
                                <div class="password-strength-meter mt-2">
                                    <div class="password-strength-meter-fill" id="password-strength-meter-fill"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1" id="password-strength-text"></div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password*</label>
                                <div class="relative">
                                    <input type="password" name="confirm_password" placeholder="••••••••" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 pl-10 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <i class="fas fa-key absolute left-3 top-3 text-gray-400"></i>
                                </div>
                            </div>
                            <button type="submit" name="change_password" class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg flex items-center justify-center transition-all">
                                <i class="fas fa-key mr-2"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Profile picture upload preview
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-pic-preview').src = e.target.result;
                    // Auto-submit the form when image is selected
                    input.form.submit();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Password strength meter
        function checkPasswordStrength(password) {
            const strengthMeter = document.getElementById('password-strength-meter-fill');
            const strengthText = document.getElementById('password-strength-text');
            
            // Reset
            strengthMeter.style.width = '0%';
            strengthMeter.style.backgroundColor = '#e0e0e0';
            strengthText.textContent = '';
            
            if (password.length === 0) return;
            
            // Calculate strength
            let strength = 0;
            let messages = [];
            
            // Length
            if (password.length > 7) strength += 1;
            if (password.length > 11) strength += 1;
            
            // Contains numbers
            if (password.match(/\d+/)) strength += 1;
            
            // Contains lowercase and uppercase
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
            
            // Contains special chars
            if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
            
            // Update UI
            let width = strength * 20;
            let color = '#ef4444'; // red
            let text = 'Weak';
            
            if (strength >= 4) {
                color = '#10b981'; // green
                text = 'Strong';
            } else if (strength >= 2) {
                color = '#f59e0b'; // yellow
                text = 'Medium';
            }
            
            strengthMeter.style.width = width + '%';
            strengthMeter.style.backgroundColor = color;
            strengthText.textContent = 'Strength: ' + text;
            strengthText.style.color = color;
        }
    </script>
</body>
</html>