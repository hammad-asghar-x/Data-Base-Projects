<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);

    $sql = "INSERT INTO Feedback (FullName, Email, Message) VALUES ('$name', '$email', '$message')";

    if ($conn->query($sql) === TRUE) {
        header("Location: Contact.html");
        exit(); 
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>
