<?php
$jsonResult = new \stdClass();
session_start();
if(isset($_SESSION["loggedin_api"]) && $_SESSION["loggedin_api"] === true){
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, user is already logged in";
    http_response_code(403);
    die(json_encode($jsonResult));
}
require_once "../../../config.php";
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
    if(empty($data["password"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, password can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $sql = "SELECT id, username, password, bSpecialAccess, accountType, email, displayName FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $data["username"];
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $special, $type, $email, $displayName);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($data["password"], $hashed_password)){
                            session_destroy();
                            session_start();
                            $_SESSION["loggedin_api"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["displayName"] = $displayName;
                            $_SESSION["username"] = $username;                            
                            $_SESSION["special"] = $special;
                            $_SESSION["type"] = $type;
                            $jsonResult->success=true;
                            $jsonResult->triggerResults->displayName = $displayName;
                            $jsonResult->triggerResults->email=$email;
                            $jsonResult->triggerResults->id=$id;
                            $jsonResult->triggerResults->accountType=$type;
                            $jsonResult->triggerResults->specialAccess= $special;
                            http_response_code(200);
                            die(json_encode($jsonResult));
                        } else{
                            $jsonResult->success=false;
                            $jsonResult->reason="Login error, no account found for username/password combination";
                            http_response_code(404);
                            die(json_encode($jsonResult));
                        }
                    }
                } else{
                    $jsonResult->success=false;
                    $jsonResult->reason="Login error, no account found for username/password combination";
                    http_response_code(404);
                    die(json_encode($jsonResult));
                }
            } else{
                $jsonResult->success=false;
                $jsonResult->reason="Login error, unknown server error occured.";
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