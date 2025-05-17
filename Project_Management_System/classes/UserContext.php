<?php
    class UserContext extends User 
    {
        public function __construct($id, $login, $email, $password, $full_name = null, $bio = null, Language $language)
        {
            parent::__construct(id: $id, login: $login, email: $email, password: $password, full_name: $full_name, bio: $bio, language: $language);
        }

        public static function select(): array
        {
            $allUsers = array();
            $sql = "SELECT * FROM `users`;";
            $connection = Connection::openConnection();
            $result = Connection::query(sql: $sql, connection: $connection);
            while ($row = $result->fetch_assoc())
            {
                $allUsers[] = new UserContext(
                    id: $row['id'],
                    login: $row['login'],
                    email: $row['email'],
                    password: $row['password'],
                    full_name: $row['full_name'],
                    bio: $row['bio'],
                    language: Language::from(value: $row['language'])
                );
            }
            Connection::closeConnection( connection: $connection);
            return $allUsers;
        }

        public function add(): void
        {
            $sql = "INSERT INTO `users` (`login`, `email`, `password`, `full_name`, `bio`, `language`) " .
                   "VALUES ('" . $this->Login . "', '" . $this->Email . "', '" . $this->Password . "', " .
                   ($this->Full_Name ? "'" . $this->Full_Name . "'" : "NULL") . ", " .
                   ($this->Bio ? "'" . $this->Bio . "'" : "NULL") . ", '" . $this->Language->name . "');";
            $connection = Connection::openConnection();
            Connection::query(sql: $sql, connection: $connection);
            Connection::closeConnection(connection: $connection);
        }

        public function update(): void
        {
            $sql = "UPDATE `users` SET " .
                   "`login` = '" . $this->Login . "', " .
                   "`email` = '" . $this->Email . "', " .
                   "`password` = '" . $this->Password . "', " .
                   "`full_name` = " . ($this->Full_Name ? "'" . $this->Full_Name . "'" : "NULL") . ", " .
                   "`bio` = " . ($this->Bio ? "'" . $this->Bio . "'" : "NULL") . ", " .
                   "`language` = '" . $this->Language->name . "' " .
                   "WHERE `id` = " . $this->Id . ";";
            $connection = Connection::openConnection();
            Connection::query(sql: $sql, connection: $connection);
            Connection::closeConnection(connection: $connection);
        }

        public function delete(): void
        {
            $sql = "DELETE FROM `users` WHERE `id` = " . $this->Id . ";";
            $connection = Connection::openConnection();
            Connection::query(sql: $sql, connection: $connection);
            Connection::closeConnection(connection: $connection);
        }
    }
?>