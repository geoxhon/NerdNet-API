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
    if($_SESSION["type"] != 1){
        $jsonResult->success=false;
        $jsonResult->reason="Operation now allowed, only professors can accept an appointment.";
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
    
    if($data["appointmentId"]==null){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, appointmentId can't be empty.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    
    if(!is_int($data["appointmentId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, appointmentId is not of type int";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    
    $myAppointments = getMyAppointments();
    foreach($myAppointments as $appointment){
        if($appointment["appointmentId"]==$data["appointmentId"]){
            if($appointment["status"] == 1){
                $jsonResult->success=false;
                $jsonResult->reason="Conflict error, can't accept an appointment that has already been accepted.";
                http_response_code(409);
                die(json_encode($jsonResult));
            }
            
            if($appointment["status"] == 2){
                $jsonResult->success=false;
                $jsonResult->reason="Operation not allowed, can't accept a cancelled appointment.";
                http_response_code(403);
                die(json_encode($jsonResult));
            }
            
            $sql = "UPDATE appointments SET status = 1 WHERE id = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "i", $param_appointmentId);
                $param_appointmentId = $data["appointmentId"];
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
        }
    }
    $jsonResult->success=false;
    $jsonResult->reason="Not found error, no valid appointment was found with provided appointmentId";
    http_response_code(404);
    die(json_encode($jsonResult));
    
}else{
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, request method (".$_SERVER["REQUEST_METHOD"].") not allowed";
    http_response_code(405);
    die(json_encode($jsonResult));
}