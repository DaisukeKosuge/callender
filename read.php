<?php
session_start(); // セッションの開始

// セッションにuser_idがない場合のデフォルト値設定
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // デフォルトのユーザーIDを1に設定
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カレンダー表示</title>

    <!-- FullCalendarのCSSとJSを読み込む -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

    <!-- jQueryの読み込み -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- 外部CSSファイルの読み込み -->
    <link rel="stylesheet" href="./style.css">
</head>
<body>

    <h1>スケジュールカレンダー</h1>

    <!-- 新規スケジュール作成ボタン -->
    <button id="createScheduleButton">スケジュールを作成する</button>

    <!-- カレンダー表示領域 -->
    <div id="calendar"></div>

    <!-- 新規スケジュール作成用モーダル -->
    <div id="createModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>新規スケジュール作成</h2>
            <form id="createScheduleForm">
                名前: <input type="text" id="name" name="name" required><br>
                予定名: <input type="text" id="event_name" name="event_name" required><br>
                開始時刻: <input type="datetime-local" id="start_time" name="start_time" required><br>
                終了時刻: <input type="datetime-local" id="end_time" name="end_time" required><br>
                <input type="hidden" id="user_id" name="user_id" value="<?= htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8') ?>"><!-- セッションから取得 -->
                <button type="submit">作成</button>
                <button type="button" id="closeCreateModal">キャンセル</button>
            </form>
        </div>
    </div>

    <!-- 編集・削除用モーダル -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>スケジュール編集・削除</h2>
            <form id="editScheduleForm">
                名前: <input type="text" id="edit_name" name="name" required><br>
                予定名: <input type="text" id="edit_event_name" name="event_name" required><br>
                開始時刻: <input type="datetime-local" id="edit_start_time" name="start_time" required><br>
                終了時刻: <input type="datetime-local" id="edit_end_time" name="end_time" required><br>
                <input type="hidden" id="edit_event_id" name="event_id"><!-- 編集対象のイベントID -->
                <button type="submit">更新</button>
                <button type="button" id="deleteScheduleButton">削除</button>
                <button type="button" id="closeEditModal">キャンセル</button>
            </form>
        </div>
    </div>

    <!-- jQueryとFullCalendar用のスクリプト -->
    <script>
        $(document).ready(function() {
            // ユーザーごとの色を定義
            var userColors = {
                1: '#FFAB91',  // ユーザーIDが1のユーザーの色
                2: '#81D4FA',  // ユーザーIDが2のユーザーの色
                3: '#A5D6A7',  // ユーザーIDが3のユーザーの色
                4: '#FFD700'   // その他のユーザー
            };

            // カレンダーの初期化
            var calendarEl = $('#calendar')[0];
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', // 月表示
                locale: 'ja', // 日本語化
                events: <?php
                    include('funcs.php');
                    $pdo = db_conn();
                    $stmt = $pdo->prepare("SELECT * FROM schedule");
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // イベントデータを準備
                    $events = array();
                    foreach ($result as $row) {
                        $userId = isset($row['user_id']) ? $row['user_id'] : 0;
                        $color = isset($userColors[$userId]) ? $userColors[$userId] : '#B39DDB'; // 色を割り当て

                        $events[] = array(
                            'id' => $row['id'],
                            'title' => $row['name'] . "：" . $row['event_name'], // 名前と内容を表示
                            'start' => $row['start_time'],
                            'end' => $row['end_time'],
                            'backgroundColor' => $color,  // 背景色をユーザーIDに応じて設定
                            'borderColor' => $color,
                            'textColor' => '#000'
                        );
                    }

                    echo json_encode($events);
                ?>,
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                eventClick: function(info) {
                    // クリックされたイベントのデータを取得してモーダルにセット
                    $('#edit_event_id').val(info.event.id);
                    $('#edit_name').val(info.event.title.split("：")[0]); // 名前
                    $('#edit_event_name').val(info.event.title.split("：")[1]); // 予定名
                    $('#edit_start_time').val(new Date(info.event.start).toISOString().slice(0, 16));
                    $('#edit_end_time').val(new Date(info.event.end).toISOString().slice(0, 16));

                    // 編集モーダルを表示
                    $('#editModal').fadeIn();
                },
                eventContent: function(info) {
                    var startTime = new Date(info.event.start).toLocaleTimeString('ja-JP', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                    var endTime = new Date(info.event.end).toLocaleTimeString('ja-JP', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });

                    var timeText = startTime + " ~ " + endTime;
                    var titleText = info.event.title;

                    var customHtml = document.createElement('div');
                    customHtml.innerHTML = '<b>' + titleText + '</b><br><span>' + timeText + '</span>';

                    return { domNodes: [customHtml] };
                }
            });

            calendar.render();

            // 新規スケジュール作成モーダルの表示
            $('#createScheduleButton').click(function() {
                $('#createModal').fadeIn();
            });

            // 新規スケジュール作成モーダルの非表示
            $('#closeCreateModal').click(function() {
                $('#createModal').fadeOut();
            });

            // 編集スケジュールモーダルの非表示
            $('#closeEditModal').click(function() {
                $('#editModal').fadeOut();
            });

            // フォーム送信処理（新規スケジュール作成）
            $('#createScheduleForm').submit(function(event) {
                event.preventDefault();

                var formData = $(this).serialize();

                // AjaxでPHPにデータを送信
                $.ajax({
                    type: 'POST',
                    url: 'write.php',
                    data: formData,
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.status === 'success') {
                            alert('スケジュールが作成されました！');
                            location.reload(); // ページを再読み込みしてカレンダーを更新
                        } else {
                            alert('スケジュールの作成に失敗しました: ' + result.message);
                        }
                    },
                    error: function() {
                        alert('スケジュールの作成に失敗しました');
                    }
                });
            });

            // フォーム送信処理（スケジュール編集）
            $('#editScheduleForm').submit(function(event) {
                event.preventDefault();

                var formData = $(this).serialize();

                // AjaxでPHPにデータを送信
                $.ajax({
                    type: 'POST',
                    url: 'update.php', // 編集用のPHPファイルに送信
                    data: formData,
                    success: function(response) {
                        alert('スケジュールが更新されました！');
                        location.reload(); // ページを再読み込みしてカレンダーを更新
                    },
                    error: function() {
                        alert('スケジュールの更新に失敗しました');
                    }
                });
            });

            // スケジュール削除処理
            $('#deleteScheduleButton').click(function() {
                var eventId = $('#edit_event_id').val();

                if (confirm("本当に削除しますか？")) {
                    // Ajaxで削除リクエストを送信
                    $.ajax({
                        type: 'POST',
                        url: 'delete.php', // 削除用のPHPファイルに送信
                        data: { event_id: eventId },
                        success: function(response) {
                            alert('スケジュールが削除されました！');
                            location.reload(); // ページを再読み込みしてカレンダーを更新
                        },
                        error: function() {
                            alert('スケジュールの削除に失敗しました');
                        }
                    });
                }
            });
        });
    </script>

</body>
</html>
