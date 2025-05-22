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
                language: $data['language'] instanceof Language
                    ? $data['language']
                    : Language::fromString($data['language']),
                full_name: $data['full_name'] ?? null,
                bio: $data['bio'] ?? null
            );
        }

        public static function select(): array
        {
            $allUsers = [];
            $sql = "SELECT * FROM `Users`;";
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
            $sql = "INSERT INTO `Users` (`login`, `email`, `password`, `full_name`, `bio`, `language`) VALUES (?, ?, ?, ?, ?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $login = $this->Login;
            $email = $this->Email;
            $password = $this->Password;
            $full_name = $this->Full_Name;
            $bio = $this->Bio;
            $language = $this->Language instanceof Language ? $this->Language->shortCode() : $this->Language;

            $stmt->bind_param("ssssss", $login, $email, $password, $full_name, $bio, $language);
            $stmt->execute();
            $this->Id = $connection->insert_id;
            $stmt->close();
            Connection::closeConnection($connection);
        }

        public function update(): void
        {
            $sql = "UPDATE `Users` SET `login` = ?, `email` = ?, `password` = ?, `full_name` = ?, `bio` = ?, `language` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $login = $this->Login;
            $email = $this->Email;
            $password = $this->Password;
            $full_name = $this->Full_Name;
            $bio = $this->Bio;
            $language = $this->Language instanceof Language ? $this->Language->shortCode() : $this->Language;
            $id = $this->Id;
            $stmt->bind_param("ssssssi", $login, $email, $password, $full_name, $bio, $language, $id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }

        public function delete(): void
        {
            $sql = "DELETE FROM `Users` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>
