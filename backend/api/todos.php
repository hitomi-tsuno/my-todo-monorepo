<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");
require_once 'db.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // 一覧取得
    $stmt = $db->query("SELECT * FROM todos ORDER BY id DESC");
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($todos);
    exit;
}

if ($method === 'POST') {
    // JSONを受け取る
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['text']) || trim($input['text']) === '') {
        echo json_encode(["error" => "text is required"]);
        exit;
    }

    $id = (int)round(microtime(true) * 1000);
    $text = $input['text'];
    $isdone = 0;
    $tags = $input['tags'] ?? '';

    // DBに追加
    $stmt = $db->prepare("INSERT INTO todos (id, text, isdone, tags) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $text, $isdone, $tags]);

    echo json_encode(["success" => true, "id" => $id]);
    exit;
}

if ($method === 'PUT') {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $id = $query['id'] ?? null;

    if (!$id) {
        echo json_encode(["error" => "id is required"]);
        exit;
    }

    // JSONを受け取る
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['text'])) {
        $stmt = $db->prepare("UPDATE todos SET text = ? WHERE id = ?");
        $stmt->execute([$input['text'], $id]);
    }

    if (isset($input['isdone'])) {
        $stmt = $db->prepare("UPDATE todos SET isdone = ? WHERE id = ?");
        $stmt->execute([$input['isdone'], $id]);
    }

    if (isset($input['tags'])) {
        $stmt = $db->prepare("UPDATE todos SET tags = ? WHERE id = ?");
        $stmt->execute([$input['tags'], $id]);
    }

    echo json_encode(["success" => true]);
    exit;
}

if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $id = $query['id'] ?? null;

    if ($id) {
        // 個別削除
        $stmt = $db->prepare("DELETE FROM todos WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["success" => true]);
        exit;
    }

    // ★ 完了済みの一括削除
    $stmt = $db->prepare("DELETE FROM todos WHERE isdone = 1");
    $stmt->execute();

    echo json_encode(["success" => true, "deleted" => "completed"]);
    exit;
}