<?php
require __DIR__ . '/../PHPMailer-master/src/Exception.php';
require __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class User {
                                                                                 
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* REGISTER */
    public function register($email, $password) {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }

        if (strlen($password) < 6) {
            return "Password must be at least 6 characters.";
        }

        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return "Email already registered.";
        }
        $stmt->close();

        $hashedPassword   = password_hash($password, PASSWORD_BCRYPT);
        $verificationCode = bin2hex(random_bytes(16));

        // Insert user (keep verification_code column for backwards compatibility,
        // but main verification tracking is done in email_verifications table).
        $stmt = $this->conn->prepare(
            "INSERT INTO users (email, password, verification_code, is_verified)
             VALUES (?, ?, ?, 0)"
        );
        $stmt->bind_param("sss", $email, $hashedPassword, $verificationCode);

        if ($stmt->execute()) {

            $userId = (int)$stmt->insert_id;
            $stmt->close();

            // Store verification token in dedicated email_verifications table
            $createdAt = (new DateTime())->format('Y-m-d H:i:s');
            $stmt = $this->conn->prepare(
                "INSERT INTO email_verifications (user_id, token, created_at)
                 VALUES (?, ?, ?)"
            );
            $stmt->bind_param("iss", $userId, $verificationCode, $createdAt);
            $stmt->execute();
            $stmt->close();

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'vincepatrickmariscal@gmail.com';
                $mail->Password   = 'uagvkfxqxxsyopqx';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('vincepatrickmariscal@gmail.com', 'Email Auth System');
                $mail->addAddress($email);

                $link = "http://localhost/php-email-auth/index.php?action=verify&code=$verificationCode";

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email';
                $mail->Body = "
                    <h3>Email Verification</h3>
                    <p>Click below to verify your account:</p>
                    <a href='$link'>Verify Account</a>
                ";

                $mail->send();
                return "Registration successful! Check your email.";

            } catch (Exception $e) {
                return "Mailer Error: {$mail->ErrorInfo}";
            }
        }

        return "Registration failed.";
    }

    /* VERIFY */
    public function verify($code) {

        // Find matching verification record
        $stmt = $this->conn->prepare(
            "SELECT user_id FROM email_verifications WHERE token = ? LIMIT 1"
        );
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return false;
        }

        $userId = (int)$row['user_id'];

        // Mark user as verified
        $stmt = $this->conn->prepare(
            "UPDATE users
             SET is_verified = 1,
                 verification_code = NULL
             WHERE id = ?"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $updated = $stmt->affected_rows > 0;
        $stmt->close();

        if ($updated) {
            // Consume verification record so link cannot be reused
            $del = $this->conn->prepare("DELETE FROM email_verifications WHERE user_id = ?");
            $del->bind_param("i", $userId);
            $del->execute();
            $del->close();
        }

        return $updated;
    }

    /* LOGIN */
    public function login($email, $password) {

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (!password_verify($password, $user['password'])) {
                return "Invalid email or password.";
            }

            if ($user['is_verified'] != 1) {
                return "Please verify your email first.";
            }

            return $user;
        }

        return "Invalid email or password.";
    }

    /* OTP: create and verify */
    public function createOtp(int $userId): string {

        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $stmt = $this->conn->prepare("DELETE FROM otp_codes WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        $expiresAt = (new DateTime('+5 minutes'))->format('Y-m-d H:i:s');

        $stmt = $this->conn->prepare(
            "INSERT INTO otp_codes (user_id, otp_code, expires_at)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iss", $userId, $otp, $expiresAt);
        $stmt->execute();
        $stmt->close();

        return $otp;
    }

    public function verifyOtp(int $userId, string $otp): bool {

        $now = (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $this->conn->prepare(
            "SELECT id FROM otp_codes
             WHERE user_id = ? AND otp_code = ? AND expires_at >= ?
             LIMIT 1"
        );
        $stmt->bind_param("iss", $userId, $otp, $now);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $id = (int)$row['id'];
            $del = $this->conn->prepare("DELETE FROM otp_codes WHERE id = ?");
            $del->bind_param("i", $id);
            $del->execute();
            $del->close();
            return true;
        }

        return false;
    }

    public function sendOtpEmail(string $email, string $otp): bool {

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'vincepatrickmariscal@gmail.com';
            $mail->Password   = 'uagvkfxqxxsyopqx';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('vincepatrickmariscal@gmail.com', 'Email Auth System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your Login OTP Code';
            $mail->Body = "
                <h3>OTP Verification</h3>
                <p>Your one-time password is:</p>
                <p style='font-size:20px;font-weight:bold;'>$otp</p>
                <p>This code will expire in 5 minutes.</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>