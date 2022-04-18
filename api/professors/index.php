<?php
$jsonResult = new \stdClass();
session_start();
if(!isset($_SESSION["loggedin_api"]) && !$_SESSION["loggedin_api"] === true){
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, user is not authenticated";
    http_response_code(403);
    die(json_encode($jsonResult));
}
require_once "../../config.php";
if($_SERVER["REQUEST_METHOD"] == "GET"){
    $sql = "SELECT id, name, phone, email, profilePhoto FROM professors";
    if($stmt = mysqli_prepare($link, $sql)){
        if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                mysqli_stmt_bind_result($stmt, $id, $name, $phone, $email, $profilePhoto);
                $jsonResult->success=true;
                $index = 0;
                while(mysqli_stmt_fetch($stmt)){
                    $jsonResult->triggerResults[$index]->id = $id;
                    $jsonResult->triggerResults[$index]->name = $name;
                    $jsonResult->triggerResults[$index]->phone = $phone;
                    $jsonResult->triggerResults[$index]->email = $email;
                    $jsonResult->triggerResults[$index]->profilePhoto = $profilePhoto;
                    $index = $index+1;
                }
                header('Content-type: application/json; charset=utf-8');
                http_response_code(200);
                die(json_encode($jsonResult));
        }else{
            $jsonResult->success=false;
            $jsonResult->reason="Login error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
    }else{
        $jsonResult->success=false;
        $jsonResult->reason="unknown server error occured.";
        http_response_code(500);
        die(json_encode($jsonResult));
    }
}else{
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, request method (".$_SERVER["REQUEST_METHOD"].") not allowed";
    http_response_code(405);
    die(json_encode($jsonResult));
}