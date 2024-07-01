<?php

require 'config.php';
require 'init.php';

header('Content-Type: application/json');

function checkLogin() {
    global $pdo;

    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="My API"');
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $email = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND deleted = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    header('WWW-Authenticate: Basic realm="My API"');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$currentUser = checkLogin();

function checkAccessLevel($controller_name, $method_name, $currentUser) {
    $controller_file = __DIR__ . "/controllers/{$controller_name}.php";

    if (file_exists($controller_file)) {
        require_once $controller_file;

        if (!function_exists($method_name)) {
            header("HTTP/1.0 404 Not Found");
            echo json_encode(['error' => 'Method not found']);
            exit;
        }

        $reflectionFunction = new ReflectionFunction($method_name);
        $docComment = $reflectionFunction->getDocComment();

        if ($docComment !== false && preg_match('/@access_level:\s*(\d+)/', $docComment, $matches)) {
            $requiredLevel = (int)$matches[1];
            if ($currentUser['access_level'] < $requiredLevel) {
                header("HTTP/1.0 403 Forbidden");
                echo json_encode(['error' => 'Insufficient access level']);
                exit;
            }
        }
    } else {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['error' => 'Controller file not found']);
        exit;
    }
}


$request_method = $_SERVER["REQUEST_METHOD"];
$path_info = isset($_SERVER['PATH_INFO']) ? explode('/', trim($_SERVER['PATH_INFO'], '/')) : [];

if (!empty($path_info)) {
    $controller_name = $path_info[0];
    
    if ($controller_name === 'me') {
        // Gérer la requête pour /me
        if ($request_method === 'GET') {
            require __DIR__ . '/controllers/users.php'; // Assurez-vous que le fichier users.php est inclus
            me();
        } else {
            header("HTTP/1.0 405 Method Not Allowed");
            echo json_encode(['error' => 'Method not allowed']);
        }
    } else {
        $controller_file = __DIR__ . "/controllers/{$controller_name}.php";
        
        if (file_exists($controller_file)) {
            require $controller_file;
            
            $id = $path_info[1] ?? null;
            $params = $_GET;
            
            switch ($request_method) {
                case 'GET':
                    if ($id !== null) {
                        checkAccessLevel($controller_name, 'get', $currentUser);
                        get($id);
                    } else {
                        checkAccessLevel($controller_name, 'getAll', $currentUser);
                        getAll($params);
                    }
                    break;
                case 'POST':
                    checkAccessLevel($controller_name, 'create', $currentUser);
                    create();
                    break;
                case 'PUT':
                    if ($id !== null) {
                        checkAccessLevel($controller_name, 'update', $currentUser);
                        update($id);
                    } else {
                        header("HTTP/1.0 400 Bad Request");
                        echo json_encode(['error' => 'ID is required for PUT request']);
                    }
                    break;
                case 'DELETE':
                    if ($id !== null) {
                        checkAccessLevel($controller_name, 'delete', $currentUser);
                        delete($id);
                    } else {
                        header("HTTP/1.0 400 Bad Request");
                        echo json_encode(['error' => 'ID is required for DELETE request']);
                    }
                    break;
                default:
                    header("HTTP/1.0 405 Method Not Allowed");
                    echo json_encode(['error' => 'Method not allowed']);
                    break;
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            echo json_encode(['error' => 'Controller not found']);
        }
    }
} else {
    header("HTTP/1.0 400 Bad Request");
    echo json_encode(['error' => 'Invalid request']);
}
