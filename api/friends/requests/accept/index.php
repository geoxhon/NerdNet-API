<?php
header('Content-type: application/json; charset=utf-8');
$jsonResult = new \stdClass();
session_start();
if(!isset($_SESSION["loggedin_api"]) && !$_SESSION["loggedin_api"] === true){
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, user is not authenticated";
    http_response_code(403);
    die(json_encode($jsonResult));
}
require_once "../../../../config.php";
include "../../../../functions.php";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $data = json_decode(file_get_contents('php://input'), true);
    if($data==null){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, input is not of type JSON";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(empty($data["userId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, userId can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(!is_string($data["userId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, userId is not of type string.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $incomingrequests = getIncomingFriendRequests();
    if(!in_array($data["userId"], $incomingrequests)){
        $jsonResult->success=false;
        $jsonResult->reason="Error, user has no incoming request with requested user.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $sql = "DELETE FROM friendrequest WHERE  requesterId = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_requester);
        $param_requester = $data["userId"];
        if(!mysqli_stmt_execute($stmt)){
            $jsonResult->success=false;
            $jsonResult->reason="Database error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
    }else{
        $jsonResult->success=false;
        $jsonResult->reason="Database error, unknown server error occured.";
        http_response_code(500);
        die(json_encode($jsonResult));
    }
    $sql = "INSERT INTO friends (userId1, userId2) VALUES (?, ?)";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ss", $param_requester, $param_me);
        $param_me = $_SESSION["id"];
        $param_requester = $data["userId"];
        if(mysqli_stmt_execute($stmt)){
           $jsonResult->success=true;
           http_response_code(200);
           die(json_encode($jsonResult));
        }else{
           $jsonResult->success=false;
           $jsonResult->reason="Database error, unknown server error occured.";
           http_response_code(500);
           die(json_encode($jsonResult));
       }
    }
}else{
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, request method (".$_SERVER["REQUEST_METHOD"].") not allowed";
    http_response_code(405);
    die(json_encode($jsonResult));
}