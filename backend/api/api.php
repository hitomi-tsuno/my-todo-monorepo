<?php
//<!-- api\api.php -->
// true = 開発モード, false = 本番モード
define("DEBUG_MODE", true);

$db_path = '../data/todos.db';

// DB接続/作成
$db = new PDO('sqlite:' . $db_path);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_POST['action'] ?? '';
try {
  switch ($action) {
    case 'add':
      $sql = "INSERT INTO todos (id, text, isdone, tags) VALUES (?, ?, 0, ?)";
      $stmt = $db->prepare($sql);
      $id = (int)round(microtime(true) * 1000);
      // $stmt->execute([$id, $_POST['text'], $_POST['tags']]);
      executeWithLog($stmt, [$id, $_POST['text'], $_POST['tags']]);
      echo json_encode(['status' => 'ok', 'id' => $id]);
      break;

    case 'delete':
      $sql = "DELETE FROM todos WHERE id = ?";
      $stmt = $db->prepare($sql);
      // $stmt->execute([$_POST['id']]);
      executeWithLog($stmt, [$_POST['id']]);
      echo json_encode(['status' => 'ok']);
      break;

    case 'delete_done':
      $sql = "DELETE FROM todos WHERE isdone = 1";
      $stmt = $db->prepare($sql);
      // $stmt->execute();
      executeWithLog($stmt, []);
      echo json_encode(['status' => 'ok']);
      break;

    case 'toggle':
      $sql = "UPDATE todos SET isdone = NOT isdone WHERE id = ?";
      $stmt = $db->prepare($sql);
      // $stmt->execute([$_POST['id']]);
      executeWithLog($stmt, [$_POST['id']]);
      echo json_encode(['status' => 'ok']);
      break;

    case 'toggle_all':
      $sql = "UPDATE todos SET isdone = ?";
      $stmt = $db->prepare($sql);
      // $stmt->execute([$_POST['isdone']]);
      executeWithLog($stmt, [$_POST['isdone']]);

      echo json_encode(['status' => 'ok']);
      break;

    case 'update':
      $id = $_POST['id'] ?? '';
      $text = $_POST['text'] ?? '';
      $tags = $_POST['tags'] ?? '';
      $sql = "UPDATE todos SET text = ?, tags = ? WHERE id = ?";
      $stmt = $db->prepare($sql);
      // $stmt->execute([$text, $tags, $id]);
      executeWithLog($stmt, [$text, $tags, $id]);

      break;

    case 'list_tags':
      $sql = "SELECT DISTINCT tags FROM todos WHERE tags <> '' ORDER BY tags ASC";
      $params = [];
      $stmt = $db->prepare($sql);
      // $stmt->execute($params);
      // $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
      executeWithLog($stmt, $params, 'fetchAll');
      echo json_encode($todos);
      break;

    case 'list':
      $filterIsDone = $_POST['filterIsDone'] ?? null;
      $filterText = $_POST['filterText'] ?? '';
      $filterTags = $_POST['filterTags'] ?? [];

      $sql = "SELECT * FROM todos WHERE 1";
      $params = [];

      // フィルター処理
      if ($filterIsDone === "0") {
        $sql .= " AND isdone = 1";
      } elseif ($filterIsDone === "1") {
        $sql .= " AND isdone = 0";
      }

      if ($filterText !== '') {
        $sql .= " AND text LIKE ?";
        $params[] = '%' . $filterText . '%';
      }

      // タグで絞り込み　複数タグ対応　OR条件を組み立て
      $conditions = [];
      foreach ($filterTags as $tag) {
        $conditions[] = "tags LIKE '%" . $tag . "%'";
      }
      if (count($conditions) > 0) {
        $sql .= " AND (" . implode(" OR ", $conditions) . ")";
      }

      // ソート処理
      $sortKey = $_POST['sortKey'] ?? 'id';
      $sortOrder = $_POST['sortOrder'] ?? 'desc';

      // 安全なカラム名と順序だけ許可
      $allowedKeys = ['id', 'text', 'isdone', 'tags'];
      $allowedOrders = ['asc', 'desc'];

      if (!in_array($sortKey, $allowedKeys)) $sortKey = 'id';
      if (!in_array($sortOrder, $allowedOrders)) $sortOrder = 'desc';

      $sql .= " ORDER BY $sortKey $sortOrder";

      // $sql .= " bbbbb"; // Try・Catchテスト用

      $stmt = $db->prepare($sql);
      // $stmt->execute($params);
      // $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
      executeWithLog($stmt, $params, 'fetchAll');

      echo json_encode($todos);
      break;
  }
} catch (Exception $e) {
  // ログにエラー出力（PHPの関数を使用）
  error_log($e->getMessage());
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);

  // エラー時にSQLと値をログ出力
  // [注意]
  // $sqlに値を設定する
  logError($e->getTraceAsString(), $e->getMessage(), $sql, ['text' => $text, 'id' => $id, 'tags' => $tags, 'params' => $params]);
}

// エラーログ出力関数
function logError($trace, $error,  $sql = null, $values = null)
{
  $logFile = "C:/work/study/php/my-todo-app/log/error.log";

  if (DEBUG_MODE) {
    // 開発モード: SQLと値を詳細に出力
    // <日付>
    error_log(str_repeat("－", 50) . PHP_EOL, 3, $logFile);
    error_log(date('Y/m/d H:i:s') . PHP_EOL, 3, $logFile);
    error_log('Stack Trace: '  . PHP_EOL, 3, $logFile);
    error_log($trace . PHP_EOL, 3, $logFile);
    error_log('エラー内容: ' . $error . PHP_EOL, 3, $logFile);
    error_log('$sql: ' . $sql . PHP_EOL, 3, $logFile);
    error_log('$_POST: ' . json_encode($_POST) . PHP_EOL, 3, $logFile);
    error_log('Values: ' . json_encode($values) . PHP_EOL, 3, $logFile);
  } else {
    // 本番モード: 最小限の情報のみ
    // 未出力
  }
}

// SQL実行関数（ログ付き）
function executeWithLog(PDOStatement $stmt, array $params, string $method = '')
{
  if (DEBUG_MODE === false) {
    // 本番モード時はそのまま実行
    if ($method === '') {
      return $stmt->execute($params);
    } elseif ($method === 'fetchAll') {
      global $todos;
      $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $todos;
    }
  }

  $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

  // デバッグモード時はSQLと値をログに出力
  $logMessage = "";
  $logMessage .= str_repeat("－", 50) . "\n";
  $logMessage .= date('Y/m/d H:i:s') . "\n";
  $logMessage .= 'Stack Trace: ' . "\n";
  $logMessage .= formatBacktrace($trace);
  $logMessage .= 'sql: ' . $stmt->queryString . "\n";
  $logMessage .= '$params: ' . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n";
  if (count($params) > 0) {
    $sentSql = chageSendSql($stmt->queryString, $params);
    $logMessage .= 'sent sql: ' . $sentSql . "\n";
  }

  // 実際のSQL実行
  $return = $stmt->execute($params);
  if ($method === 'fetchAll') {
    global $todos;
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $return = $todos;
  }

  if ($return === false) {
    $logMessage .= 'SQL実行エラー：error.logをご確認ください' . "\n";
  }
  // デバッグモード時はSQLと値をログに出力
  $sql = strtoupper(trim($stmt->queryString)); // 大文字に変換

  if (str_starts_with($sql, "SELECT ")) {
    $logMessage .= 'SELECT件数: ' . count($return) . "\n";
  }
  if (str_starts_with($sql, "UPDATE ")) {
    $logMessage .= 'UPDATE件数: ' . $stmt->rowCount() . "\n";
  }
  if (str_starts_with($sql, "INSERT ")) {
    $logMessage .= 'INSERT件数: ' . $stmt->rowCount() . "\n";
  }
  if (str_starts_with($sql, "DELETE ")) {
    $logMessage .= 'DELETE件数: ' . $stmt->rowCount() . "\n";
  }
  writeSqlLog($logMessage);
  return $return;
}

// バックトレースをフォーマットする関数
function formatBacktrace(array $trace): string
{
  $result = "";
  foreach ($trace as $i => $frame) {
    $file = $frame['file'] ?? '[internal function]';
    $line = $frame['line'] ?? '';
    $func = $frame['function'] ?? '';
    $class = $frame['class'] ?? '';
    $type = $frame['type'] ?? '';
    $result .= "#{$i} {$file}({$line}): {$class}{$type}{$func}()" . PHP_EOL;
  }
  $result .= "#" . count($trace) . " {main}" . PHP_EOL; // 最後に {main} を追加
  return $result;
}

// 
function writeSqlLog(string $message)
{
  $logFile = "C:/work/study/php/my-todo-app/log/sql.log";

  $fp = fopen($logFile, 'a');
  if ($fp) {
    if (flock($fp, LOCK_EX)) { // 排他ロック
      fwrite($fp, $message . PHP_EOL);
      fflush($fp); // バッファを即書き込み
      flock($fp, LOCK_UN); // ロック解除
    }
    fclose($fp);
  }
}

// SQL文をパラメータを含めたSQLに変換する関数
function chageSendSql(string $string, array $params)
{
  $count = 0; // 配列のインデックスとして使用するカウンター
  $replacements = []; // 置換値の配列
  $replacements = array_map(function ($value) {
    return "'" . $value . "'";
  }, $params);

  // "?"→paramsを順番に置換
  $result = preg_replace_callback('/\?/', function ($matches) use (&$replacements, &$count) {
    // 置換値の配列から現在のカウンターに対応する値を取得し、カウンターをインクリメント
    return isset($replacements[$count]) ? $replacements[$count++] : $matches[0];
  }, $string);

  return $result;
}
