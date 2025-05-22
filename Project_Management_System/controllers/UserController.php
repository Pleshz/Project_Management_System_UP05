<?php
    // Включаем отображение всех ошибок для отладки
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Разрешаем CORS для разработки
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

    // Если это preflight запрос, завершаем выполнение
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    require_once __DIR__.'/../classes/UserContext.php';

    class UserController
    {
        public function register(): void
        {
            header('Content-Type: application/json');

            try {
                $input = file_get_contents('php://input');
                if (empty($input)) {
                    throw new Exception('Не получены данные. Пустой ввод.');
                }

                $postData = json_decode($input, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Ошибка JSON: ' . json_last_error_msg() . '. Получены данные: ' . $input);
                }

                if (!isset($postData['login']) || empty($postData['login'])) {
                    throw new Exception('Логин обязателен для заполнения');
                }

                if (!isset($postData['email']) || empty($postData['email'])) {
                    throw new Exception('Email обязателен для заполнения');
                }

                if (!isset($postData['password']) || empty($postData['password'])) {
                    throw new Exception('Пароль обязателен для заполнения');
                }

                if (!isset($postData['language']) || empty($postData['language'])) {
                    throw new Exception('Язык обязателен для заполнения');
                }

                if (!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Некорректный формат email');
                }

                if (strlen($postData['password']) < 6) {
                    throw new Exception('Пароль должен содержать не менее 6 символов');
                }

                $connection = Connection::openConnection();
                $stmt = $connection->prepare("SELECT COUNT(*) as count FROM Users WHERE login = ? OR email = ?");
                $login = $postData['login'];
                $email = $postData['email'];
                $stmt->bind_param("ss", $login, $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if ($row['count'] > 0) {
                    throw new Exception('Пользователь с таким логином или email уже существует');
                }

                $hashedPassword = password_hash($postData['password'], PASSWORD_DEFAULT);

                $userData = [
                    'id' => 0,
                    'login' => $postData['login'],
                    'email' => $postData['email'],
                    'password' => $hashedPassword,
                    'language' => $postData['language'],
                    'full_name' => $postData['full_name'] ?? null,
                    'bio' => $postData['bio'] ?? null,
                    'is_email_verified' => false
                ];

                $user = new UserContext($userData);
                $user->add();

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Пользователь успешно зарегистрирован',
                    'user_id' => $user->Id
                ]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Не удалось зарегистрировать пользователя: ' . $e->getMessage()
                ]);
            }
        }

        public function login(): void
        {
            header('Content-Type: application/json');

            try {
                $input = file_get_contents('php://input');
                if (empty($input)) {
                    throw new Exception('Не получены данные. Пустой ввод.');
                }

                $postData = json_decode($input, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Ошибка JSON: ' . json_last_error_msg() . '. Получены данные: ' . $input);
                }

                if (!isset($postData['login']) || empty($postData['login'])) {
                    throw new Exception('Логин обязателен для заполнения');
                }

                if (!isset($postData['password']) || empty($postData['password'])) {
                    throw new Exception('Пароль обязателен для заполнения');
                }

                $connection = Connection::openConnection();
                $stmt = $connection->prepare("SELECT * FROM Users WHERE login = ?");
                $login = $postData['login'];
                $stmt->bind_param("s", $login);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    throw new Exception('Пользователь с таким логином не найден');
                }

                $userData = $result->fetch_assoc();

                if (!password_verify($postData['password'], $userData['password'])) {
                    throw new Exception('Неверный пароль');
                }

                session_start();
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['user_login'] = $userData['login'];

                unset($userData['password']);

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Авторизация успешна',
                    'user' => $userData
                ]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Не удалось авторизоваться: ' . $e->getMessage()
                ]);
            }
        }

        public function logout(): void
        {
            header('Content-Type: application/json');

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            session_destroy();

            echo json_encode([
                'status' => 'success',
                'message' => 'Выход выполнен успешно'
            ]);
        }

        public function getCurrentUser(): void
        {
            header('Content-Type: application/json');

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            try {
                if (!isset($_SESSION['user_id'])) {
                    throw new Exception('Пользователь не авторизован');
                }

                $connection = Connection::openConnection();
                $stmt = $connection->prepare("SELECT * FROM Users WHERE id = ?");
                $userId = $_SESSION['user_id'];
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    throw new Exception('Пользователь не найден');
                }

                $userData = $result->fetch_assoc();

                unset($userData['password']);

                echo json_encode([
                    'status' => 'success',
                    'user' => $userData
                ]);
            } catch (Exception $e) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        }

        public function updateUser(): void
        {
            header('Content-Type: application/json');

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            try {
                if (!isset($_SESSION['user_id'])) {
                    throw new Exception('Пользователь не авторизован');
                }

                $input = file_get_contents('php://input');
                if (empty($input)) {
                    throw new Exception('Не получены данные. Пустой ввод.');
                }

                $postData = json_decode($input, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Ошибка JSON: ' . json_last_error_msg() . '. Получены данные: ' . $input);
                }

                $connection = Connection::openConnection();
                $stmt = $connection->prepare("SELECT * FROM Users WHERE id = ?");
                $userId = $_SESSION['user_id'];
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    throw new Exception('Пользователь не найден');
                }

                $currentUserData = $result->fetch_assoc();

                $updatedUserData = [
                    'id' => $userId,
                    'login' => isset($postData['login']) && !empty($postData['login']) ? $postData['login'] : $currentUserData['login'],
                    'email' => $postData['email'] ?? $currentUserData['email'],
                    'password' => $currentUserData['password'],
                    'language' => $postData['language'] ?? $currentUserData['language'],
                    'full_name' => $postData['full_name'] ?? $currentUserData['full_name'],
                    'bio' => $postData['bio'] ?? $currentUserData['bio'],
                    'is_email_verified' => $currentUserData['is_email_verified']
                ];

                // Проверяем, если логин изменился
                if (isset($postData['login']) && $postData['login'] !== $currentUserData['login']) {
                    // Проверяем, что такой логин не занят другим пользователем
                    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM Users WHERE login = ? AND id != ?");
                    $login = $postData['login'];
                    $stmt->bind_param("si", $login, $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    if ($row['count'] > 0) {
                        throw new Exception('Логин уже используется другим пользователем');
                    }
                }

                if (isset($postData['new_password']) && !empty($postData['new_password'])) {
                    if (!isset($postData['current_password']) || empty($postData['current_password'])) {
                        throw new Exception('Текущий пароль обязателен для смены пароля');
                    }

                    if (!password_verify($postData['current_password'], $currentUserData['password'])) {
                        throw new Exception('Текущий пароль неверен');
                    }

                    if (strlen($postData['new_password']) < 6) {
                        throw new Exception('Новый пароль должен содержать не менее 6 символов');
                    }

                    $updatedUserData['password'] = password_hash($postData['new_password'], PASSWORD_DEFAULT);
                }

                if (isset($postData['email']) && $postData['email'] !== $currentUserData['email']) {
                    if (!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Некорректный формат email');
                    }

                    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM Users WHERE email = ? AND id != ?");
                    $email = $postData['email'];
                    $stmt->bind_param("si", $email, $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    if ($row['count'] > 0) {
                        throw new Exception('Email уже используется другим пользователем');
                    }

                    $updatedUserData['is_email_verified'] = false;
                }

                $user = new UserContext($updatedUserData);
                $user->update();

                unset($updatedUserData['password']);

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Данные успешно обновлены',
                    'user' => $updatedUserData
                ]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Не удалось обновить данные: ' . $e->getMessage()
                ]);
            }
        }

        public function deleteUser(): void
        {
            header('Content-Type: application/json');
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            try {
                if (!isset($_SESSION['user_id'])) {
                    throw new Exception('Пользователь не авторизован');
                }

                $input = file_get_contents('php://input');
                $postData = json_decode($input, true);

                if (isset($postData['password']) && !empty($postData['password'])) {
                    $connection = Connection::openConnection();
                    $stmt = $connection->prepare("SELECT password FROM Users WHERE id = ?");
                    $userId = $_SESSION['user_id'];
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 0) {
                        throw new Exception('Пользователь не найден');
                    }

                    $row = $result->fetch_assoc();

                    if (!password_verify($postData['password'], $row['password'])) {
                        throw new Exception('Неверный пароль');
                    }

                    $userData = [
                        'id' => $_SESSION['user_id'],
                        'login' => '',
                        'email' => '',
                        'password' => '',
                        'language' => 'ru',
                        'full_name' => null,
                        'bio' => null,
                        'is_email_verified' => false
                    ];

                    $user = new UserContext($userData);
                    $user->delete();

                    session_destroy();

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Аккаунт успешно удален'
                    ]);
                } else {
                    throw new Exception('Пароль обязателен для подтверждения удаления аккаунта');
                }
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Не удалось удалить аккаунт: ' . $e->getMessage()
                ]);
            }
        }
    }

    if (!isset($_GET['action'])) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Не указано действие (action)'
        ]);
        exit;
    }

    $controller = new UserController();

    switch ($_GET['action']) {
        case 'register':
            $controller->register();
            break;
        case 'login':
            $controller->login();
            break;
        case 'logout':
            $controller->logout();
            break;
        case 'getCurrentUser':
            $controller->getCurrentUser();
            break;
        case 'updateUser':
            $controller->updateUser();
            break;
        case 'deleteUser':
            $controller->deleteUser();
            break;
        default:
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Неизвестное действие: ' . $_GET['action']
            ]);
    }
?>
