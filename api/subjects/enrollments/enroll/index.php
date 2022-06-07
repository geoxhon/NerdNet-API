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
    if(empty($data["subjectId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, subjectId can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(!is_string($data["subjectId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, subjectId is not of type string.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $sub = doesSubjectExist($data["subjectId"]);
    if($sub===false){
        $jsonResult->success=false;
        $jsonResult->reason="Not found error, no subject with provided subjectid was found";
        http_response_code(404);
        die(json_encode($jsonResult));
    }
    $currentEnrollments = getEnrollments();
    if(count($currentEnrollements)>10){
        $jsonResult->success=false;
        $jsonResult->reason="Operation not allowed, too many enrollments";
        http_response_code(403);
        die(json_encode($jsonResult));
    }
    if(in_array($data["subjectId"], $currentEnrollments)){
        $jsonResult->success=false;
        $jsonResult->reason="Operation not allowed, user is already enrolled in this subject.";
        http_response_code(403);
        die(json_encode($jsonResult));
    }
    $sql = "INSERT INTO enrollments (userId, subjectId) VALUES (?, ?)";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ss", $param_userid, $param_subjectid);
        $param_userid = $_SESSION["id"];
        $param_subjectid = $data["subjectId"];
        if(mysqli_stmt_execute($stmt)){
            $jsonResult->success=true;
            http_response_code(200);
            die(json_encode($jsonResult));
        }else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
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