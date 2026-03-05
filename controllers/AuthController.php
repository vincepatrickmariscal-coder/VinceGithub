<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {

    private $userModel;

    public function __construct($conn) {
        $this->userModel = new User($conn);
    }

    public function register($email, $password, $csrf) {

        if ($csrf !== $_SESSION['csrf_token']) {
            return "Invalid CSRF token.";
        }

        return $this->userModel->register($email, $password);
    }

    public function verify($code) {
        return $this->userModel->verify($code);
    }

    public function login($email, $password, $csrf) {

        if ($csrf !== $_SESSION['csrf_token']) {
            return "Invalid CSRF token.";
        }

        $result = $this->userModel->login($email, $password);

        if (!is_array($result)) {
            return $result;
        }

        // Start OTP flow
        $userId = (int)$result['id'];
        $otp    = $this->userModel->createOtp($userId);

        if (!$this->userModel->sendOtpEmail($email, $otp)) {
            return "Could not send OTP email. Please try again.";
        }

        $_SESSION['pending_user_id']    = $userId;
        $_SESSION['pending_user_email'] = $result['email'];

        header("Location: index.php?action=otp");
        exit();
    }

    public function verifyOtp($otp, $csrf) {

        if ($csrf !== $_SESSION['csrf_token']) {
            return "Invalid CSRF token.";
        }

        if (empty($_SESSION['pending_user_id'])) {
            return "Session expired. Please log in again.";
        }

        $userId = (int)$_SESSION['pending_user_id'];

        if ($this->userModel->verifyOtp($userId, $otp)) {
            $email = $_SESSION['pending_user_email'] ?? '';

            unset($_SESSION['pending_user_id'], $_SESSION['pending_user_email']);

            session_regenerate_id(true);
            $_SESSION['user'] = $email;

            header("Location: index.php?action=home");
            exit();
        }

        return "Invalid or expired OTP code.";
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }
}
?>