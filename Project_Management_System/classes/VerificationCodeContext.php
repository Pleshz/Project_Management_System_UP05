<?php
    require_once __DIR__.'/../models/verificationCode.php';
    require_once __DIR__.'/common/connection.php';

    class VerificationCodeContext extends VerificationCode
    {
        public function __construct(array $data)
        {
            parent::__construct(
                id: $data['id'] ?? 0,
                userId: $data['user_id'] ?? 0,
                code: $data['code'],
                email: $data['email'],
                createdAt: $data['created_at'] ?? time(),
                expiresAt: $data['expires_at'] ?? (time() + 3600),
                isUsed: $data['is_used'] ?? false
            );
        }

        public static function createForUser(int $userId, string $email, int $expiresIn = 3600): self
        {
            $code = VerificationCode::generateCode();
            $createdAt = time();
            $expiresAt = $createdAt + $expiresIn;

            $data = [
                'id' => 0,
                'user_id' => $userId,
                'code' => $code,
                'email' => $email,
                'created_at' => $createdAt,
                'expires_at' => $expiresAt,
                'is_used' => false
            ];

            $codeObj = new self($data);
            $codeObj->add();

            return $codeObj;
        }

        public function add(): void
        {
            try {
                $sql = "INSERT INTO `verification_codes` (`user_id`, `code`, `email`, `created_at`, `expires_at`, `is_used`) VALUES (?, ?, ?, ?, ?, ?)";
                $connection = Connection::openConnection();
                $stmt = $connection->prepare($sql);

                $userId = $this->UserId;
                $code = $this->Code;
                $email = $this->Email;
                $createdAt = $this->CreatedAt;
                $expiresAt = $this->ExpiresAt;
                $isUsed = $this->IsUsed ? 1 : 0;

                error_log("VerificationCode add() - UserId: $userId, Code: $code, Email: $email, CreatedAt: $createdAt, ExpiresAt: $expiresAt, IsUsed: $isUsed");

                $stmt->bind_param("issiii", $userId, $code, $email, $createdAt, $expiresAt, $isUsed);
                $result = $stmt->execute();

                if (!$result) {
                    error_log("Ошибка выполнения SQL: " . $stmt->error);
                }

                $this->Id = $connection->insert_id;
                error_log("VerificationCode добавлен с ID: " . $this->Id);

                $stmt->close();
                Connection::closeConnection($connection);
            } catch (Throwable $e) {
                error_log("Ошибка при добавлении кода подтверждения: " . $e->getMessage());
                error_log("Стек вызовов: " . $e->getTraceAsString());
                throw $e; 
            }
        }

        public static function findActiveByCodeAndEmail(string $code, string $email): ?self
        {
            $sql = "SELECT * FROM `verification_codes` WHERE `code` = ? AND `email` = ? AND `is_used` = 0 AND `expires_at` > ? ORDER BY `created_at` DESC LIMIT 1";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);

            $currentTime = time();
            $stmt->bind_param("ssi", $code, $email, $currentTime);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $stmt->close();
                Connection::closeConnection($connection);
                return null;
            }

            $row = $result->fetch_assoc();
            $stmt->close();
            Connection::closeConnection($connection);

            return new self($row);
        }

        public function markAsUsed(): void
        {
            $sql = "UPDATE `verification_codes` SET `is_used` = 1 WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);

            $id = $this->Id;
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $stmt->close();
            Connection::closeConnection($connection);

            $this->IsUsed = true;
        }

        public static function deleteOldCodesForEmail(string $email): void
        {
            $sql = "DELETE FROM `verification_codes` WHERE `email` = ? AND `is_used` = 0";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>
