<?php

namespace App;

class Auth
{
    private const SESSION_KEY = 'ticketapp_session';
    private const USER_KEY = 'ticketapp_user';
    private const USERS_FILE = __DIR__ . '/../data/users.json';
    
    public function __construct()
    {
        // Ensure data directory exists
        $dataDir = dirname(self::USERS_FILE);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        // Create users file if it doesn't exist
        if (!file_exists(self::USERS_FILE)) {
            file_put_contents(self::USERS_FILE, json_encode([]));
        }
    }
    
    public function login($email, $password)
    {
        // Check test user
        if ($email === 'testuser@example.com' && $password === 'password123') {
            $_SESSION[self::SESSION_KEY] = true;
            $_SESSION[self::USER_KEY] = ['email' => $email];
            return true;
        }
        
        // Check registered users
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email && $user['password'] === $password) {
                $_SESSION[self::SESSION_KEY] = true;
                $_SESSION[self::USER_KEY] = ['email' => $email];
                return true;
            }
        }
        
        return false;
    }
    
    public function signup($email, $password)
    {
        $users = $this->getUsers();
        
        // Check if email exists
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return false;
            }
        }
        
        // Add new user
        $users[] = [
            'email' => $email,
            'password' => $password
        ];
        
        file_put_contents(self::USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
        
        // Auto-login
        $_SESSION[self::SESSION_KEY] = true;
        $_SESSION[self::USER_KEY] = ['email' => $email];
        
        return true;
    }
    
    public function logout()
    {
        unset($_SESSION[self::SESSION_KEY]);
        unset($_SESSION[self::USER_KEY]);
        session_destroy();
    }
    
    public function isAuthenticated()
    {
        return isset($_SESSION[self::SESSION_KEY]) && $_SESSION[self::SESSION_KEY] === true;
    }
    
    public function getCurrentUser()
    {
        return $_SESSION[self::USER_KEY] ?? null;
    }
    
    private function getUsers()
    {
        $json = file_get_contents(self::USERS_FILE);
        return json_decode($json, true) ?: [];
    }
}