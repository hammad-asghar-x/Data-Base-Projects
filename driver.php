<?php

include 'db_connection.php';
session_start();

if (isset($_SESSION['driver_id'])) {
    $username = $_SESSION['driver_name'];
    $driver_id = $_SESSION['driver_id'];
    $password = $_SESSION['driver_password']; 
    $vehicle_model = $_SESSION['vehicle_model'];
    $vehicle_make = $_SESSION['vehicle_make'];
    $license_plate = $_SESSION['License_Plate'];

    $completedRidesCountSql = "SELECT COUNT(*) as total_completed FROM ride WHERE DriverID = ? AND Status = 'completed'";
    $stmtCompletedCount = $conn->prepare($completedRidesCountSql);

    if ($stmtCompletedCount === false) {
        die('MySQL prepare error: ' . mysqli_error($conn));
    }

    $stmtCompletedCount->bind_param("s", $driver_id); 
    $stmtCompletedCount->execute();
    $result = $stmtCompletedCount->get_result();
    $completedRidesCount = $result->fetch_assoc()['total_completed']; 

    $completedRidesSql = "SELECT * FROM ride WHERE DriverID = ? AND Status = 'completed'";
    $stmtCompleted = $conn->prepare($completedRidesSql);

    if ($stmtCompleted === false) {
        die('MySQL prepare error: ' . mysqli_error($conn));
    }

    $stmtCompleted->bind_param("s", $driver_id); 
    $stmtCompleted->execute();
    $completedRidesResult = $stmtCompleted->get_result();

    $pendingRidesSql = "SELECT * FROM ride WHERE DriverID = ? AND Status = 'in-progress'";
    $stmtPending = $conn->prepare($pendingRidesSql);

    if ($stmtPending === false) {
        die('MySQL prepare error: ' . mysqli_error($conn));
    }

    $stmtPending->bind_param("s", $driver_id); 
    $stmtPending->execute();
    $pendingRidesResult = $stmtPending->get_result();

    $availableRidesSql = "SELECT * FROM ride WHERE DriverID IS NULL AND Status = 'pending'";
    $stmtAvailable = $conn->prepare($availableRidesSql);
    $stmtAvailable->execute();
    $availableRidesResult = $stmtAvailable->get_result();

    if (isset($_POST['select_ride'])) {
        $ride_id = $_POST['ride_id'];


        $checkStatusSql = "SELECT Status, StartTime FROM ride WHERE RideID = ?";
        $stmtCheckStatus = $conn->prepare($checkStatusSql);
        $stmtCheckStatus->bind_param("i", $ride_id);
        $stmtCheckStatus->execute();
        $statusResult = $stmtCheckStatus->get_result()->fetch_assoc();

        if ($statusResult['Status'] == 'pending' && is_null($statusResult['StartTime'])) {
            $updateRideSql = "UPDATE ride SET DriverID = ?, Status = 'in-progress', StartTime = NOW() WHERE RideID = ?";
            $stmtUpdate = $conn->prepare($updateRideSql);
            $stmtUpdate->bind_param("si", $driver_id, $ride_id);
            if ($stmtUpdate->execute()) {
                echo "Ride has been successfully selected and status updated.";
            } else {
                echo "Error: Could not update the ride status.";
            }
        }
    }

    $autoUpdateSql = "UPDATE ride SET Status = 'completed' WHERE Status = 'in-progress' AND TIMESTAMPDIFF(MINUTE, StartTime, NOW()) >= 2";
    $conn->query($autoUpdateSql); 

    $balanceSql = "SELECT SUM(Fare) as balance FROM ride WHERE DriverID = ? AND Status = 'completed'";
    $stmtBalance = $conn->prepare($balanceSql);
    $stmtBalance->bind_param("s", $driver_id);
    $stmtBalance->execute();
    $balanceResult = $stmtBalance->get_result();
    $balance = $balanceResult->fetch_assoc()['balance'] ?? 0; 

} else {
    header("Location: login.php");
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taxi Management System - Menu</title>
    <link rel="stylesheet" href="driver.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="greeting">
              Welcome, <span id="username"><?php echo htmlspecialchars($username); ?></span>! Ready to Pick a ride?
        </div>
        <button class="logout-btn" onclick="window.location.href = 'Home.html';">Logout</button>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <button class="sidebar-btn" onclick="showContent('personal-info')">Personal Info</button>
        <button class="sidebar-btn" onclick="showContent('ride-history')">Ride History</button>
        <button class="sidebar-btn" onclick="showContent('pick-ride')">Pick Rides</button>
    </div>

    <!-- Content Area -->
    <div class="content">
        <div id="personal-info" class="content-section" style="display: block;">
            <div class="section-heading">
                <h2>Personal Information</h2>
            </div>

            <div class="user-info">
                <p><strong>Driver ID:</strong> <?php echo htmlspecialchars($driver_id); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($username); ?></p>
                <p><strong>Password:</strong> <?php echo htmlspecialchars($password); ?></p> 
                <p><strong>Balance:</strong> <?php echo htmlspecialchars($balance); ?></p>
            </div>

            <div class="Vehicle-info">
                <p><strong>Vehicle Make:</strong> <?php echo htmlspecialchars($vehicle_make); ?></p>
                <p><strong>Vehicle Model:</strong> <?php echo htmlspecialchars($vehicle_model); ?></p>
                <p><strong>License Plate:</strong> <?php echo htmlspecialchars($license_plate); ?></p>
            </div>

            <div class="ride-info">
                <p><strong>Total Rides Completed: </strong><?php echo htmlspecialchars($completedRidesCount); ?></p>
            </div>

        </div>

        <div id="ride-history" class="content-section">
            <div class="section-heading">
                <h2>Ride History</h2>
            </div>

            <div class="ride-buttons">
                <button class="ride-btn" onclick="showRideInfo('completed')">Completed Rides</button>
                <button class="ride-btn" onclick="showRideInfo('pending')">Pending Rides</button>
            </div>

            <div id="completed-rides" class="ride-info" >
                <table class="ride-table">
                    <thead>
                        <tr>
                            <th>Ride ID</th>
                            <th>Driver ID</th>
                            <th>Start Location</th>
                            <th>End Location</th>
                            <th>Distance (km)</th>
                            <th>Fare</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if ($completedRidesResult->num_rows > 0) {
                                while ($row = $completedRidesResult->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['RideID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['DriverID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['StartLocation']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['EndLocation']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['distant']) . "</td>";
                                    echo "<td>$" . htmlspecialchars($row['Fare']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                            echo "<tr><td colspan='8'>No completed rides found.</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div id="pending-rides" class="ride-info" style="display: none;">
                <table class="ride-table">
                    <thead>
                        <tr>
                            <th>Ride ID</th>
                            <th>Driver ID</th>
                            <th>Start Location</th>
                            <th>End Location</th>
                            <th>Distance (km)</th>
                            <th>Fare</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if ($pendingRidesResult->num_rows > 0) {
                                while ($row = $pendingRidesResult->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['RideID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['DriverID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['StartLocation']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['EndLocation']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['distant']) . "</td>";
                                    echo "<td>$" . htmlspecialchars($row['Fare']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No pending rides found.</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="pick-ride" class="content-section" >
            <div class="section-heading">
                <h2>Pick a Ride</h2>
            </div>
    
            <div id="available-rides" class="ride-info">
                <table class="ride-table">
                    <thead>
                        <tr>
                            <th>Ride ID</th>
                            <th>Passenger ID</th>
                            <th>Start Location</th>
                            <th>End Location</th>
                            <th>Fare</th>
                            <th>Select</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if ($availableRidesResult->num_rows > 0) {
                                while ($row = $availableRidesResult->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['RideID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['UserID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['StartLocation']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['EndLocation']) . "</td>";
                                    echo "<td>$" . htmlspecialchars($row['Fare']) . "</td>";
                                    echo "<td>
                                            <form method='POST'>
                                                <input type='hidden' name='ride_id' value='" . htmlspecialchars($row['RideID']) . "'>
                                                <button class='pick-button' name='select_ride'>Select</button>
                                            </form>
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                 echo "<tr><td colspan='6'>No available rides at the moment.</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <script src="driver.js"></script>
     
</body>

</html>
