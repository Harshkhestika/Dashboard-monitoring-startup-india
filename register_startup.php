<?php
// Database connection
$host = "localhost";
$username = "root";
$password = ""; // Update if needed
$database = "startup_india";

// Establishing the connection
$conn = new mysqli($host, $username, $password, $database);

// Checking the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form values
    $startup_name = $_POST['startup_name'] ?? '';
    $sector = $_POST['sector'] ?? '';
    $date_applied = $_POST['date_applied'] ?? '';
    $revenue = $_POST['revenue'] ?? null;
    $job = $_POST['job'] ?? null;
    $email = $_POST['email'] ?? '';

    // Handle file upload
    $business_plan = $_FILES['business_plan']['name'] ?? '';
    $target_dir = "uploads/"; // Directory to save uploaded files
    $target_file = $target_dir . basename($business_plan); // Path where file will be saved

    // Check if the file is uploaded
    if (!empty($business_plan)) {
        if (!move_uploaded_file($_FILES["business_plan"]["tmp_name"], $target_file)) {
            die("Error uploading file.");
        }
    }

    // Prepare the SQL query to insert data
    $sql = "INSERT INTO startups (startup_name, sector, date_applied, revenue, job, email, business_plan)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters
        $stmt->bind_param("sssiiss", $startup_name, $sector, $date_applied, $revenue, $job, $email, $business_plan);

        // Execute the statement and check if it's successful
        if ($stmt->execute()) {
            // Redirect to index.php with success message using JavaScript
            echo "<script type='text/javascript'>
                    alert('Startup registered successfully!');
                    window.location.href = 'index.php';
                  </script>";
        } else {
            // Redirect to index.php with error message using JavaScript
            echo "<script type='text/javascript'>
                    alert('Error: " . addslashes($stmt->error) . "');
                    window.location.href = 'index.php';
                  </script>";
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        echo "Error preparing the query: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>