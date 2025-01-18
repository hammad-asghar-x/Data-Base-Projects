<?php
session_start(); 

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $loginType = $_POST['login_type'];

    if ($loginType === 'passenger')
    {
        $userId = $_POST['user_id'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM User WHERE UserID = ? AND Password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $userId, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) 
        {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['UserID']; 
            $_SESSION['username'] = $user['Name']; 
            $_SESSION['email'] = $user['Email']; 
            $_SESSION['phone'] = $user['PhoneNumber']; 
            $_SESSION['password'] = $user['Password']; 

            header("Location: passenger.php"); 
            exit();
        } else 
        {
            echo "Invalid Passenger Credentials.";
        }
    } elseif ($loginType === 'driver') 
    {
        $driverId = $_POST['driver_id'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM Driver WHERE DriverID = ? AND Password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $driverId, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) 
        {
            $driver = $result->fetch_assoc();
            $_SESSION['driver_id'] = $driver['DriverID']; 
            $_SESSION['driver_name'] = $driver['Name']; 
            $_SESSION['driver_password'] = $driver['password']; 
            $_SESSION['vehicle_model'] = $driver['VehicleModel']; 
            $_SESSION['vehicle_make'] = $driver['VehicleMake']; 
            $_SESSION['License_Plate'] = $driver['LicensePlate']; 


            header("Location: driver.php"); 
            exit();
        } else 
        {
            echo "Invalid Driver Credentials.";
        }
    } else 
    {
        echo "Invalid Login Type.";
    }

    $stmt->close();
}

$conn->close();
?>
