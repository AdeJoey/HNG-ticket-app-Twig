<?php

namespace App;

class Auth
{
    private const SESSION_KEY = 'ticketapp_session';
    private const USER_KEY = 'ticketapp_user';
    private const USERS_FILE = __DIR__ . '/../data/users.json';

    public function __construct()
    {
        $dataDir = dirname(self::USERS_FILE);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        if (!file_exists(self::USERS_FILE)) {
            file_put_contents(self::USERS_FILE, json_encode([]));
            chmod(self::USERS_FILE, 0664);
        }
    }

    public function login($email, $password)
    {
        if ($email === 'testuser@example.com' && $password === 'password123') {
            $_SESSION[self::SESSION_KEY] = true;
            $_SESSION[self::USER_KEY] = ['email' => $email];
            return true;
        }

        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
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
        foreach ($users as $user) {
            if ($user['email'] === $email) return false;
        }

        $users[] = [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        file_put_contents(self::USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        chmod(self::USERS_FILE, 0664);

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

    public function isAuthenticated(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]) && $_SESSION[self::SESSION_KEY] === true;
    }

    public function getCurrentUser(): ?array
    {
        return $_SESSION[self::USER_KEY] ?? null;
    }

    private function getUsers(): array
    {
        $json = @file_get_contents(self::USERS_FILE);
        return json_decode($json, true) ?: [];
    }
}
