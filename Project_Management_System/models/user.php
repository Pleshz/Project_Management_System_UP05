<?php
    enum Language {
        case Russian;
        case English;

        public static function fromString(string $lang): self {
            return match(strtolower($lang)) {
                'ru' => self::Russian,
                'en' => self::English,
                default => throw new InvalidArgumentException("Unknown language: $lang"),
            };
        }

        public function shortCode(): string {
            return match($this) {
                self::Russian => 'ru',
                self::English => 'en',
            };
        }
    }

    class User
    {
        public int $Id;
        public string $Login;
        public string $Email;
        public string $Password;
        public Language $Language;
        public ?string $Full_Name;
        public ?string $Bio;

        public function __construct($id, $login, $email, $password, Language $language, $full_name = null, $bio = null)
        {
            $this->Id = $id;
            $this->Login = $login;
            $this->Email = $email;
            $this->Password = $password;
            $this->Language = $language;
            $this->Full_Name = $full_name;
            $this->Bio = $bio;
        }
    }
?>
