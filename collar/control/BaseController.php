<?php
require_once '../vista/components/Notification.php';

/**
 * Clase base para todos los controladores
 * Maneja la funcionalidad común como sesiones y redirecciones
 */
class BaseController {
    protected function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function redirect($url, $message = null, $type = 'success') {
        if ($message) {
            $_SESSION['notification'] = [
                'message' => $message,
                'type' => $type
            ];
        }
        header('Location: ' . $url);
        exit;
    }

    protected function validateRequiredFields($fields, $data) {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    protected function sanitizeEmail($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    protected function logout() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }

    public static function showNotification() {
        if (isset($_SESSION['notification'])) {
            Notification::show(
                $_SESSION['notification']['message'],
                $_SESSION['notification']['type']
            );
            unset($_SESSION['notification']);
        }
    }
} 