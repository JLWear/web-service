<?php

require_once __DIR__ . '/../config.php';

function getAll($params) {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM projects WHERE deleted = 0");
    echo json_encode($stmt->fetchAll());
}

function get($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND deleted = 0");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetch());
}

function create() {
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);
    $fields = array_keys($data);
    $values = array_values($data);
    
    $placeholders = array_fill(0, count($fields), '?');
    
    $stmt = $pdo->prepare("INSERT INTO projects (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")");
    $stmt->execute($values);
    echo json_encode(['id' => $pdo->lastInsertId()]);
}

function update($id) {
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);
    $fields = [];
    $values = [];
    
    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value;
    }
    
    $values[] = $id;
    $stmt = $pdo->prepare("UPDATE projects SET " . implode(', ', $fields) . " WHERE id = ?");
    $stmt->execute($values);
    echo json_encode(['status' => 'success']);
}

function delete($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE projects SET deleted = 1 WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success']);
}
