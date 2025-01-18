<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) 
{
    header('Location: login.php'); 
    exit();
}

$user_id = $_SESSION['user_id'];  
$query = "SELECT * FROM User WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) 
{
    $user = $result->fetch_assoc();
    $username = $user['Name'];
    $phone = $user['PhoneNumber'];
    $email = $user['Email'];
    $password = $user['Password'];
} 
else 
{
    echo 'User not found.';
    exit();
}

$countQuery = "SELECT COUNT(*) AS total_rides FROM Ride WHERE UserID = ?";
$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param('i', $user_id);  
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalRides = $countRow['total_rides'];

$completedQuery = "SELECT * FROM Ride WHERE UserID = ? AND Status = 'Completed' AND DriverID IS NOT NULL";
$completedStmt = $conn->prepare($completedQuery);
$completedStmt->bind_param('i', $user_id);
$completedStmt->execute();
$completedResult = $completedStmt->get_result();

$pendingQuery = "SELECT * FROM Ride WHERE UserID = ? AND Status = 'pending' AND DriverID IS NULL";
$pendingStmt = $conn->prepare($pendingQuery);
$pendingStmt->bind_param('i', $user_id);
$pendingStmt->execute();
$pendingResult = $pendingStmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $start_location = $_POST['start-location'] ?? '';
    $end_location = $_POST['end-location'] ?? '';
    $distant = $_POST['distant'] ?? 0; 
    if (empty($start_location) || empty($end_location) || $distant <= 0) 
    {
        echo "<p>Error: Invalid input data.</p>";
        exit();
    }

    $fare = $distant * 30;

    $driver_id = NULL; 
    $status = "Pending";

    $insertQuery = "INSERT INTO Ride (UserID, DriverID, StartLocation, EndLocation, Fare, Status, Distant) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) 
    {
        echo "<p>SQL Error: " . $conn->error . "</p>";
        exit();
    }

    $stmt->bind_param("iissdsi", $user_id, $driver_id, $start_location, $end_location, $fare, $status, $distant); 

    if ($stmt->execute()) 
    {
        header("Location: passenger.php");
        exit();
    } 
    else 
    {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

$stmt->close();
$countStmt->close();
$completedStmt->close();
$pendingStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taxi Management System - Menu</title>
    <link rel="stylesheet" href="passenger.css">
</head>

<body>
    <nav class="navbar">
        <div class="greeting">
            Welcome, <span id="username">
                <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?>
            </span>! Ready to book a ride?
        </div>
        <button class="logout-btn" onclick="window.location.href = 'logout.php';">Logout</button>
    </nav>

    <div class="sidebar">
        <button class="sidebar-btn" onclick="showContent('personal-info')">Personal Info</button>
        <button class="sidebar-btn" onclick="showContent('ride-history')">Ride History</button>
        <button class="sidebar-btn" onclick="showContent('book-ride')">Book a Ride</button>
    </div>

    <div class="content">
        <div id="personal-info" class="content-section" style="display: block;">
            <div class="section-heading">
                <h2>Personal Information</h2>
            </div>

            <div class="user-info">
                <p><strong>User ID :</strong>
                    <?php echo $user_id; ?>
                </p>
                <p><strong>Name :</strong>
                    <?php echo $username; ?>
                </p>
                <p><strong>Phone :</strong>
                    <?php echo $phone; ?>
                </p>
                <p><strong>Email :</strong>
                    <?php echo $email; ?>
                </p>
                <p><strong>Password :</strong>
                    <?php echo $password; ?>
                </p>
            </div>

            <div class="ride-info">
                <p><strong>Total Rides Booked:</strong>
                    <?php echo $totalRides; ?>
                </p>
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
                            while ($row = $completedResult->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $row['RideID'] . '</td>';
                                echo '<td>' . $row['DriverID'] . '</td>';
                                echo '<td>' . $row['StartLocation'] . '</td>';
                                echo '<td>' . $row['EndLocation'] . '</td>';
                                echo '<td>' . $row['distant'] . '</td>';
                                echo '<td>' . $row['Fare'] . '</td>';
                                echo '<td>' . $row['Status'] . '</td>';
                                echo '</tr>';
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
                                while ($row = $pendingResult->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>' . $row['RideID'] . '</td>';
                                    echo '<td>' . $row['DriverID'] . '</td>';
                                    echo '<td>' . $row['StartLocation'] . '</td>';
                                    echo '<td>' . $row['EndLocation'] . '</td>';
                                    echo '<td>' . $row['distant'] . '</td>';
                                    echo '<td>' . $row['Fare'] . '</td>';
                                    echo '<td>' . $row['Status'] . '</td>';
                                    echo '</tr>';
                                }
                            ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="book-ride" class="content-section">
            <!-- Section Heading -->
            <div class="section-heading">
                <h2>Book a Ride Today</h2>
            </div>

            <div class="ride-booking-form">
                <form action="passenger.php" method="POST">
                    <div class="form-group">
                        <label for="start-location">Start Location</label>
                        <input type="text" id="start-location" name="start-location" placeholder="Enter start location"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="end-location">End Location</label>
                        <input type="text" id="end-location" name="end-location" placeholder="Enter end location"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="distant">Distance (km)</label>
                        <input type="number" id="distant" name="distant" placeholder="Enter distance in km" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="book-btn">Book Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="passenger.js"></script>
</body>

</html>