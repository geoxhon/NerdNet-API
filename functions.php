<?php

function getProfessorRating($professorId){
    require "config.php";
    $sql = "SELECT rating FROM professors WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_professortId);
        $param_professorId = $professorId;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) != 0){
                mysqli_stmt_bind_result($stmt, $rating);
                mysqli_stmt_fetch($stmt);
                return floatval($rating);
            }else{
                return -1;
            }
            
        }else{
            $jsonResult->success=false;
            $jsonResult->reason="Database error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
    }
}

function getSubjectRating($subjectId){
    require "config.php";
    $sql = "SELECT rating FROM subjects WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_subjectId);
        $param_subjectId = $subjectId;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) != 0){
                mysqli_stmt_bind_result($stmt, $rating);
                mysqli_stmt_fetch($stmt);
                return floatval($rating);
            }else{
                return -1;
            }
            
        }else{
            $jsonResult->success=false;
            $jsonResult->reason="Database error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
    }
}

function getProfessorDetailedVotes($professorId){
    require "config.php";
    $sql = "SELECT SUM(rating), COUNT(rating) FROM ratings WHERE professorId = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_professorId);
        $param_professorId = $professorId;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $sum, $count);
            if(mysqli_stmt_fetch($stmt)){
                $outResult["sum"] = $sum;
                $outResult["count"] = $count;
                return $outResult;
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
    }
}

function getSubjectDetailedVotes($subjectId){
    require "config.php";
    $sql = "SELECT SUM(rating), COUNT(rating) FROM ratings WHERE subjectId = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_subjectId);
        $param_subjectId = $subjectId;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $sum, $count);
            if(mysqli_stmt_fetch($stmt)){
                $outResult["sum"] = $sum;
                $outResult["count"] = $count;
                return $outResult;
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
    }
}

function getMyProfessorRating($professorId, $userId){
    require "config.php";
    $sql = "SELECT rating FROM ratings WHERE userId = ? AND professorId = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "si", $param_id, $param_professorId);
        $param_id = $userId;
        $param_professorId = $professorId;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) != 0){
                mysqli_stmt_bind_result($stmt, $rating);
                mysqli_stmt_fetch($stmt);
                return intval($rating);
            }else{
                return -1;
            }
            
        }else{
            $jsonResult->success=false;
            $jsonResult->reason="Database error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
    }
}

function doesProfessorExist($professorId){
    require "config.php";
     $sql = "SELECT rating FROM professors WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_professorid);
            
            // Set parameters
            $param_professorid = $professorId;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                return !(mysqli_stmt_num_rows($stmt) == 0);
                
            }else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
        }
}

function getBookedTimestamps($professorId){
    require "config.php";
    $sql = "SELECT date FROM appointments WHERE (professorId = ? AND status = 1 AND date>=current_timestamp())";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $professorId;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $timestamp);
            $index= 0;
            $outTimestamps = [];
            while(mysqli_stmt_fetch($stmt)){
                $outTimestamps[$index] = strtotime($timestamp);
                $index++;
            }
            return $outTimestamps;
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
}
function getMyAppointments(){
    require "config.php";
    if($_SESSION["type"]!=1){
        $sql = "SELECT * FROM appointments WHERE (studentId = ? AND date>=current_timestamp())";
        $param_type = "s";
        $id = $_SESSION["id"];
    }else{
        $sql = "SELECT * FROM appointments WHERE (professorId = ? AND date>=current_timestamp())";
        $param_type = "i";
        $id = $_SESSION["associatedProfessor"];
    }
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, $param_type, $param_id);
        $param_currentTime = time();
        $param_id = $id;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            mysqli_stmt_bind_result($stmt, $appointmentId, $studentId, $professorId, $date, $status, $created_at);
            $index= 0;
            while(mysqli_stmt_fetch($stmt)){
                $outAppointments[$index]["appointmentId"] = $appointmentId;
                $outAppointments[$index]["studentId"] = $studentId;
                $outAppointments[$index]["professorId"] = $professorId;
                $outAppointments[$index]["date"] = strtotime($date);
                $outAppointments[$index]["status"] = $status;
                $outAppointments[$index]["created_at"] = $created_at;
                $index++;
            }
            return $outAppointments;
        }else{
            $jsonResult->success=false;
            $jsonResult->reason="Database error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
    }
}
function getMySubjectRating($subjectId, $userId){
    require "config.php";
    $sql = "SELECT rating FROM ratings WHERE userId = ? AND subjectId = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ss", $param_id, $param_subjectId);
        $param_id = $userId;
        $param_subjectId = $subjectId;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) != 0){
                mysqli_stmt_bind_result($stmt, $rating);
                mysqli_stmt_fetch($stmt);
                return intval($rating);
            }else{
                return -1;
            }
            
        }else{
            $jsonResult->success=false;
            $jsonResult->reason="Database error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
    }
}
function getAvailabilityHours($day, $professorId){
    require "config.php";
    $sql = "SELECT startHour, endHour FROM availabilityDates WHERE (professorId = ? AND day = ?)";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ii", $param_profid, $param_day);
        $param_day = $day;
        $param_profid = $professorId;
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) == 0){
                return false;
            }else{
                mysqli_stmt_bind_result($stmt, $startHour, $endHour);
                if(mysqli_stmt_fetch($stmt)){
                    $outHours["startHour"] = $startHour;
                    $outHours["endHour"] = $endHour;
                    return $outHours;
                }else{
                    $jsonResult->success=false;
                    $jsonResult->reason="Database error, unknown server error occured.";
                    http_response_code(500);
                    die(json_encode($jsonResult));
                }
            }
        }else{
            $jsonResult->success=false;
            $jsonResult->reason="Database error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
        
    }
}
function getUserId($username){
    require "config.php";
     $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 0){
                    return false;
                }else{
                    mysqli_stmt_bind_result($stmt, $userId);
                    if(mysqli_stmt_fetch($stmt)){
                        return  $userId;
                    }else{
                        $jsonResult->success=false;
                        $jsonResult->reason="Register error, unknown server error occured.";
                        http_response_code(500);
                        die(json_encode($jsonResult));
                    }
                    
                }
                
            }else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
        }
}
function doesSubjectExist($subjectId){
     require "config.php";
     $sql = "SELECT rating FROM subjects WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $subjectId;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 0){
                    return false;
                }else{
                    return true;
                }
                
            }else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
        }else{
            $jsonResult->success=false;
            $jsonResult->reason="Register error, unknown server error occured.";
            http_response_code(500);
            die(json_encode($jsonResult));
        }
}
function getIncomingFriendRequests(){
    require "config.php";
     $sql = "SELECT requesterId FROM friendrequest WHERE (receiverId = ? AND isDeleted = 0)";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_id);
            
            // Set parameters
            $param_id = trim($_SESSION["id"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                mysqli_stmt_bind_result($stmt, $userId);
                $index = 0;
                $outFriends = [];
                while(mysqli_stmt_fetch($stmt)){
                    $outFriends[$index] = $userId;
                    $index = $index+1;
                }
                return $outFriends;
            }else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
                
        }else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
        
}
function getOutgoingFriendRequests(){
     require "config.php";
     $sql = "SELECT receiverId FROM friendrequest WHERE (requesterId = ? AND isDeleted = 0)";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_id);
            
            // Set parameters
            $param_id = trim($_SESSION["id"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                mysqli_stmt_bind_result($stmt, $userId);
                $index = 0;
                $outFriends = [];
                while(mysqli_stmt_fetch($stmt)){
                    $outFriends[$index] = $userId;
                    $index = $index+1;
                }
                return $outFriends;
            }else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
                
        }
}
function getFriends(){
    require "config.php";
     $sql = "SELECT userId1, userId2 FROM friends WHERE (userId1 = ? or userId2 = ?)";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_id, $param_id);
            
            // Set parameters
            $param_id = trim($_SESSION["id"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                mysqli_stmt_bind_result($stmt, $userId1, $userId2);
                $index = 0;
                $outFriends = [];
                while(mysqli_stmt_fetch($stmt)){
                    if($userId1!=$param_id){
                        $outFriends[$index] = $userId1;
                    }else{
                        $outFriends[$index] = $userId2;
                    }
                    
                    $index = $index+1;
                }
                return $outFriends;
            }else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
                
        }
        
}
function getEnrollments(){
    require "config.php";
     $sql = "SELECT subjectId FROM enrollments WHERE userId = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_id);
            
            // Set parameters
            $param_id = trim($_SESSION["id"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                mysqli_stmt_bind_result($stmt, $subject);
                $index = 0;
                $outEnrollements = [];
                while(mysqli_stmt_fetch($stmt)){
                    $outEnrollments[$index] = $subject;
                    $index = $index+1;
                }
                return $outEnrollments;
            }else{
                $jsonResult->success=false;
                $jsonResult->reason="Register error, unknown server error occured.";
                http_response_code(500);
                die(json_encode($jsonResult));
            }
                
        }
        
}


function getRandomId() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
  
    for ($i = 0; $i < 8; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    $randomString .='-';
    for ($i = 0; $i < 4; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    $randomString .='-';
    for ($i = 0; $i < 4; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    $randomString .='-';
    for ($i = 0; $i < 4; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    $randomString .='-';
    for ($i = 0; $i < 12; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}