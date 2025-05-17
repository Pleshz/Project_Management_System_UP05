<?php
    enum Language {
        case Russian;
        case English;
    }
    class User 
    {
        public int $Id;
        public string $Login;
        public string $Email;
        public string $Password;
        public string $Full_Name;
        public string $Bio;
        public Language $Language;
        
        public function __construct($id, $login, $email, $password, $full_name, $bio, Language $language)
        {
            $this->Id = $id;
            $this->Login = $login;
            $this->Email = $email;
            $this->Password = $password;
            $this->Full_Name = $full_name;
            $this->Bio = $bio;
            $this->Language = $language;
        }
    }
?>