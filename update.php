<?php
include('funcs.php');
$pdo = db_conn(); // データベース接続

// POSTデータを取得
$event_id = $_POST['event_id']; // イベントID
$name = $_POST['name']; // 名前
$event_name = $_POST['event_name']; // 予定名
$start_time = $_POST['start_time']; // 開始時刻
$end_time = $_POST['end_time']; // 終了時刻

// SQLクエリでイベントを更新
$stmt = $pdo->prepare("UPDATE schedule SET name=:name, event_name=:event_name, start_time=:start_time, end_time=:end_time WHERE id=:event_id");
$stmt->bindValue(':name', $name, PDO::PARAM_STR);
$stmt->bindValue(':event_name', $event_name, PDO::PARAM_STR);
$stmt->bindValue(':start_time', $start_time, PDO::PARAM_STR);
$stmt->bindValue(':end_time', $end_time, PDO::PARAM_STR);
$stmt->bindValue(':event_id', $event_id, PDO::PARAM_INT);

$status = $stmt->execute(); // クエリ実行

if ($status === false) {
    $error = $stmt->errorInfo();
    echo json_encode(['status' => 'error', 'message' => $error[2]]);
} else {
    echo json_encode(['status' => 'success']);
}
?>
