<?php
class Response {
    public static function send($success, $message, $data = []) {
        echo json_encode([
            'success'   => $success,
            'message'   => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'data'      => $data
        ]);
        exit;
    }
}