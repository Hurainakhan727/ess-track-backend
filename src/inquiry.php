<?php
class Inquiry {
    private $conn;
    private $table_name = "inquiries";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function checkSpam($phone) {
        $query = "SELECT COUNT(*) AS total FROM " . $this->table_name . " WHERE phone_number = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] >= 3;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (first_name, last_name, email, phone_number, vehicle_type, message, interested_package) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        $pkg = $data['interested_package'] ?? 'Not Sure';
        $stmt->bind_param("sssssss", 
            $data['first_name'], $data['last_name'], $data['email'], 
            $data['phone'], $data['vehicleType'], $data['message'], $pkg
        );

        if ($stmt->execute()) {
            $insertedId = $this->conn->insert_id;
            $this->sendEmailNotification($data, $insertedId);
            return $insertedId;
        }
        return false;
    }

    private function sendEmailNotification($data, $id) {
        $emailSubject = "New Inquiry from ESS Tracker Website - " . $data['first_name'] . " " . $data['last_name'];
        $emailBody = "New inquiry received:\n\n"
                   . "Name:    " . $data['first_name'] . " " . $data['last_name'] . "\n"
                   . "Email:   " . $data['email'] . "\n"
                   . "Phone:   " . $data['phone'] . "\n"
                   . "Vehicle: " . ($data['vehicleType'] ?? '') . "\n"
                   . "Package: " . ($data['interested_package'] ?? 'Not Sure') . "\n"
                   . "Message: " . $data['message'] . "\n\n"
                   . "Submitted at: " . date('Y-m-d H:i:s');
        @mail('info@esspl.com.pk', $emailSubject, $emailBody, "From: info@esspl.com.pk");
    }
}