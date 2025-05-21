<?php
require_once __DIR__ . '/controllers/UsersController.php';
require_once __DIR__ . '/classes/UserContext.php';
require_once __DIR__ . '/classes/VerificationCodeContext.php';
require_once __DIR__ . '/classes/EmailService.php';

// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Тест полного цикла регистрации и верификации</h1>";

// Предотвращаем кэширование
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        // Регистрация нового пользователя
        $email = $_POST['email'] ?? '';
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        $usersController = new UsersController();

        $userData = [
            'email' => $email,
            'login' => $login,
            'password' => $password,
            'full_name' => $_POST['full_name'] ?? null,
            'bio' => null,
            'language' => 'RU'
        ];

        echo "<h2>Попытка регистрации</h2>";
        echo "<pre>";
        echo "Email: " . htmlspecialchars($email) . "\n";
        echo "Login: " . htmlspecialchars($login) . "\n";
        echo "</pre>";

        $result = $usersController->register($userData);

        if ($result['success']) {
            echo "<p style='color:green;'>✓ Пользователь успешно зарегистрирован</p>";
            echo "<p>ID пользователя: " . $result['user_id'] . "</p>";
            echo "<p>" . $result['message'] . "</p>";

            // Сохраняем в сессии данные для формы проверки кода
            $_SESSION['email'] = $email;

            // Показываем форму для ввода кода подтверждения
            echo "<h2>Проверка кода</h2>";
            echo "<form method='post'>";
            echo "<div><label>Email: <input type='email' name='verify_email' value='" . htmlspecialchars($email) . "' required></label></div>";
            echo "<div><label>Код подтверждения: <input type='text' name='code' required pattern='[0-9]{6}' maxlength='6'></label></div>";
            echo "<div><input type='submit' name='verify' value='Проверить код'></div>";
            echo "</form>";

            // Показываем форму для повторной отправки кода
            echo "<h3>Или запросить код повторно</h3>";
            echo "<form method='post'>";
            echo "<div><label>Email: <input type='email' name='resend_email' value='" . htmlspecialchars($email) . "' required></label></div>";
            echo "<div><input type='submit' name='resend' value='Отправить код повторно'></div>";
            echo "</form>";
        } else {
            echo "<p style='color:red;'>✗ Ошибка при регистрации</p>";
            echo "<p>" . $result['message'] . "</p>";

            // Показываем форму регистрации снова
            showRegistrationForm($email, $login, $_POST['full_name'] ?? '');
        }
    } elseif (isset($_POST['verify'])) {
        // Проверяем код подтверждения
        $email = $_POST['verify_email'] ?? '';
        $code = $_POST['code'] ?? '';

        echo "<h2>Проверка кода подтверждения</h2>";
        echo "<pre>";
        echo "Email: " . htmlspecialchars($email) . "\n";
        echo "Код: " . htmlspecialchars($code) . "\n";
        echo "</pre>";

        $usersController = new UsersController();
        $result = $usersController->verifyEmail([
            'email' => $email,
            'code' => $code
        ]);

        if ($result['success']) {
            echo "<p style='color:green;'>✓ Email успешно подтвержден!</p>";
            echo "<p>" . $result['message'] . "</p>";
        } else {
            echo "<p style='color:red;'>✗ Ошибка при проверке кода</p>";
            echo "<p>" . $result['message'] . "</p>";

            // Показываем форму для повторной проверки кода
            echo "<h3>Попробовать еще раз</h3>";
            echo "<form method='post'>";
            echo "<div><label>Email: <input type='email' name='verify_email' value='" . htmlspecialchars($email) . "' required></label></div>";
            echo "<div><label>Код подтверждения: <input type='text' name='code' required pattern='[0-9]{6}' maxlength='6'></label></div>";
            echo "<div><input type='submit' name='verify' value='Проверить код'></div>";
            echo "</form>";

            // Показываем форму для повторной отправки кода
            echo "<h3>Или запросить код повторно</h3>";
            echo "<form method='post'>";
            echo "<div><label>Email: <input type='email' name='resend_email' value='" . htmlspecialchars($email) . "' required></label></div>";
            echo "<div><input type='submit' name='resend' value='Отправить код повторно'></div>";
            echo "</form>";
        }
    } elseif (isset($_POST['resend'])) {
        // Повторно отправляем код подтверждения
        $email = $_POST['resend_email'] ?? '';

        echo "<h2>Повторная отправка кода подтверждения</h2>";
        echo "<pre>";
        echo "Email: " . htmlspecialchars($email) . "\n";
        echo "</pre>";

        $usersController = new UsersController();
        $result = $usersController->resendVerificationCode([
            'email' => $email
        ]);

        if ($result['success']) {
            echo "<p style='color:green;'>✓ Код подтверждения отправлен повторно</p>";
            echo "<p>" . $result['message'] . "</p>";

            // Показываем форму для ввода кода подтверждения
            echo "<h3>Введите полученный код</h3>";
            echo "<form method='post'>";
            echo "<div><label>Email: <input type='email' name='verify_email' value='" . htmlspecialchars($email) . "' required></label></div>";
            echo "<div><label>Код подтверждения: <input type='text' name='code' required pattern='[0-9]{6}' maxlength='6'></label></div>";
            echo "<div><input type='submit' name='verify' value='Проверить код'></div>";
            echo "</form>";
        } else {
            echo "<p style='color:red;'>✗ Ошибка при отправке кода подтверждения</p>";
            echo "<p>" . $result['message'] . "</p>";

            // Показываем форму для повторной отправки
            echo "<h3>Попробовать еще раз</h3>";
            echo "<form method='post'>";
            echo "<div><label>Email: <input type='email' name='resend_email' value='" . htmlspecialchars($email) . "' required></label></div>";
            echo "<div><input type='submit' name='resend' value='Отправить код повторно'></div>";
            echo "</form>";
        }
    }
} else {
    // Показываем форму регистрации
    showRegistrationForm();
}

function showRegistrationForm($email = '', $login = '', $fullName = '') {
    echo "<h2>Регистрация нового пользователя</h2>";
    echo "<form method='post'>";
    echo "<div><label>Email: <input type='email' name='email' value='" . htmlspecialchars($email) . "' required></label></div>";
    echo "<div><label>Логин: <input type='text' name='login' value='" . htmlspecialchars($login) . "' required pattern='[a-zA-Z0-9]{3,20}'></label></div>";
    echo "<div><label>Пароль: <input type='password' name='password' required minlength='6'></label></div>";
    echo "<div><label>Полное имя (опционально): <input type='text' name='full_name' value='" . htmlspecialchars($fullName) . "'></label></div>";
    echo "<div><input type='submit' name='register' value='Зарегистрироваться'></div>";
    echo "</form>";

    echo "<p><small>* Логин должен состоять из латинских букв и цифр, от 3 до 20 символов</small></p>";
    echo "<p><small>* Пароль должен быть не менее 6 символов, содержать буквы и цифры</small></p>";
}

// Добавим дополнительную информацию
echo "<hr>";
echo "<h2>Полезные ссылки</h2>";
echo "<ul>";
echo "<li><a href='check_database.php' target='_blank'>Проверить структуру базы данных</a></li>";
echo "<li><a href='test_email.php' target='_blank'>Протестировать отправку писем</a></li>";
echo "</ul>";
?>
