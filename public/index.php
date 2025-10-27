<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../src/views');
$twig = new \Twig\Environment($loader, [
    'cache' => false, // Disable cache for development
    'debug' => true,
]);

// Add debug extension
$twig->addExtension(new \Twig\Extension\DebugExtension());

// Initialize Auth
$auth = new Auth();

// Get route from URL
$route = $_GET['route'] ?? '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($auth->login($email, $password)) {
                header('Location: /dashboard');
                exit;
            } else {
                $loginError = 'Invalid credentials. Try testuser@example.com / password123';
            }
            break;
            
        case 'signup':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (strlen($password) < 6) {
                $signupError = 'Password must be at least 6 characters long';
            } elseif ($password !== $confirmPassword) {
                $signupError = 'Passwords do not match';
            } else {
                if ($auth->signup($email, $password)) {
                    header('Location: /dashboard');
                    exit;
                } else {
                    $signupError = 'Email already exists. Please use a different email.';
                }
            }
            break;
            
        case 'logout':
            $auth->logout();
            header('Location: /login');
            exit;
            break;
    }
}

// Route handling
switch ($route) {
    case '':
    case 'home':
        echo $twig->render('home.twig', [
            'isAuthenticated' => $auth->isAuthenticated()
        ]);
        break;
        
    case 'login':
        if ($auth->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }
        echo $twig->render('login.twig', [
            'error' => $loginError ?? null
        ]);
        break;
        
    case 'signup':
        if ($auth->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }
        echo $twig->render('signup.twig', [
            'error' => $signupError ?? null
        ]);
        break;
        
    case 'dashboard':
        if (!$auth->isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        echo $twig->render('dashboard.twig', [
            'isAuthenticated' => true,
            'user' => $auth->getCurrentUser()
        ]);
        break;
        
    default:
        header('Location: /');
        exit;
}