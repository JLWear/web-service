<?php

require 'config.php';

function createControllerFile($tableName) {
    $template = file_get_contents('template/controller_template.php');
    $controllerContent = str_replace('{{table_name}}', $tableName, $template);
    file_put_contents("controllers/{$tableName}.php", $controllerContent);
}

function init() {
    global $pdo;
    
    // Créer le dossier 'controllers' s'il n'existe pas
    if (!is_dir('controllers')) {
        mkdir('controllers', 0755, true);
    }

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $controllerFile = __DIR__ . "/controllers/{$table}.php";
        
        if (!file_exists($controllerFile)) {
            createControllerFile($table);
        }
    }
}

init();
