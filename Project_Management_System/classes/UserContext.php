<?php
    require_once __DIR__.'/../models/user.php';
    require_once __DIR__.'/common/connection.php';
    class UserContext extends User 
    {
        public function __construct(array $data)
        {
            parent::__construct(
                id: $data['id'],
                login: $data['login'],
                email: $data['email'],
                password: $data['password'],
                full_name: $data['full_name'] ?? null,
                bio: $data['bio'] ?? null,
                language: $data['language'] instanceof Language
                    ? $data['language']
                    : Language::from($data['language'])
            );
        }

        public static function select(): array
        {
            $allUsers = [];
            $sql = "SELECT * FROM `users`;";
            $connection = Connection::openConnection();
            $result = Connection::query($sql, $connection);
            
            while ($row = $result->fetch_assoc()) {
                $allUsers[] = new UserContext($row);
            }

            Connection::closeConnection($connection);
            return $allUsers;
        }

        public function add(): void
        {
            $sql = "INSERT INTO `users` (`login`, `email`, `password`, `full_name`, `bio`, `language`) VALUES (?, ?, ?, ?, ?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ssssss", $this->Login, $this->Email, $this->Password, $this->Full_Name, $this->Bio, $this->Language instanceof Language ? $this->Language->name : $this->Language);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }

        public function update(): void
        {
            $sql = "UPDATE `users` SET `login` = ?, `email` = ?, `password` = ?, `full_name` = ?, `bio` = ?, `language` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ssssssi", $this->Login, $this->Email, $this->Password, $this->Full_Name, $this->Bio, $this->Language instanceof Language ? $this->Language->name : $this->Language, $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }

        public function delete(): void
        {
            $sql = "DELETE FROM `users` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>