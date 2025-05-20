<?php
    require_once __DIR__ . '/../classes/UserContext.php';
    require_once __DIR__ . '/../classes/common/connection.php';
    class UsersController
    {
        public function register($data)
        {
        
            if (empty($data['email']) || empty($data['password']) || empty($data['login'])) {
                return [ 'success' => false, 'message' => 'Email, логин и пароль обязательны.' ];
            }


            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [ 'success' => false, 'message' => 'Некорректный email.' ];
            }

            if (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $data['login'])) {
                return [ 'success' => false, 'message' => 'Логин должен состоять из латинских букв и цифр и быть от 3 до 20 символов.' ];
            }

            if (strlen($data['password']) < 6 || !preg_match('/[A-Za-z]/', $data['password']) || !preg_match('/\d/', $data['password'])) {
                return [ 'success' => false, 'message' => 'Пароль должен быть не менее 6 символов, содержать буквы и цифры.' ];
            }

            if (!empty($data['full_name']) && mb_strlen($data['full_name']) > 50) {
                return [ 'success' => false, 'message' => 'Имя слишком длинное.' ];
            }

            if (!empty($data['bio']) && mb_strlen($data['bio']) > 250) {
                return [ 'success' => false, 'message' => 'Биография слишком длинная.' ];
            }

            // ПРОВЕРКА ПОЧТЫ
            $connection = Connection::openConnection();
            $sqlEmail = 'SELECT COUNT(*) as c FROM users WHERE email = ?';
            $stmtEmail = $connection->prepare($sqlEmail);
            $stmtEmail->bind_param('s', $data['email']);
            $stmtEmail->execute();
            $resultEmail = $stmtEmail->get_result()->fetch_assoc();
            $stmtEmail->close();
            if ($resultEmail['c'] > 0) {
                Connection::closeConnection($connection);
                return [ 'success' => false, 'message' => 'Пользователь с таким email уже существует.' ];
            }

            // ПРОВЕРКА ЛОГИНА
            $sqlLogin = 'SELECT COUNT(*) as c FROM users WHERE login = ?';
            $stmtLogin = $connection->prepare($sqlLogin);
            $stmtLogin->bind_param('s', $data['login']);
            $stmtLogin->execute();
            $resultLogin = $stmtLogin->get_result()->fetch_assoc();
            $stmtLogin->close();
            Connection::closeConnection($connection);
            if ($resultLogin['c'] > 0) {
                return [ 'success' => false, 'message' => 'Пользователь с таким логином уже существует.' ];
            }

            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $userData = [
                'id' => null,
                'login' => $data['login'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'full_name' => $data['full_name'] ?? null,
                'bio' => $data['bio'] ?? null,
                'language' => $data['language'] ?? 'RU'
            ];
            try {
                $user = new UserContext($userData);
                $user->add();
            } catch (Throwable $e) {
                return [ 'success' => false, 'message' => 'Ошибка при регистрации: ' . $e->getMessage() ];
            }
            return [ 'success' => true, 'message' => 'Пользователь успешно зарегистрирован.' ];
        }
    }
?>