<?php
require_once __DIR__ . '/../db.php';
session_start();
if($_SERVER['REQUEST_METHOD']!=='POST'){ header('Location: ../public/index.php'); exit; }
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$pdo = getPDO();
$stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();
if($user && password_verify($password, $user['password_hash'])){
    $_SESSION['user'] = ['id'=>$user['id'],'username'=>$user['username'],'role'=>$user['role']];
    header('Location: ../public/index.php');
    exit;
}else{
    echo "<p>Login gagal. <a href='../public/index.php'>Kembali</a></p>";
}
