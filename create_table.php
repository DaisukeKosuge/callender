<?php
// funcs.phpをインクルードしてデータベース接続を使用
include('funcs.php');

// データベース接続
$pdo = db_conn();

// ユーザーテーブルの作成
$sql_users = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL
);
";

// スケジュールテーブルの作成
$sql_schedule = "
CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    event_name VARCHAR(255) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
";

// テーブル作成の実行
try {
    $pdo->exec($sql_users);
    $pdo->exec($sql_schedule);
    echo "テーブルが正常に作成されました。";
} catch (PDOException $e) {
    echo "テーブル作成エラー: " . $e->getMessage();
}
?>
    