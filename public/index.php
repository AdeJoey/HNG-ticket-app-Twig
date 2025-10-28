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

// Extract the route from the request URI (supports /login, /signup, /dashboard, /get-started)
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$page = trim($requestUri, '/');
if ($page === '') $page = 'home';

// If your app is served from a subdirectory, you might need to strip the base path.
// Example: if hosted at /project/, use str_replace('/project', '', $requestUri);

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
                $signupError = 'Password must be at least 6 characters long.';
            } elseif ($password !== $confirmPassword) {
                $signupError = 'Passwords do not match.';
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
    }
}

// Routing
switch ($page) {
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

    case 'get-started':
        header('Location: /signup');
        exit;

    default:
        http_response_code(404);
        if (file_exists(__DIR__ . '/../src/views/404.twig')) {
            echo $twig->render('404.twig');
        } else {
            echo $twig->render('home.twig');
        }
        break;
}
