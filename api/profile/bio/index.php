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
if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(empty($_GET["userId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, userId can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $sql = "SELECT bio FROM users WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_id);
        $param_id = trim($_GET["userId"]);
        if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                mysqli_stmt_bind_result($stmt, $bio);
                if(mysqli_stmt_fetch($stmt)){
                    http_response_code(200);
                    $jsonResult->success=true;
                    $jsonResult->triggerResults["bio"] = $bio;
                    die(json_encode($jsonResult));
                }else{
                     $jsonResult->success=false;
                     $jsonResult->reason="User not found error";
                     http_response_code(404);
                     die(json_encode($jsonResult));
                }
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
}elseif($_SERVER["REQUEST_METHOD"] == "PUT"){
    $data = json_decode(file_get_contents('php://input'), true);
    if($data==null){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, input is not of type JSON";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if($data["bio"]==null){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, bio can't be null";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif(strlen($data["bio"])>300){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, bio must be less than 300 characters";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $sql = "UPDATE users SET bio = ? WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ss", $param_bio, $param_id);
        $param_bio = $data["bio"];
        $param_id = $_SESSION["id"];
        if(!mysqli_stmt_execute($stmt)){
                $jsonResult->success=false;
                $jsonResult->reason="Database error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }else{
                $jsonResult->success=true;
                http_response_code(200);
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