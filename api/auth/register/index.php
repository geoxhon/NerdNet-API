<?php
$jsonResult = new \stdClass();
session_start();
if(isset($_SESSION["loggedin_api"]) && $_SESSION["loggedin_api"] === true){
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, user is already logged in";
    http_response_code(403);
    die(json_encode($jsonResult));
}
function endsWith($string, $endString)
{
    $len = strlen($endString);
    if ($len == 0) {
        return true;
    }
    return (substr($string, -$len) === $endString);
}
require_once "../../../config.php";
include "../../../functions.php";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $data = json_decode(file_get_contents('php://input'), true);
    if($data==null){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, input is not of type JSON";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(empty($data["displayName"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, displayName can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(!is_string($data["displayName"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, displayName is not of type string.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(strlen(trim($data["displayName"])) < 5){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, displayName can't contain less than 5 characters";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(strlen(trim($data["displayName"])) > 30){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, displayName can't contain more than 30 characters";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(empty($data["username"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, username can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($data["username"]))){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, username contains invalid characters.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }else{
        $sql = "SELECT id FROM users WHERE username = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($data["username"]);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $jsonResult->success=false;
                    $jsonResult->reason="Validation error, username already exists";
                    http_response_code(409);
                    die(json_encode($jsonResult));
                } else{
                   
                }
            } else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }

            mysqli_stmt_close($stmt);
        }
    }
    if(empty($data["password"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, password can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif(strlen(trim($data["password"])) < 6){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, password must contain at least 6 characters";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(empty($data["email"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, email can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, email is invalid";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif(!endsWith($data["email"], '@uom.edu.gr')){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, email must be academic.";
        http_response_code(400);
        die(json_encode($jsonResult));
    }else{
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $data["email"];
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $jsonResult->success=false;
                    $jsonResult->reason="Validation error, email already exists";
                    http_response_code(409);
                    die(json_encode($jsonResult));
                } 
            } else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
            mysqli_stmt_close($stmt);
        }
    }
    $sql = "INSERT INTO users (id, username, password, validationid, email, displayName) VALUES (?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sssss", $param_id, $param_username, $param_password, $param_validationid, $param_email, $param_displayName);
            $param_displayName = $data["displayName"];
            $param_username = $data['username'];
            $param_email = $data['email'];
            $param_id = getRandomId();
            $param_validationid = getRandomId();
            $param_password = password_hash($data['password'], PASSWORD_DEFAULT);
            if(mysqli_stmt_execute($stmt)){
                mail($email, "Verify your account", "Κάνε επιβεβαίωση του λογαριασμού σου: https://nerdnet.geoxhonapps.com/validade?id=".$param_validationid, 'From: nerdnet@geoxhonapps.com');
                $jsonResult->success=true;
                $jsonResult->triggerResults->id = $param_id;
                $jsonResult->triggerResults->createdAt = time();
                $jsonResult->triggerResults->accountType = 0;
                http_response_code(200);
                die(json_encode($jsonResult));
            } else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
            mysqli_stmt_close($stmt);
        }
    
}else{
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, request method (".$_SERVER["REQUEST_METHOD"].") not allowed";
    http_response_code(405);
    die(json_encode($jsonResult));
}