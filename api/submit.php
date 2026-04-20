<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/Database.php';
require_once '../src/Inquiry.php';

$database = new Database();
$db = $database->getConnection();
if (!$db) {
    Response::send(false, 'Database connection failed. Please try again later.');
}

$inquiry = new Inquiry($db);


$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    $data = $_POST;
}

$first_name = trim($data['first_name'] ?? '');
$last_name  = trim($data['last_name']  ?? '');
$email      = trim($data['email']      ?? '');
$phone      = trim($data['phone']      ?? '');
$vehicleType= trim($data['vehicleType'] ?? '');
$pkg        = trim($data['interested_package'] ?? 'Not Sure');
$message    = trim($data['message']    ?? '');

$validation = Validator::validateInquiry($data);
if (!empty($validation['errors'])) {
    Response::send(false, 'Validation failed. Please correct the errors below.', ['errors' => $validation['errors']]);
}

$cleanPhone = $validation['cleanPhone'];

if ($inquiry->checkSpam($cleanPhone)) {
    Response::send(false, 'Maximum 3 inquiries allowed per phone number. For further assistance, please call: 021-34330887-88');
}

$insertData = [
    'first_name' => $first_name,
    'last_name' => $last_name,
    'email' => $email,
    'phone' => $cleanPhone,
    'vehicleType' => $vehicleType,
    'message' => $message,
    'interested_package' => $pkg
];

$insertedId = $inquiry->create($insertData);
if ($insertedId) {
    Response::send(true, 'Thank you! Your inquiry has been received. Our team will contact you within 24 hours.', [
        'inquiry_id' => $insertedId,
        'name'       => "$first_name $last_name"
    ]);
} else {
    Response::send(false, 'Error submitting inquiry.');
}