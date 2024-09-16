<?php
include('funcs.php');
$pdo = db_conn(); // データベース接続

// POSTデータを取得
$event_id = $_POST['event_id']; // 削除するイベントID

// SQLクエリでイベントを削除
$stmt = $pdo->prepare("DELETE FROM schedule WHERE id = :event_id");
$stmt->bindValue(':event_id', $event_id, PDO::PARAM_INT);
$status = $stmt->execute(); // クエリ実行

if ($status === false) {
    $error = $stmt->errorInfo();
    echo json_encode(['status' => 'error', 'message' => $error[2]]);
} else {
    echo json_encode(['status' => 'success']);
}
?>
