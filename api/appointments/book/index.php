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
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if($_SESSION["type"] == 1){
        $jsonResult->success=false;
        $jsonResult->reason="Operation now allowed, professor can not book appointment";
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
    
    if($data["timestamp"]==null){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, timestamp can't be empty.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    
    if(!is_int($data["timestamp"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, timestamp is not of type int";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    
    if($data["professorId"]==null){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, professorId can't be empty.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    
    if(!is_int($data["professorId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, professorId is not of type int";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    
    if(!doesProfessorExist($data["professorId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Not found error, professor was not found.";
        http_response_code(404);
        die(json_encode($jsonResult));
    }
    
    if(date("i", $data["timestamp"]) != 0 && date("i", $data["timestamp"]) != 30){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, minute of appointment must be 00 or 30";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    
    $availability = getAvailabilityHours(date("w", $data["timestamp"]), $data["professorId"]);
    if($availability===false){
        $jsonResult->success=false;
        $jsonResult->reason="Not found error, professor has no available dates this day";
        http_response_code(404);
        die(json_encode($jsonResult));
    }
    
    if(date("H", $data["timestamp"])<$availability["startHour"]||date("H", $data["timestamp"])>=$availability["endHour"]){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, timestamp doesnt match to professors availability.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    
    $bookedTimestamps = getBookedTimestamps($data["professorId"]);
    if($bookedTimestamps!=null){
        if(in_array($data["timestamp"], $bookedTimestamps)){
            $jsonResult->success=false;
            $jsonResult->reason="Conflict error, there is a booked appointment at provided date";
            http_response_code(409);
            die(json_encode($jsonResult));
        }
    }
    
    $myAppointments = getMyAppointments();
    if($myAppointments!=null){
        foreach($myAppointments as $appointment){
            if($appointment["professorId"]==$data["professorId"] && ($appointment["status"] == 0||$appointment["status"]==1)){
                $jsonResult->success=false;
                $jsonResult->reason="Conflict error, you have already booked an appointment with provided professor.";
                http_response_code(409);
                die(json_encode($jsonResult));
            }
        }
    }
    
    $sql = "INSERT INTO appointments (studentId, professorId, date) VALUES (?, ?, FROM_UNIXTIME(?))";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "sii", $param_id, $param_professorId, $param_timestamp);
        $param_id = $_SESSION["id"];
        $param_professorId = $data["professorId"];
        $param_timestamp = $data["timestamp"];
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