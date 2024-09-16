<?php
// データベース接続情報を変数で管理
$db_name = '*********';        // データベース名
$db_user = '******';           // データベースのユーザー名
$db_password = '********';   // データベースのパスワード
$db_host = '***********';           // データベースホスト名（通常はlocalhost）

// データベース接続関数
function db_conn() {
    global $db_name, $db_user, $db_password, $db_host;

    try {
        $pdo = new PDO(
            'mysql:dbname=' . $db_name . ';host=' . $db_host . ';charset=utf8',
            $db_user,
            $db_password
        );
        return $pdo;
    } catch (PDOException $e) {
        exit('DB Connection Error: ' . $e->getMessage());
    }
}
?>
