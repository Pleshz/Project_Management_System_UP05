<?php
    class VerificationCode
    {
        public int $Id;
        public int $UserId;
        public string $Code;
        public string $Email;
        public int $CreatedAt;
        public int $ExpiresAt;
        public bool $IsUsed;

        public function __construct($id, $userId, $code, $email, $createdAt, $expiresAt, $isUsed = false)
        {
            $this->Id = $id;
            $this->UserId = $userId;
            $this->Code = $code;
            $this->Email = $email;
            $this->CreatedAt = $createdAt;
            $this->ExpiresAt = $expiresAt;
            $this->IsUsed = $isUsed;
        }

        public static function generateCode(): string
        {
            return str_pad(strval(mt_rand(0, 999999)), 6, '0', STR_PAD_LEFT);
        }

        public function isExpired(): bool
        {
            return time() > $this->ExpiresAt;
        }
    }
?>
