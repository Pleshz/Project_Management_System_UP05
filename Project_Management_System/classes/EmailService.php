<?php
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

    class EmailService
    {
        private $mailer;
        private $from = 'pleschkoffdan@yandex.ru';
        private $fromName = 'Project Management System';

        public function __construct()
        {
            $host = 'smtp.yandex.ru';
            $port = 465;
            $username = 'pleschkoffdan@yandex.ru';
            $password = 'osrxxymreydqiokw';

            $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
            $this->mailer->isSMTP();
            $this->mailer->Host = $host;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $username;
            $this->mailer->Password = $password;
            $this->mailer->SMTPSecure = 'ssl';
            $this->mailer->Port = $port;
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->isHTML(true);
            $this->mailer->setFrom($this->from, $this->fromName);
        }

        public function sendVerificationCode(string $email, string $name, string $code): bool
        {
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($email, $name);

                $this->mailer->Subject = 'Подтверждение регистрации в Project Management System';

                $body = $this->getVerificationEmailTemplate($name, $code);
                $this->mailer->Body = $body;

                return $this->mailer->send();
            } catch (Exception $e) {
                error_log('Ошибка отправки письма: ' . $e->getMessage());
                return false;
            }
        }

        private function getVerificationEmailTemplate(string $name, string $code): string
        {
            $codeDisplay = implode(' ', str_split($code));

            return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Подтверждение регистрации</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        max-width: 600px;
                        margin: 0 auto;
                    }
                    .container {
                        padding: 20px;
                        background-color: #f9f9f9;
                        border-radius: 5px;
                    }
                    .header {
                        text-align: center;
                        padding-bottom: 10px;
                        border-bottom: 1px solid #eee;
                        margin-bottom: 20px;
                    }
                    .verification-code {
                        text-align: center;
                        font-size: 24px;
                        letter-spacing: 5px;
                        font-weight: bold;
                        color: #333;
                        margin: 30px 0;
                        padding: 15px;
                        background-color: #f2f2f2;
                        border-radius: 5px;
                    }
                    .footer {
                        margin-top: 30px;
                        text-align: center;
                        font-size: 12px;
                        color: #777;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>Подтверждение регистрации</h2>
                    </div>

                    <p>Здравствуйте, {$name}!</p>

                    <p>Вы получили это письмо, потому что данный email был использован при регистрации на платформе Project Management System.</p>

                    <p>Для подтверждения вашего email, пожалуйста, введите следующий код в форме подтверждения:</p>

                    <div class="verification-code">{$codeDisplay}</div>

                    <p>Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.</p>

                    <p>С уважением,<br>Команда Project Management System</p>

                    <div class="footer">
                        <p>Это автоматическое письмо, пожалуйста, не отвечайте на него.</p>
                    </div>
                </div>
            </body>
            </html>
            HTML;
        }
    }
?>
