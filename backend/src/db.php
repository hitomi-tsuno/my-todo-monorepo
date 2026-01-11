<?php
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/SqlLogger.php';
require_once __DIR__ . '/ErrorLogger.php';

function getDB()
{
    $dbPath = __DIR__ . '/../data/todos.db';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

/**
 * SQL実行 + ログ記録
 *
 * @param PDO $db
 * @param string $sql
 * @param array $params
 * @return PDOStatement
 */
function executeWithLog(PDO $db, string $sql, array $params = [])
{
    $sqlLogger   = new SqlLogger();
    $errorLogger = new ErrorLogger();

    try {
        // SQLログ
        $sqlLogger->log($sql, $params);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    } catch (Exception $e) {

        // エラーログ
        $errorLogger->log($e->getMessage(), [
            'sql' => $sql,
            'params' => $params
        ]);

        throw $e; // 上位に再スロー
    }
}
