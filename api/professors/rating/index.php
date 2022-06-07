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
    $data = json_decode(file_get_contents('php://input'), true);
    if($data==null){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, input is not of type JSON";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    if(empty($data["professorId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, professorId can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif(!is_int($data["professorId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, professorId must be of type int";
        http_response_code(400);
        die(json_encode($jsonResult));
    }else{
        
        if(!doesProfessorExist($data["professorId"])){
            $jsonResult->success=false;
            $jsonResult->reason="Validation error, professorId was not found.";
            http_response_code(404);
            die(json_encode($jsonResult));
        }
        
        if(getMyProfessorRating($data["professorId"], $_SESSION["id"])!=-1){
            $jsonResult->success=false;
            $jsonResult->reason="Operation not allowed, user has already posted rating.";
            http_response_code(403);
            die(json_encode($jsonResult));
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
    $details = getProfessorDetailedVotes($data["professorId"]);
    $sql = "UPDATE professors SET rating = ? WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "di", $param_rating, $param_professorId);
            if($details["count"] == 0){
                $param_rating = $data["rating"];
            }else{
                $param_rating = floatval(number_format(($details["sum"] + $data["rating"])/($details["count"]+1.0), 2));
            }
            // Set parameters
            $param_professorId = trim($data["professorId"]);
            
            // Attempt to execute the prepared statement
            if(!mysqli_stmt_execute($stmt)){
                $jsonResult->success=false;
                $jsonResult->reason="Database error, unknown server error occured";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
    
    }else{
        $jsonResult->success=false;
                $jsonResult->reason="Database error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
    }
    $sql = "INSERT INTO ratings (professorId, userId, rating) VALUES (?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "isi", $param_professorid, $param_userid, $param_rating2);
            $param_professorid = trim($data["professorId"]);
            $param_userid = $param_username = trim($_SESSION["id"]);
            $param_rating2 = $data["rating"];
            // Attempt to execute the prepared statement
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

            // Close statement
            mysqli_stmt_close($stmt);
        }
}elseif($_SERVER["REQUEST_METHOD"] == "GET"){
    if(empty($_GET["professorId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, professorId can't be empty";
        http_response_code(400);
        die(json_encode($jsonResult));
    }elseif(!is_string($_GET["professorId"])){
        $jsonResult->success=false;
        $jsonResult->reason="Validation error, professorId must be of type int";
        http_response_code(400);
        die(json_encode($jsonResult));
    }
    $sql = "SELECT rating FROM professors WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_professorId);
            
            // Set parameters
            $param_professorId = $_GET["professorId"];
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 0){
                    $jsonResult->success=false;
                    $jsonResult->reason="Not found error, professor was not found";
                    http_response_code(404);
                    die(json_encode($jsonResult));
                } else{
                   mysqli_stmt_bind_result($stmt, $rating);
                   mysqli_stmt_fetch($stmt);
                   $jsonResult->success=true;
                   $jsonResult->triggerResults->rating = $rating;
                   $jsonResult->triggerResults->myRating = getMyProfessorRating($param_professorId, $_SESSION["id"]);
                   http_response_code(200);
                   die(json_encode($jsonResult));
                }
            } else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
}else{
    $jsonResult->success=false;
    $jsonResult->reason="Operation not allowed, request method (".$_SERVER["REQUEST_METHOD"].") not allowed";
    http_response_code(405);
    die(json_encode($jsonResult));
}