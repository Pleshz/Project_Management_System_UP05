<?php
    require_once __DIR__.'/../models/projectUser.php';
    require_once __DIR__.'/common/connection.php';
    
    class ProjectUserContext extends ProjectUser
    {
        public function __construct(array $data)
        {
            parent::__construct(
                $data['id'],
                $data['project_id'],
                $data['user_id'],
                $data['is_public'],
                $data['role'] instanceof Role ? $data['role'] : Role::from($data['role'])
            );
        }
    
        public static function select(): array
        {
            $all = [];
            $sql = "SELECT * FROM `projectusers`;";
            $connection = Connection::openConnection();
            $result = Connection::query($sql, $connection);
            while ($row = $result->fetch_assoc()) {
                $all[] = new ProjectUserContext($row);
            }
            Connection::closeConnection($connection);
            return $all;
        }
    
        public function add(): void
        {
            $sql = "INSERT INTO `projectusers` (`project_id`, `user_id`, `is_public`, `role`) VALUES (?, ?, ?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param(
                "iiis",
                $this->Project_Id,
                $this->User_Id,
                $this->Is_Public,
                $this->Role instanceof Role ? $this->Role->name : $this->Role
            );
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function update(): void
        {
            $sql = "UPDATE `projectusers` SET `project_id` = ?, `user_id` = ?, `is_public` = ?, `role` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param(
                "iiisi",
                $this->Project_Id,
                $this->User_Id,
                $this->Is_Public,
                $this->Role instanceof Role ? $this->Role->name : $this->Role,
                $this->Id
            );
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function delete(): void
        {
            $sql = "DELETE FROM `projectusers` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>