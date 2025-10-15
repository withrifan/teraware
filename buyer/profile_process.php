<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'update_profile') {
        $name = pg_escape_string($dbconn, $_POST['name']);
        $email = pg_escape_string($dbconn, $_POST['email']);
        $phone = pg_escape_string($dbconn, $_POST['phone']);
        $address = pg_escape_string($dbconn, $_POST['address']);
        
        $query = "UPDATE users SET name = $1, email = $2, phone_number = $3, address = $4 WHERE user_id = $5";
        $result = pg_query_params($dbconn, $query, array($name, $email, $phone, $address, $user_id));
        
        if ($result) {
            $_SESSION['user_name'] = $name; // Update session
            header('Location: profile.php?success=Profil berhasil diperbarui.');
        } else {
            header('Location: profile.php?error=Gagal memperbarui profil.');
        }
    }

    if ($action == 'update_password') {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            header('Location: profile.php?error=Password baru tidak cocok.');
            exit();
        }

        $query = "SELECT password FROM users WHERE user_id = $1";
        $result = pg_query_params($dbconn, $query, array($user_id));
        $user = pg_fetch_assoc($result);

        if (password_verify($old_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = $1 WHERE user_id = $2";
            pg_query_params($dbconn, $update_query, array($hashed_password, $user_id));
            header('Location: profile.php?success=Password berhasil diubah.');
        } else {
            header('Location: profile.php?error=Password lama salah.');
        }
    }
}
exit();