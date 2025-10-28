<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Initialize Twig
$loader = new FilesystemLoader(__DIR__ . '/../src/views');
$twig = new Environment($loader, [
    'cache' => false,
    'debug' => true,
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

// Initialize Auth
$auth = new Auth();

// --- ✅ FIXED ROUTING LOGIC ---
$requestUri = strtok($_SERVER['REQUEST_URI'], '?'); // Strip query string
$requestUri = trim($requestUri, '/');
$requestUri = str_replace(['public/', 'public'], '', $requestUri);
$page = $requestUri === '' ? 'home' : $requestUri;

// --- ✅ HANDLE POST REQUESTS ---
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
                    $signupError = 'Email already exists. Please use a different one.';
                }
            }
            break;

        case 'logout':
            $auth->logout();
            header('Location: /login');
            exit;
    }
}

// --- ✅ ROUTE HANDLING ---
switch ($page) {
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

    case 'logout':
        $auth->logout();
        header('Location: /login');
        exit;
        break;

    default:
        // Catch-all for unknown routes
        header("HTTP/1.0 404 Not Found");
        echo $twig->render('home.twig', [
            'error' => 'Page not found',
            'isAuthenticated' => $auth->isAuthenticated()
        ]);
        break;
}
