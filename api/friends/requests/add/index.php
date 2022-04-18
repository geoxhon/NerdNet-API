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
    if(empty($data["username"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, username can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(!is_string($data["username"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, username is not of type string.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $userid = getUserId($data["username"]);
    if($userid==false){
        $jsonResult->success=false;
        $jsonResult->reason="No user was found.";
        http_response_code(404);
        die(json_encode($jsonResult));
    }
    if($userid==$_SESSION["id"]){
        $jsonResult->success=false;
        $jsonResult->reason="Error, user can not add themselves";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $friends = getFriends();
    $incomindgrequests = getIncomingFriendRequests();
    $outgoingrequests = getOutgoingFriendRequests();
    
    if(in_array($userid, $friends)){
        $jsonResult->success=false;
        $jsonResult->reason="Error, user is already friends with requested user.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(in_array($userid, $incomingrequests)){
        $jsonResult->success=false;
        $jsonResult->reason="Error, user has already an incoming request with requested user.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(in_array($userid, $outgoingrequests)){
        $jsonResult->success=false;
        $jsonResult->reason="Error, user has already an outgoing request with requested user.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(in_array($userid, $friends)){
        $jsonResult->success=false;
        $jsonResult->reason="Error, user is already friends with requested user.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $sql = "INSERT INTO friendrequest (requesterid, receiverid) VALUES (?, ?)";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ss", $param_requester, $param_receiver);
        $param_requester = $_SESSION["id"];
        $param_receiver = $userid;
        if(mysqli_stmt_execute($stmt)){
           $jsonResult->success=true;
           $jsonResult->triggerResults->userId = $userid;
           $jsonResult->triggerResults->outgoingRequests = $outgoingrequests;
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