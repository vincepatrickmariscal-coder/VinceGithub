<?php
require_once 'config/db.php';
require_once 'controllers/AuthController.php';

session_start();

$auth = new AuthController($conn);
$action = $_GET['action'] ?? 'login';

switch ($action) {

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = $auth->register($_POST['email'], $_POST['password'], $_POST['csrf_token']);
        }
        include 'views/register.php';
        break;

    case 'verify':
        $code = $_GET['code'] ?? '';
        $message = $auth->verify($code);
        include 'views/verify.php';
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = $auth->login($_POST['email'], $_POST['password'], $_POST['csrf_token']);
        }
        include 'views/login.php';
        break;

    case 'home':
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?action=login");
            exit();
        }
        include 'views/home.php';
        break;

    case 'logout':
        $auth->logout();
        break;

    default:
        include 'views/login.php';
}
?>