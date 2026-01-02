<!-- api\setup.php -->
<!-- Todo テーブルを作成する（初回だけ実行） -->
<?php
require_once 'db.php';

$db = getDB();

$db->exec("
CREATE TABLE IF NOT EXISTS todos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    text TEXT NOT NULL,
    completed INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL
);
");

echo "Setup completed.";
