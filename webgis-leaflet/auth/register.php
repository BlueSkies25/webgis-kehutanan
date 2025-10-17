<?php
require_once __DIR__ . '/../db.php';
session_start();
if($_SERVER['REQUEST_METHOD']!=='POST'){ header('Location: ../public/index.php'); exit; }
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
if(!$username || !$password){ echo 'Isi username & password. <a href="../public/index.php">Kembali</a>'; exit; }
$pdo = getPDO();
$hash = password_hash($password, PASSWORD_DEFAULT);
try{
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, "user")');
    $stmt->execute([$username, $hash]);
    $id = $pdo->lastInsertId();
    $_SESSION['user'] = ['id'=>$id,'username'=>$username,'role'=>'user'];
    header('Location: ../public/index.php');
    exit;
}catch(Exception $e){
    echo 'Register gagal: '.htmlspecialchars($e->getMessage()).' <a href="../public/index.php">Kembali</a>';
}
