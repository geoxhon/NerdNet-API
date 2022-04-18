<?php
$jsonResult = new \stdClass();
session_start();
if(!isset($_SESSION["loggedin_api"]) && !$_SESSION["loggedin_api"] === true){
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, user is not authenticated";
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
    if(empty($data["subjectId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, subjectId can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif(!is_string($data["subjectId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, subjectId must be of type string";
        http_response_code(400);
        die(json_encode($jsonResult));
    }else{
        $sql = "SELECT rating FROM subjects WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($data["subjectId"]);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 0){
                    $jsonResult->success=false;
                    $jsonResult->reason="Validation error, subjectId was not found.";
                    http_response_code(404);
                    die(json_encode($jsonResult));
                } else{
                    mysqli_stmt_bind_result($stmt, $subjectRating);
                    mysqli_stmt_fetch($stmt);
                    $sql = "SELECT * FROM ratings WHERE userId = ? AND subjectId = ?";
                    if($stmt = mysqli_prepare($link, $sql)){
                         mysqli_stmt_bind_param($stmt, "ss", $param_id, $param_subjectId);
                         $param_id = trim($_SESSION["id"]);
                         $param_subjectId = trim($data["subjectId"]);
                         if(mysqli_stmt_execute($stmt)){
                             mysqli_stmt_store_result($stmt);
                             if(mysqli_stmt_num_rows($stmt) != 0){
                                 $jsonResult->success=false;
                                 $jsonResult->reason="Operation not allowed, user has already posted rating.";
                                 http_response_code(403);
                                 die(json_encode($jsonResult));
                             }
                         }else{
                             $jsonResult->success=false;
                             $jsonResult->reason="Database error, unknown server error occured.";
                             http_response_code(500);
                             die(json_encode($jsonResult));
                         }
                         
                    }
                    
                }
            } else{
                $jsonResult->success=false;
                $jsonResult->reason="Database error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    if(empty($data["rating"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, rating can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif(!is_int($data["rating"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, rating must be of type int";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif($data["rating"]<0||$data["rating"]>5){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, rating must be in range 0-5";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $sql = "UPDATE subjects SET rating = ? WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "is", $param_rating, $param_subjectId);
            if($subjectRating == 0){
                $param_rating = $data["rating"];
            }else{
                $param_rating = ($subjectRating + $data["rating"])/2;
            }
            $param_subjectId = trim($data["subjectId"]);
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
    $sql = "INSERT INTO ratings (subjectId, userId, rating) VALUES (?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssi", $param_subjectid, $param_userid, $param_rating2);
            $param_subjectid = trim($data["subjectId"]);
            $param_userid = $param_username = trim($_SESSION["id"]);
            $param_rating2 = $param_rating;
            if(mysqli_stmt_execute($stmt)){
                $jsonResult->success=true;
                $jsonResult->triggerResults->newRating = $param_rating;
                $jsonResult->triggerResults->createdAt = time();
                http_response_code(200);
                die(json_encode($jsonResult));
            } else{
                $jsonResult->success=false;
                $jsonResult->reason="Database error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
            mysqli_stmt_close($stmt);
        }
}elseif($_SERVER["REQUEST_METHOD"] == "GET"){
    if(empty($_GET["subjectId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, subjectId can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif(!is_string($_GET["subjectId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, subjectId must be of type string";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $sql = "SELECT rating FROM subjects WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_subjectId);
            $param_subjectId = trim($_GET["subjectId"]);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 0){
                    $jsonResult->success=false;
                    $jsonResult->reason="Not found error, subject was not found";
                    http_response_code(404);
                    die(json_encode($jsonResult));
                } else{
                   mysqli_stmt_bind_result($stmt, $rating);
                   mysqli_stmt_fetch($stmt);
                   $jsonResult->success=true;
                   $jsonResult->triggerResults->rating = $rating;
                   http_response_code(200);
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