<?php
require_once __DIR__ . '/../vendor/autoload.php';

function getDB()
{
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load(); // .envファイルから環境変数をロード
    $db = new PDO('sqlite:' . __DIR__ . $_ENV['DB_PATH']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}
