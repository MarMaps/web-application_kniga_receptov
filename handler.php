<?php
header('Content-Type: application/json; charset=utf-8');

// Читаем тело запроса (даже если не используем)
$json = file_get_contents('php://input');
$data = json_decode( $json, true);

// объявление массива ответа
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

$con = pg_connect('host=localhost port=5432 dbname=kniga_receptov user=postgres password=123456');
if (!$con) {
    $response['message'] = 'Ошибка соединения с БД';
    echo json_encode( $response);
    exit;
}

// действие: insert (добавить рецепт) или list (вывести рецепты)
$action = isset($data['action']) ? $data['action'] : 'list';

if ($action == 'insert') {
    $nazvanie = isset($data['nazvanie']) ? trim($data['nazvanie']) : '';
    $shagi = isset($data['shagi_prigotovleniya']) ? trim($data['shagi_prigotovleniya']) : '';

    if ($nazvanie === '' || $shagi === '') {
        $response['message'] = 'Заполните название и шаги приготовления';
        echo json_encode( $response);
        exit;
    }

    // экранируем значения, чтобы кавычки и переводы строк не ломали SQL
    $nazvanieSql = pg_escape_literal( $con, $nazvanie);
    $shagiSql = pg_escape_literal( $con, $shagi);

    $sql = "INSERT INTO recept (nazvanie, shagi_prigotovleniya, likes, dislikes)
            VALUES ($nazvanieSql, $shagiSql, 0, 0)
            RETURNING id";
    $result = pg_query( $con, $sql);

    if (!$result) {
        $response['message'] = 'Ошибка выполнения запроса: ' . pg_last_error( $con);
        echo json_encode( $response);
        exit;
    }

    $novyi = pg_fetch_assoc( $result);
    $id = $novyi['id'];
    pg_close( $con);

    $response['success'] = true;
    $response['message'] = 'Рецепт добавлен';
    $response['data'] = [
        'id' => $id,
        'nazvanie' => $nazvanie,
        'shagi_prigotovleniya' => $shagi,
        'likes' => 0,
        'dislikes' => 0
    ];
    echo json_encode( $response, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action == 'vote') {
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $type = isset($data['type']) ? $data['type'] : '';

    if ($id <= 0 || ($type != 'like' && $type != 'dislike')) {
        $response['message'] = 'Неверный id или тип голоса';
        echo json_encode( $response);
        exit;
    }

    // COALESCE, потому что likes/dislikes бывают NULL
    if ($type == 'like') {
        $sql = "UPDATE recept SET likes = COALESCE(likes, 0) + 1 WHERE id = $id
                RETURNING id, COALESCE(likes, 0) AS likes, COALESCE(dislikes, 0) AS dislikes";
    } else {
        $sql = "UPDATE recept SET dislikes = COALESCE(dislikes, 0) + 1 WHERE id = $id
                RETURNING id, COALESCE(likes, 0) AS likes, COALESCE(dislikes, 0) AS dislikes";
    }

    $result = pg_query( $con, $sql);
    $row = $result ? pg_fetch_assoc( $result) : null;

    if (!$row) {
        $response['message'] = 'Рецепт с id = ' . $id . ' не найден';
        echo json_encode( $response);
        exit;
    }

    pg_close( $con);

    $response['success'] = true;
    $response['message'] = ($type == 'like' ? 'Лайк' : 'Дизлайк') . ' поставлен';
    $response['data'] = [
        'id' => $row['id'],
        'likes' => $row['likes'],
        'dislikes' => $row['dislikes']
    ];
    echo json_encode( $response, JSON_UNESCAPED_UNICODE);
    exit;
}

// сортировка: по id, по лайкам или по дизлайкам (всегда по убыванию)
// COALESCE, потому что likes/dislikes бывают NULL
$sort = isset($data['sort']) ? $data['sort'] : 'id';

if ($sort == 'like') {
    $orderBy = 'COALESCE(likes, 0) DESC, id';
} elseif ($sort == 'dislike') {
    $orderBy = 'COALESCE(dislikes, 0) DESC, id';
} else {
    $orderBy = 'id';
}

$sql = "SELECT id, nazvanie, shagi_prigotovleniya, likes, dislikes FROM recept
        ORDER BY $orderBy";
$result = pg_query( $con, $sql);

if (!$result) {
    $response['message'] = 'Ошибка выполнения запроса: ' . pg_last_error( $con);
    echo json_encode( $response);
    exit;
}

$recepty = [];
while ( $row = pg_fetch_assoc( $result)) {
    $recepty[] = [
        'id' => $row['id'],
        'nazvanie' => $row['nazvanie'],
        'shagi_prigotovleniya' => $row['shagi_prigotovleniya'],
        'likes' => $row['likes'],
        'dislikes' => $row['dislikes']
    ];
}

pg_close( $con);

$response['success'] = true;
$response['message'] = 'Данные успешно загружены';
$response['data'] = $recepty;

echo json_encode( $response, JSON_UNESCAPED_UNICODE);
?>