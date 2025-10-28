<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../src/views');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
    'debug' => true,
]);

$twig->addExtension(new \Twig\Extension\DebugExtension());

// Initialize Auth
$auth = new Auth();

// Simple routing - get page from query parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($auth->login($email, $password)) {
                header('Location: index.php?page=dashboard');
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
                    header('Location: index.php?page=dashboard');
                    exit;
                } else {
                    $signupError = 'Email already exists. Please use a different email.';
                }
            }
            break;
            
        case 'logout':
            $auth->logout();
            header('Location: index.php?page=login');
            exit;
            break;
    }
}

// Route handling
switch ($page) {
    case 'home':
        echo $twig->render('home.twig', [
            'isAuthenticated' => $auth->isAuthenticated()
        ]);
        break;
        
    case 'login':
        if ($auth->isAuthenticated()) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        echo $twig->render('login.twig', [
            'error' => $loginError ?? null
        ]);
        break;
        
    case 'signup':
        if ($auth->isAuthenticated()) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        echo $twig->render('signup.twig', [
            'error' => $signupError ?? null
        ]);
        break;
        
    case 'dashboard':
        if (!$auth->isAuthenticated()) {
            header('Location: index.php?page=login');
            exit;
        }
        echo $twig->render('dashboard.twig', [
            'isAuthenticated' => true,
            'user' => $auth->getCurrentUser()
        ]);
        break;
        
    default:
        header('Location: index.php?page=home');
        exit;
}