<?php
    require_once __DIR__ . '/../controllers/UsersController.php';

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
        exit;
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Неверный формат данных']);
        exit;
    }

    if (empty($data['email']) || empty($data['code'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email и код подтверждения обязательны']);
        exit;
    }

    $usersController = new UsersController();
    $result = $usersController->verifyCodeAndRegister($data);

    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
?>
