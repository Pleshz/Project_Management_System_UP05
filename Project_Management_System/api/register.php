<?php
    require_once __DIR__ . '/../controllers/UsersController.php';
    require_once __DIR__ . '/../classes/EmailService.php';
    require_once __DIR__ . '/../classes/VerificationCodeContext.php';

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

    // Получаем данные из запроса
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Неверный формат данных']);
        exit;
    }

    if (empty($data['email']) || empty($data['login']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email, логин и пароль обязательны']);
        exit;
    }

    $usersController = new UsersController();

    // 1. Проверяем валидность данных (без создания пользователя)
    $validationResult = $usersController->validateRegistrationData([
        'email' => $data['email'],
        'login' => $data['login'],
        'password' => $data['password'],
        'language' => $data['language'] ?? 'RU'
    ]);

    if (!$validationResult['success']) {
        http_response_code(400);
        echo json_encode($validationResult);
        exit;
    }

    // 2. Сохраняем данные пользователя во временное хранилище (сессию)
    session_start();
    $_SESSION['pending_registration'] = [
        'email' => $data['email'],
        'login' => $data['login'],
        'password' => $data['password'], // в реальном приложении стоит хешировать даже временные данные
        'full_name' => $data['full_name'] ?? null,
        'bio' => null,
        'language' => $data['language'] ?? 'RU',
        'created_at' => time()
    ];

    // 3. Генерируем код верификации и отправляем его на email
    $code = rand(100000, 999999); // 6-значный код

    // Сохраняем код во временное хранилище, привязанное к email
    $_SESSION['verification_code'] = [
        'email' => $data['email'],
        'code' => $code,
        'expires_at' => time() + 3600 // код действителен 1 час
    ];

    // Отправляем код на email
    $emailService = new EmailService();
    $sent = $emailService->sendVerificationCode($data['email'], $data['login'], (string)$code);

    if (!$sent) {
        unset($_SESSION['pending_registration']);
        unset($_SESSION['verification_code']);

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Не удалось отправить код подтверждения. Пожалуйста, попробуйте позже.'
        ]);
        exit;
    }

    // 4. Возвращаем успешный результат, но пользователь еще не создан
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Код подтверждения отправлен на ваш email. Пожалуйста, введите его для завершения регистрации.',
        'email' => $data['email']
    ]);
?>
