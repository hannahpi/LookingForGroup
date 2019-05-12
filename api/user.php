<?php
require_once '../config/env.php';
session_start_once();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../Classes/DebugHelper.php';
require_once '../Classes/User.php';
require_once '../Classes/Database.php';

$database = new Database();
$conn = $database->getConnection();
$attributes = $database->getAttributes();

$myUser = new User($conn, $attributes);
if (isset($_SESSION['Email'])) {
    $myUser->get($_SESSION['Email'], false);
}
$method = $_SERVER['REQUEST_METHOD'];
$request = file_get_contents('php://input');
$debugH = new DebugHelper(true);
$debugH->addObject($method);
$debugH->addObject($request);
$debugH->addObject($myUser);

function update_user($conn, $attributes, $request) {
    $user = new User($conn, $attributes);
    $userInfo = json_decode(strip_tags($request),true);
    $user->setUsername($userInfo["Username"]);
    $user->setStartTime($userInfo["StartTime"]);
    $user->duration($userInfo["Duration"]);
    $user->setTimezoneOffset($userInfo["TimezoneOffset"]);
    $user->setDST($userInfo["DST"]);
    $user->setLocation($userInfo["Location"]);
    $user->setGroupID($userInfo["GroupID"]);
    $user->updateDB();
    print_r($request);
}

function add_user($conn, $attributes, $request) {
    $user = new User($conn, $attributes);
    $userInfo = json_decode(strip_tags($request),true);
    print_r($request);        
    $email = $userInfo["Email"];
    $username = $userInfo["Username"];
    $startTime = $userInfo["StartTime"];
    $duration = DateTime::createFromFormat("H:i:s", $userInfo["Duration"]);
    $timezoneOffset = floatval($userInfo["TimezoneOffset"]);
    $dst = (strtolower($userInfo["DST"]) == "true" ? true : false);
    $location = $userInfo["Location"];
    if (isset($userInfo["GroupID"]))
      $groupID = $userInfo["GroupID"];
    else
      $groupID = NULL;
    echo "user information has been interpretted";
    print_r(json_encode($userInfo));
    $user->createNew($email, $username, $startTime, $duration, $timezoneOffset,
           $dst, $location, $groupID);
    echo "user was supposed to be created...";
    print_r(json_encode($user));
}

function get_user($conn, $attributes, $request) {
    $user = new User($conn, $attributes);
    switch (strtolower($request[0])) {
        case "email":
            $rEmail = strip_tags($request[1]);
            break;
        case "getall":
            print_r($user->getAllJson());
            return;
        default:
            $rID = strip_tags($request[0]);
            break;
    }
    if (isset($rID)) {
        echo $user->getByID($rID, true);
    } else if (isset($rEmail)) {
        echo $user->get($rEmail);
    }
}


switch ($method) {
    case 'PUT':
        update_user($conn, $attributes, $request);
        break;
    case 'POST':
        add_user($conn, $attributes, $request);
        break;
    case 'GET':
        $request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));
        get_user($conn, $attributes, $request);
        break;
    default:
        print_r(json_encode(array("message" => "Invalid method received")));
        $debugH->errormail($myUser->email, "API Call to user", "Invalid API call");



}

 ?>
