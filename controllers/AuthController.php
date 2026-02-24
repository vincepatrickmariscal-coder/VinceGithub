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

        if (is_array($result)) {
            session_regenerate_id(true);
            $_SESSION['user'] = $result['email'];
            header("Location: index.php?action=home");
            exit();
        }

        return $result;
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }
}
?>