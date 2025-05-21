<?php
    require_once __DIR__ . '/../classes/UserContext.php';
    require_once __DIR__ . '/../classes/VerificationCodeContext.php';
    require_once __DIR__ . '/../classes/EmailService.php';
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

            if (strlen($data['password']) < 6 || !preg_match('/[A-Za-z]/', $data['password']) || !preg_match('/\\d/', $data['password'])) {
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
                'id' => 0,
                'login' => $data['login'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'full_name' => $data['full_name'] ?? null,
                'bio' => $data['bio'] ?? null,
                'language' => $data['language'] ?? 'RU',
                'is_email_verified' => false
            ];

            try {
                $user = new UserContext($userData);
                $user->add();

                $result = $this->sendVerificationCode($user->Id, $user->Email, $user->Login);

                if (!$result['success']) {
                    return [
                        'success' => true,
                        'message' => 'Пользователь успешно зарегистрирован, но не удалось отправить код подтверждения. Попробуйте запросить код повторно.',
                        'user_id' => $user->Id
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'Пользователь успешно зарегистрирован. На вашу почту отправлен код подтверждения.',
                    'user_id' => $user->Id
                ];
            } catch (Throwable $e) {
                return [ 'success' => false, 'message' => 'Ошибка при регистрации: ' . $e->getMessage() ];
            }
        }

        public function sendVerificationCode($userId, $email, $login)
        {
            try {
                error_log("Начало sendVerificationCode для пользователя: $userId, email: $email");

                VerificationCodeContext::deleteOldCodesForEmail($email);
                error_log("Старые коды для email: $email успешно удалены");

                $verificationCode = VerificationCodeContext::createForUser($userId, $email);
                error_log("Создан новый код подтверждения: {$verificationCode->Code} для пользователя: $userId");

                $emailService = new EmailService();
                error_log("EmailService инициализирован");

                $sent = $emailService->sendVerificationCode($email, $login, $verificationCode->Code);
                error_log("Результат попытки отправки письма: " . ($sent ? 'успешно' : 'неудачно'));

                if (!$sent) {
                    error_log("Не удалось отправить код подтверждения на email: $email");
                    return ['success' => false, 'message' => 'Не удалось отправить код подтверждения. Пожалуйста, попробуйте позже.'];
                }

                error_log("Код подтверждения успешно отправлен на email: $email");
                return ['success' => true, 'message' => 'Код подтверждения успешно отправлен на ваш email.'];
            } catch (Throwable $e) {
                error_log("Ошибка в sendVerificationCode: " . $e->getMessage());
                error_log("Стек вызовов: " . $e->getTraceAsString());
                return ['success' => false, 'message' => 'Ошибка при отправке кода: ' . $e->getMessage()];
            }
        }

        public function verifyEmail($data)
        {
            if (empty($data['code']) || empty($data['email'])) {
                return ['success' => false, 'message' => 'Код подтверждения и email обязательны.'];
            }

            try {
                $code = VerificationCodeContext::findActiveByCodeAndEmail($data['code'], $data['email']);

                if (!$code) {
                    return ['success' => false, 'message' => 'Неверный код подтверждения или срок его действия истек.'];
                }

                if ($code->isExpired()) {
                    return ['success' => false, 'message' => 'Срок действия кода подтверждения истек. Запросите новый код.'];
                }

                $user = UserContext::findById($code->UserId);

                if (!$user) {
                    return ['success' => false, 'message' => 'Пользователь не найден.'];
                }

                $user->verifyEmail();
                $code->markAsUsed();

                return ['success' => true, 'message' => 'Email успешно подтвержден. Теперь вы можете войти в систему.'];
            } catch (Throwable $e) {
                return ['success' => false, 'message' => 'Ошибка при проверке кода: ' . $e->getMessage()];
            }
        }

        public function resendVerificationCode($data)
        {
            if (empty($data['email'])) {
                return ['success' => false, 'message' => 'Email обязателен.'];
            }

            try {
                $user = UserContext::findByEmail($data['email']);

                if (!$user) {
                    return ['success' => false, 'message' => 'Пользователь с указанным email не найден.'];
                }

                if ($user->IsEmailVerified) {
                    return ['success' => false, 'message' => 'Email уже подтвержден.'];
                }

                return $this->sendVerificationCode($user->Id, $user->Email, $user->Login);
            } catch (Throwable $e) {
                return ['success' => false, 'message' => 'Ошибка при повторной отправке кода: ' . $e->getMessage()];
            }
        }
    }
?>
