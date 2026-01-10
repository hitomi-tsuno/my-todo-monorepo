<?php
require_once __DIR__ . '/../vendor/autoload.php';

function getDB()
{
    // $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    // $dotenv->load(); // .envファイルから環境変数をロード
    // $dbPath = __DIR__ . '/../' . getenv('DB_PATH');
    $dbPath = __DIR__ . '/../data/todos.db';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}
