<?php
require_once __DIR__ . '/db.php';
if (php_sapi_name() !== 'cli') { echo "Run from CLI: php create_default_users.php\n"; exit; }
$users = [['username'=>'admin','password'=>'12345','role'=>'admin'], ['username'=>'user','password'=>'12345','role'=>'user']];
$pdo = getPDO();
foreach ($users as $u) {
    $hash = password_hash($u['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
        $stmt->execute([$u['username'], $hash, $u['role']]);
        echo "Created user: {$u['username']}\n";
    } catch (Exception $e) {
        echo "Skipping {$u['username']}: " . $e->getMessage() . "\n";
    }
}
