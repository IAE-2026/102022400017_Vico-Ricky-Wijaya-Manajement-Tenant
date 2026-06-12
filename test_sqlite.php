<?php
try {
    $pdo = new PDO("sqlite:/tmp/database.sqlite");
    $pdo->exec("CREATE TABLE IF NOT EXISTS test (id int);");
    echo "SQLite works!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
