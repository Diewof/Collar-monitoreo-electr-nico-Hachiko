<?php
/**
 * Controlador de autenticación
 * Maneja la lógica de negocio relacionada con el inicio de sesión y registro
 */

// Incluir el modelo
require_once '../modelo/authmodel.php';
require_once 'BaseController.php';

// Iniciar sesión
session_start();

class AuthController extends BaseController {
    private $authModel;
    
    public function __construct() {
        parent::__construct();
        $this->authModel = new AuthModel();
    }
    
    public function processRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('../vista/login-registro.php', 'Acceso no permitido', 'error');
        }
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'login':
                $this->handleLogin();
                break;
            case 'register':
                $this->handleRegister();
                break;
            case 'forgot_password':
                $this->handleForgotPassword();
                break;
            case 'logout':
                $this->handleLogout();
                break;
            default:
                $this->redirect('../vista/login-registro.php', 'Acción no válida', 'error');
        }
    }
    
    private function handleLogin() {
        $email = $this->sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        if (!$this->validateRequiredFields(['email', 'password'], $_POST)) {
            $this->redirect('../vista/login-registro.php?form=login', 'Por favor, complete todos los campos', 'error');
        }
        
        $lockStatus = $this->authModel->isUserLocked($email, $ip_address);
        if ($lockStatus['locked']) {
            $this->redirect('../vista/login-registro.php?form=login', 
                "Demasiados intentos fallidos. Cuenta bloqueada por {$lockStatus['minutes_left']} minutos.", 
                'error'
            );
        }
        
        $result = $this->authModel->login($email, $password, $ip_address);
        
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['is_logged_in'] = true;
            $_SESSION['user_role'] = $result['user_role'] ?? 'user';
            
            // Si es el primer inicio de sesión, no redirigir
            if (isset($_SESSION['is_first_login'])) {
                $this->redirect('../vista/login-registro.php', 'Por favor, complete su información de propietario para continuar.', 'info');
            } else {
                $redirectUrl = $_SESSION['user_role'] === 'admin' ? '../vista/admin_main.php' : '../vista/main.php';
                $this->redirect($redirectUrl, '¡Bienvenido de nuevo!');
            }
        } else {
            $this->redirect('../vista/login-registro.php?form=login', 
                $result['error'] ?? 'Credenciales incorrectas', 
                'error'
            );
        }
    }
    
    private function handleRegister() {
        $email = $this->sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (!$this->validateRequiredFields(['email', 'password', 'confirm_password'], $_POST)) {
            $this->redirect('../vista/login-registro.php?form=register', 'Por favor, complete todos los campos', 'error');
        }
        
        if ($password !== $confirmPassword) {
            $this->redirect('../vista/login-registro.php?form=register', 'Las contraseñas no coinciden', 'error');
        }
        
        if (strlen($password) < 8) {
            $this->redirect('../vista/login-registro.php?form=register', 'La contraseña debe tener al menos 8 caracteres', 'error');
        }
        
        $result = $this->authModel->register($email, $password);
        
        if ($result['success']) {
            $this->redirect('../vista/login-registro.php?form=login', '¡Registro exitoso! Ahora puedes iniciar sesión');
        } else {
            $this->redirect('../vista/login-registro.php?form=register', 
                $result['error'] ?? 'Error al registrar el usuario', 
                'error'
            );
        }
    }
    
    private function handleForgotPassword() {
        $email = $this->sanitizeEmail($_POST['email'] ?? '');
        
        if (empty($email)) {
            $this->redirect('../vista/recuperar-password.php', 'Por favor, ingrese su correo electrónico', 'error');
        }
        
        $this->authModel->requestPasswordReset($email);
        $this->redirect('../vista/login-registro.php?form=login', 
            'Si el correo existe en nuestra base de datos, recibirá instrucciones para restablecer su contraseña'
        );
    }
    
    private function handleLogout() {
        $this->logout();
        $this->redirect('../vista/login-registro.php', 'Has cerrado sesión correctamente');
    }
}

// Crear instancia y procesar la solicitud
$controller = new AuthController();
$controller->processRequest();