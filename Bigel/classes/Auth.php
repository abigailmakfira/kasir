<?php
require_once 'Koneksi.php';

class Auth {
    private $conn;
    private $logoPath = '../logo.jpeg';

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $koneksi = new Koneksi();
        $this->conn = $koneksi->getConnection();
    }

    public function getLogo() {
        return $this->logoPath;
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['login_success'] = true;
                return true;
            }
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        header('Location: login.php');
        exit;
    }


}
?>