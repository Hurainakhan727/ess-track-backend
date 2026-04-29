<?php

namespace App\Services;

use App\Exceptions\DatabaseException;

class OtpService {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function generateOtp($phone, $purpose = 'inquiry') {
        // Clean phone number
        $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Set expiry time (5 minutes from now)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Delete any existing unverified OTPs for this phone
        $this->cleanupExpiredOtps($cleanPhone);

        // Insert new OTP
        $query = "INSERT INTO otp_verifications (phone_number, otp_code, purpose, expires_at) 
                  VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new DatabaseException('Failed to prepare OTP insert statement');
        }

        $stmt->bind_param("ssss", $cleanPhone, $otp, $purpose, $expiresAt);

        if (!$stmt->execute()) {
            throw new DatabaseException('Failed to save OTP');
        }

        $stmt->close();

        return $otp;
    }

    public function verifyOtp($phone, $otp, $purpose = 'inquiry') {
        $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Get the latest unverified OTP for this phone and purpose
        $query = "SELECT id, otp_code, expires_at FROM otp_verifications 
                  WHERE phone_number = ? AND purpose = ? AND is_verified = 0 
                  ORDER BY created_at DESC LIMIT 1";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ss", $cleanPhone, $purpose);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return false; // No OTP found
        }

        $row = $result->fetch_assoc();
        $stmt->close();

        // Check if OTP is expired
        if (strtotime($row['expires_at']) < time()) {
            return false; // OTP expired
        }

        // Check if OTP matches
        if ($row['otp_code'] !== $otp) {
            return false; // Wrong OTP
        }

        // Mark OTP as verified
        $this->markOtpVerified($row['id']);

        return true;
    }

    public function isPhoneVerified($phone, $purpose = 'inquiry') {
        $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $phone);

        $query = "SELECT id FROM otp_verifications \
                  WHERE phone_number = ? AND purpose = ? AND is_verified = 1 \
                  ORDER BY created_at DESC LIMIT 1";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ss", $cleanPhone, $purpose);
        $stmt->execute();
        $result = $stmt->get_result();
        $isVerified = $result && $result->num_rows > 0;
        $stmt->close();

        return $isVerified;
    }

    public function sendOtpSms($phone, $otp) {
        // For now, we'll just log the OTP (in production, integrate with SMS service)
        $message = "Your ESS Tracker verification code is: $otp\nValid for 5 minutes.";

        // Log the OTP for testing
        error_log("OTP SMS to $phone: $otp");

        // In production, you would integrate with SMS service like:
        // - Twilio
        // - AWS SNS
        // - Firebase
        // - etc.

        return true; // Assume success for now
    }

    private function cleanupExpiredOtps($phone) {
        $now = date('Y-m-d H:i:s');
        $query = "DELETE FROM otp_verifications WHERE phone_number = ? AND expires_at < ?";

        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ss", $phone, $now);
            $stmt->execute();
            $stmt->close();
        }
    }

    private function markOtpVerified($otpId) {
        $query = "UPDATE otp_verifications SET is_verified = 1 WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $otpId);
            $stmt->execute();
            $stmt->close();
        }
    }
}