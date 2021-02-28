<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods,Authorization,X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Student.php';

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

$s_name = $data->s_name;
$p_id = $data->p_id;

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();

// Instantiate Student object
$student = new Student($db, $p_id);

// Create student
if ($student->create($s_name)) {
    echo json_encode(
        array('message' => 'Student created')
    );
} else {
    echo json_encode(
        array('message' => 'Failed to create student')
    );
}
