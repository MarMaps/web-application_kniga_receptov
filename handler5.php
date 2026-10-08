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

$con = pg_connect('host=localhost port=5432 dbname=kniga_receptov user=postgres password=123');
if (!$con) {
    $response['message'] = 'Ошибка соединения с БД';
    echo json_encode( $response);
    exit;
}

$sql = "SELECT id,nazvanie FROM recept";
$result = pg_query( $con, $sql);

if (!$result) {
    $response['message'] = 'Ошибка выполнения запроса: ' . pg_last_error( $con);
    echo json_encode( $response);
    exit;
}

$recept = [];
while ( $row = pg_fetch_assoc( $result)) {
    $recept[] = [
        'id' => $row['id'],
        'nazvanie' => $row['nazvanie']
    ];
}

pg_close( $con);

$response['success'] = true;
$response['message'] = 'Данные успешно загружены';
$response['data'] = $recept;

echo json_encode( $response, JSON_UNESCAPED_UNICODE);
?>
