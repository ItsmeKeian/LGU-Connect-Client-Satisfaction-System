<?php

session_start();

require "dbconnect.php";


$username = $_POST["username"];
$password = $_POST["password"];

$stmt = $conn->prepare(
    "SELECT * FROM users WHERE username=?"
);

$stmt->execute([$username]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user){

    if(password_verify($password, $user["password"])){

        $_SESSION["user"] = $user["username"];

        echo json_encode([
            "status" => "success"
        ]);

    } else {

        echo json_encode([
            "status" => "error",
            "message" => "Wrong password"
        ]);
    }

} else {

    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
}
?>