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
require_once "../../../config.php";
include "../../../functions.php";
if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(empty($_GET["professorId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, professorId can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(is_int($_GET["professorId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, professorId is not of type int";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $count = 0;
    $outAvailability = [];
    for($i = 0; $i<=6; $i++){
        $hours = getAvailabilityHours($i, $_GET["professorId"]);
        if($hours===false){
            continue;
        }else{
            $outAvailability[$count]["day"] = $i;
            $outAvailability[$count]["startHour"] = $hours["startHour"];
            $outAvailability[$count]["endHour"] = $hours["endHour"];
            $count++;
        }
        
    }
    $jsonResult->success=true;
    $jsonResult->triggerResults->availability = $outAvailability;
    $jsonResult->triggerResults->bookedTimestamps = getBookedTimestamps(1);
    http_response_code(200);
    die(json_encode($jsonResult));
}elseif($_SERVER["REQUEST_METHOD"] == "POST"){
    if($_SESSION["type"] != 1){
        $jsonResult->success=false;
        $jsonResult->reason="Operation now allowed, user is not professor";
        http_response_code(403);
        die(json_encode($jsonResult));
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if($data==null){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, input is not of type JSON";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(empty($data["day"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, day can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(empty($data["startHour"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, startHour can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(empty($data["endHour"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, endHour can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(!is_int($data["day"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, day must be of type int";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(!is_int($data["startHour"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, startHour must be of type int";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(!is_int($data["endHour"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, endHour must be of type int";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if($data["startHour"]>=$data["endHour"]){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, startHour can not be greater or equal to endHour";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if($data["day"]>6||$data["day"]<0){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, day has invalid value";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if($data["startHour"]>24||$data["startHour"]<0){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, startHour has invalid value";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if($data["endHour"]>24||$data["endHour"]<0){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, endHour has invalid value";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $hours = getAvailabilityHours($data["day"], $_SESSION["associatedProfessor"]);
    if($hours===false){
        $sql = "INSERT INTO availabilityDates (day, startHour, endHour, professorId) VALUES (?, ?, ?, ?)";
    }else{
        $sql = "UPDATE availabilityDates SET startHour = ?, endHour = ? WHERE (professorId = ? AND day = ?)";
    }
    if($stmt = mysqli_prepare($link, $sql)){
        if($hours===false){
            mysqli_stmt_bind_param($stmt, "iiii", $param_day, $param_startHour, $param_endHour, $param_professorId);
        }else{
            mysqli_stmt_bind_param($stmt, "iiii", $param_startHour, $param_endHour, $param_professorId, $param_day);
        }
        $param_day = $data["day"];
        $param_startHour = $data["startHour"];
        $param_endHour = $data["endHour"];
        $param_professorId = $_SESSION["associatedProfessor"];
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
    }else{
            $jsonResult->success=false;
            $jsonResult->reason="Database error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
}else{
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, request method (".$_SERVER["REQUEST_METHOD"].") not allowed";
    http_response_code(405);
    die(json_encode($jsonResult));
}
