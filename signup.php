<?php
include 'db_connection.php';

if (!$conn) 
{
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $role = $_POST["role"]; 
    $name = $_POST["name"];
    $password = $_POST["password"]; 

    if ($role === "passenger") 
    {
        $email = $_POST["email"];
        $phone = $_POST["phone"];

        $sql = "INSERT INTO User (Name, Email, PhoneNumber, Password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) 
        {
            $stmt->bind_param("ssss", $name, $email, $phone, $password);

            if ($stmt->execute()) 
            {
                $userID = $conn->insert_id; 
                echo "<p>Passenger registered successfully! Your User ID is: <strong>" . $userID . "</strong></p>";
                echo "<p>You will be redirected to the login page in 10 seconds...</p>";
                echo '<meta http-equiv="refresh" content="10;url=login.html">';
            } 
            else 
            {
                echo "Error registering passenger: " . $stmt->error;
            }

            $stmt->close();
        } 
        else 
        {
            echo "Error preparing statement: " . $conn->error;
        }
    } 
    elseif ($role === "driver") 
    {
        $vehicleName = $_POST["vehicle_name"];
        $model = $_POST["model"];
        $licensePlate = $_POST["license_plate"];

        $sql = "INSERT INTO Driver (Name, VehicleMake, VehicleModel, LicensePlate, Password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) 
        {
            $stmt->bind_param("sssss", $name, $vehicleName, $model, $licensePlate, $password);

            if ($stmt->execute()) 
            {
                $driverID = $conn->insert_id; 
                echo "<p>Driver registered successfully! Your Driver ID is: <strong>" . $driverID . "</strong></p>";
                echo "<p>You will be redirected to the login page in 10 seconds...</p>";
                echo '<meta http-equiv="refresh" content="10;url=login.html">';
            } 
            else 
            {
                echo "Error registering driver: " . $stmt->error;
            }

            $stmt->close();
        } 
        else 
        {
            echo "Error preparing statement: " . $conn->error;
        }
    } 
    else 
    {
        echo "Invalid role specified.";
    }
}
?>
