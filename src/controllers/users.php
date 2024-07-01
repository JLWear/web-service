<?php

require_once __DIR__ . '/../config.php';

/**
 * @access_level: 10
 */
function getAll($params) {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM users WHERE deleted = 0");
    echo json_encode($stmt->fetchAll());
}

/**
 * @access_level: 10
 */
function get($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND deleted = 0");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetch());
}

/**
 * @access_level: 10
 */
function create() {
    global $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    }

    $data['access_level'] = 10;

    $fields = array_keys($data);
    $values = array_values($data);
    
    $placeholders = array_fill(0, count($fields), '?');
    
    $stmt = $pdo->prepare("INSERT INTO users (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")");
    $stmt->execute($values);
    echo json_encode(['id' => $pdo->lastInsertId()]);
}

/**
 * @access_level: 10
 */
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
    $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
    $stmt->execute($values);
    echo json_encode(['status' => 'success']);
}

/**
 * @access_level: 10
 */
function delete($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET deleted = 1 WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success']);
}

/**
 * @access_level: 10
 */
function me() {
    global $pdo;
    $email = $_SERVER['PHP_AUTH_USER'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND deleted = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        unset($user['password']);
        echo json_encode($user);
    } else {
        header('HTTP/1.0 404 Not Found');
        echo json_encode(['error' => 'User not found']);
    }
}
