<?php
    require_once __DIR__ . '/../classes/EmailService.php';

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

    if (empty($data['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email обязателен']);
        exit;
    }

    // Проверяем, есть ли в сессии данные для этого email
    session_start();
    $pendingRegistration = $_SESSION['pending_registration'] ?? null;

    if (!$pendingRegistration || $pendingRegistration['email'] !== $data['email']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Данные для этого email не найдены. Пожалуйста, начните регистрацию заново.']);
        exit;
    }

    // Генерируем новый код верификации
    $code = rand(100000, 999999); // 6-значный код

    // Обновляем код в сессии
    $_SESSION['verification_code'] = [
        'email' => $data['email'],
        'code' => $code,
        'expires_at' => time() + 3600 // код действителен 1 час
    ];

    // Отправляем код на email
    $emailService = new EmailService();
    $sent = $emailService->sendVerificationCode($data['email'], $pendingRegistration['login'], (string)$code);

    if (!$sent) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Не удалось отправить код подтверждения. Пожалуйста, попробуйте позже.'
        ]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Новый код подтверждения отправлен на ваш email.'
    ]);
?>
