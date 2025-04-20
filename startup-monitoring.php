<?php
// Start session and check if admin is logged in
session_start();
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: index.php');
//     exit();
// }

// Database connection
require_once 'db_connection.php';

// First, let's check what columns actually exist in the startups table
try {
    $stmt = $conn->query("DESCRIBE startups");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error checking table structure: " . $e->getMessage());
}

// Fetch startup data based on available columns
try {
    // Get total startups count
    $totalStartups = $conn->query("SELECT COUNT(*) FROM startups")->fetchColumn();
    
    // Initialize variables with defaults
    $totalFunding = 0;
    $totalFundingFormatted = '₹0';
    $sectorData = [];
    $growthData = [];
    $topStartups = [];

    // Check and calculate funding if revenue column exists
    if (in_array('revenue', $columns)) {
        $totalFunding = $conn->query("SELECT COALESCE(SUM(revenue), 0) FROM startups")->fetchColumn();
        $totalFundingFormatted = '₹' . number_format($totalFunding / 100000, 2) . 'L'; // Format in lakhs
    }

    // Get sector distribution if sector column exists
    if (in_array('sector', $columns)) {
        $sectorData = $conn->query("SELECT sector, COUNT(*) as count FROM startups GROUP BY sector")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // If no sector column, create dummy data
        $sectorData = [
            ['sector' => 'Technology', 'count' => $totalStartups]
        ];
    }

    // Get growth trend data - use any date column that exists
    $dateColumn = null;
    foreach (['created_at', 'registration_date', 'date_created'] as $col) {
        if (in_array($col, $columns)) {
            $dateColumn = $col;
            break;
        }
    }

    if ($dateColumn) {
        $growthData = $conn->query("
            SELECT 
                DATE_FORMAT($dateColumn, '%b') as month,
                COUNT(*) as count
            FROM startups
            WHERE $dateColumn >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY MONTH($dateColumn), YEAR($dateColumn)
            ORDER BY YEAR($dateColumn), MONTH($dateColumn)
        ")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // If no date column exists, create dummy data
        $growthData = [
            ['month' => date('M', strtotime('-5 months')), 'count' => round($totalStartups * 0.2)],
            ['month' => date('M', strtotime('-4 months')), 'count' => round($totalStartups * 0.4)],
            ['month' => date('M', strtotime('-3 months')), 'count' => round($totalStartups * 0.6)],
            ['month' => date('M', strtotime('-2 months')), 'count' => round($totalStartups * 0.8)],
            ['month' => date('M', strtotime('-1 month')), 'count' => $totalStartups]
        ];
    }

    // Get top performing startups - adjust fields based on what exists
    $selectFields = [];
    if (in_array('startup_name', $columns)) {
        $selectFields[] = 'startup_name as name';
    } elseif (in_array('company_name', $columns)) {
        $selectFields[] = 'company_name as name';
    }
    
    if (in_array('sector', $columns)) {
        $selectFields[] = 'sector';
    }
    
    if (in_array('revenue', $columns)) {
        $selectFields[] = 'revenue';
    }
    
    if (!empty($selectFields)) {
        $topStartups = $conn->query("
            SELECT " . implode(', ', $selectFields) . "
            FROM startups 
            " . (in_array('revenue', $columns) ? "ORDER BY revenue DESC" : "") . "
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Startup Monitoring | Startup India</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 font-sans flex h-screen">
    <!-- Sidebar would be included here -->
    <div class="w-64 bg-white shadow-md">
    <?php include 'sidebar.php'; ?>
    </div>
    <div class="flex-1 overflow-hidden">
        <?php include 'header.php'; ?>
        <!-- Header would be included here -->

        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            <div class="flex flex-col space-y-6">
                <!-- Stats Cards -->
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 - Total Startups -->
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Startups</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= number_format($totalStartups) ?></h3>
                            <p class="text-xs text-green-500 mt-1">
                                <i class="fas fa-arrow-up mr-1"></i> 
                                <?= $totalStartups > 0 ? round(($totalStartups / max(1, $totalStartups - 10)) * 100 ): 0 ?>% growth
                            </p>
                        </div>
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                            <i class="fas fa-rocket text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Card 2 - Funding Raised -->
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Funding Raised</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= $totalFundingFormatted ?></h3>
                            <p class="text-xs text-green-500 mt-1">
                                <i class="fas fa-arrow-up mr-1"></i> 
                                <?= $totalFunding > 0 ? round(($totalFunding / max(1, $totalFunding - 10000000)) * 100 ): 0 ?>% growth
                            </p>
                        </div>
                        <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                            <i class="fas fa-rupee-sign text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Card 3 - New Metric (e.g., Sectors) -->
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Sectors</p>
                            <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= count($sectorData) ?></h3>
                            <p class="text-xs text-green-500 mt-1">
                                <i class="fas fa-chart-pie mr-1"></i> 
                                Diverse industries
                            </p>
                        </div>
                        <div class="p-3 rounded-full bg-green-100 text-green-500">
                            <i class="fas fa-chart-pie text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Growth Chart -->
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Startup Growth Trend</h3>
                            <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option>Last 6 Months</option>
                                <option>Last Year</option>
                                <option>All Time</option>
                            </select>
                        </div>
                        <div class="h-64">
                            <canvas id="growthChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Sector Distribution -->
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Sector Distribution</h3>
                            <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option>By Count</option>
                                <option>By Funding</option>
                            </select>
                        </div>
                        <div class="h-64">
                            <canvas id="sectorChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize charts with real data
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare growth chart data
            const growthMonths = <?= json_encode(array_column($growthData, 'month')) ?>;
            const growthCounts = <?= json_encode(array_column($growthData, 'count')) ?>;
            
            // Prepare sector chart data
            const sectorLabels = <?= json_encode(array_column($sectorData, 'sector')) ?>;
            const sectorCounts = <?= json_encode(array_column($sectorData, 'count')) ?>;

            // Growth Chart
            const growthCtx = document.getElementById('growthChart').getContext('2d');
            new Chart(growthCtx, {
                type: 'line',
                data: {
                    labels: growthMonths,
                    datasets: [{
                        label: 'Startups Registered',
                        data: growthCounts,
                        borderColor: '#ED8936',
                        backgroundColor: 'rgba(237, 137, 54, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Sector Chart
            const sectorCtx = document.getElementById('sectorChart').getContext('2d');
            new Chart(sectorCtx, {
                type: 'doughnut',
                data: {
                    labels: sectorLabels,
                    datasets: [{
                        data: sectorCounts,
                        backgroundColor: [
                            '#ED8936', '#4299E1', '#48BB78', '#9F7AEA', '#F56565',
                            '#667EEA', '#F6AD55', '#68D391', '#F687B3', '#805AD5'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%'
                }
            });
        });
    </script>
</body>
</html>