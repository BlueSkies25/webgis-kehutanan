<?php
require_once __DIR__ . '/../db.php';
session_start();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$pdo = getPDO();
function json($d){ header('Content-Type: application/json'); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }
$user = $_SESSION['user'] ?? null;

// status
if ($action === 'status') {
    json(['user'=>$user]);
}

// list
if ($action === 'list' && $method === 'GET') {
    $stmt = $pdo->query('SELECT id, name, description, type, geojson, created_by, created_at FROM features ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();
    json(['success'=>true,'features'=>$rows]);
}

// create
if ($action === 'create' && $method === 'POST') {
    if (!$user || $user['role'] !== 'admin') json(['success'=>false,'error'=>'forbidden']);
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) json(['success'=>false,'error'=>'invalid json']);
    $name = $input['properties']['name'] ?? null;
    $desc = $input['properties']['description'] ?? null;
    $geom = $input['geometry'] ?? null;
    $type = $geom['type'] ?? null;
    $geojson = json_encode($geom, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare('INSERT INTO features (name, description, type, geojson, created_by) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$name, $desc, $type, $geojson, $user['id']]);
    json(['success'=>true,'id'=>$pdo->lastInsertId()]);
}

// delete
if ($action === 'delete' && $method === 'POST') {
    if (!$user || $user['role'] !== 'admin') json(['success'=>false,'error'=>'forbidden']);
    $id = intval($_POST['id'] ?? 0);
    if (!$id) json(['success'=>false,'error'=>'missing id']);
    $stmt = $pdo->prepare('DELETE FROM features WHERE id = ?');
    $stmt->execute([$id]);
    json(['success'=>true]);
}

// update
if ($action === 'update' && $method === 'POST') {
    if (!$user || $user['role'] !== 'admin') json(['success'=>false,'error'=>'forbidden']);
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) json(['success'=>false,'error'=>'invalid json']);
    $id = intval($input['id'] ?? 0);
    if (!$id) json(['success'=>false,'error'=>'missing id']);
    $name = $input['properties']['name'] ?? null;
    $desc = $input['properties']['description'] ?? null;
    $geom = $input['geometry'] ?? null;
    $geojson = $geom ? json_encode($geom, JSON_UNESCAPED_UNICODE) : null;
    $parts = []; $vals = [];
    if ($name !== null) { $parts[]='name=?'; $vals[]=$name; }
    if ($desc !== null) { $parts[]='description=?'; $vals[]=$desc; }
    if ($geojson !== null) { $parts[]='geojson=?'; $vals[]=$geojson; }
    if (empty($parts)) json(['success'=>false,'error'=>'nothing to update']);
    $vals[] = $id;
    $sql = 'UPDATE features SET '.implode(',', $parts).' WHERE id=?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($vals);
    json(['success'=>true]);
}

json(['success'=>false,'error'=>'unknown action']);
