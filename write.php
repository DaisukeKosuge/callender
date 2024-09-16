<?php
include('funcs.php');
$pdo = db_conn(); // データベース接続

// POSTデータを取得
$name = $_POST['name'];
$event_name = $_POST['event_name'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$user_id = $_POST['user_id'];

// ユーザー名がusersテーブルに存在するか確認
$stmt = $pdo->prepare("SELECT id FROM users WHERE name = :name");
$stmt->bindValue(':name', $name, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // ユーザーが存在する場合、user_idを取得
    $user_id = $user['id'];
} else {
    // ユーザーが存在しない場合、usersテーブルに新しいユーザーを追加してuser_idを取得
    $stmt = $pdo->prepare("INSERT INTO users(name) VALUES(:name)");
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $user_id = $pdo->lastInsertId(); // 新しく作成されたuser_idを取得
}

// SQL文を作成してスケジュールデータを登録
$stmt = $pdo->prepare("INSERT INTO schedule(name, event_name, start_time, end_time, user_id) VALUES(:name, :event_name, :start_time, :end_time, :user_id)");
$stmt->bindValue(':name', $name, PDO::PARAM_STR);
$stmt->bindValue(':event_name', $event_name, PDO::PARAM_STR);
$stmt->bindValue(':start_time', $start_time, PDO::PARAM_STR);
$stmt->bindValue(':end_time', $end_time, PDO::PARAM_STR);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT); // ユーザーIDを保存
$status = $stmt->execute();

// 結果をJSONで返す
if ($status === false) {
    $error = $stmt->errorInfo();
    echo json_encode(['status' => 'error', 'message' => $error[2]]);
} else {
    echo json_encode(['status' => 'success']);
}
?>
