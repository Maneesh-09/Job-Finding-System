<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'use_strict_mode' => true,
    ]);
}

function require_role(string $role): void
{
    if (($_SESSION['role'] ?? null) !== $role || !isset($_SESSION['username'])) {
        header('Location: ' . $role . '.php');
        exit;
    }
}

function login_account(mysqli $con, string $role, string $username, string $password): bool
{
    if (!in_array($role, ['admin', 'user', 'company'], true)) {
        return false;
    }
    $statement = $con->prepare("SELECT * FROM `$role` WHERE username = ? LIMIT 2");
    $statement->bind_param('s', $username);
    $statement->execute();
    $result = $statement->get_result();
    if ($result->num_rows !== 1) {
        return false;
    }
    $account = $result->fetch_assoc();
    $stored = $account['password'];
    $isHash = password_get_info($stored)['algo'] !== null;
    $valid = $isHash && password_verify($password, $stored);
    // Upgrade old admin passwords; never accept a modern hash as the password.
    if (!$isHash && $role === 'admin' && $stored !== '') {
        $valid = preg_match('/^[a-f0-9]{32}$/i', $stored)
            ? hash_equals(strtolower($stored), md5($password))
            : hash_equals($stored, $password);
    }
    if (!$valid) {
        return false;
    }
    if (!$isHash || password_needs_rehash($stored, PASSWORD_BCRYPT)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $update = $con->prepare("UPDATE `$role` SET password = ? WHERE username = ? AND password = ?");
        $update->bind_param('sss', $hash, $account['username'], $stored);
        $update->execute();
    }
    session_regenerate_id(true);
    $_SESSION = ['role' => $role, 'username' => $account['username']];
    if ($role === 'admin') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $account['username'];
    } elseif ($role === 'company') {
        $_SESSION['cid'] = (int) $account['cid'];
    } else {
        $_SESSION['uid'] = (int) $account['uid'];
    }
    return true;
}

function post_string(string $name, bool $trim = true): string
{
    $value = $_POST[$name] ?? '';
    return is_string($value) ? ($trim ? trim($value) : $value) : '';
}

function valid_job_expiry(string $date): bool
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date && $date >= date('Y-m-d');
}
