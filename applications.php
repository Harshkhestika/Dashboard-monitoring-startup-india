<?php
// Database connection's
$host = "localhost";
$username = "root";
$password = ""; // update if neededs
$database = "startup_india";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total applications count's
$count_sql = "SELECT COUNT(startup_name) AS total_applications FROM startups";
$count_result = $conn->query($count_sql);
$total_applications = 0;

if ($count_result && $count_result->num_rows > 0) {
    $row = $count_result->fetch_assoc();
    $total_applications = $row['total_applications'];
}

// Fetch all application data
$sql = "SELECT * FROM startups";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications | Startup India</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex flex-col flex-1 overflow-hidden">
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            <div class="flex flex-col space-y-6">
                <!-- Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Applications</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $total_applications; ?></h3>
                                <p class="text-xs text-gray-500 mt-1">12% from last week</p>
                            </div>
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                                <i class="fas fa-clock text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Approved</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1">128</h3>
                                <p class="text-xs text-green-500 mt-1"><i class="fas fa-arrow-up mr-1"></i> 8% from last week</p>
                            </div>
                            <div class="p-3 rounded-full bg-green-100 text-green-500">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                        </div>
                    </div> -->
                    
                    <!-- <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Rejected</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1">18</h3>
                                <p class="text-xs text-red-500 mt-1"><i class="fas fa-arrow-down mr-1"></i> 5% from last week</p>
                            </div>
                            <div class="p-3 rounded-full bg-red-100 text-red-500">
                                <i class="fas fa-times-circle text-xl"></i>
                            </div>
                        </div>
                    </div> -->
                    
                    <!-- <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">In Review</p>
                                <h3 class="text-2xl font-bold text-gray-800 mt-1">32</h3>
                                <p class="text-xs text-blue-500 mt-1"><i class="fas fa-arrow-up mr-1"></i> 15% from last week</p>
                            </div>
                            <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                                <i class="fas fa-search text-xl"></i>
                            </div>
                        </div>
                    </div> -->
                </div>
                
                <!-- Applications Table -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex space-x-4">
                            <button class="px-4 py-2 text-sm font-medium border-b-2 border-primary text-primary">
                                All Applications
                            </button>
                            <!-- <button class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-primary">
                                Pending
                            </button>
                            <button class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-primary">
                                Approved
                            </button>
                            <button class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-primary">
                                Rejected
                            </button> -->
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" placeholder="Search applications..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <button class="flex items-center px-4 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 rounded-lg">
                                <i class="fas fa-filter mr-2"></i> Filter
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Startup Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sector</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Applied</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                // Loop through the results and display each startup
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . $row['startup_name'] . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . $row['sector'] . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . $row['date_applied'] . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . ($row['status'] ?? 'N/A') . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-right'>";
                                        echo "<button class='px-4 py-2 text-sm font-medium bg-blue-500 text-white rounded-lg'>View</button>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='px-6 py-4 text-center text-gray-500'>No applications found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>



