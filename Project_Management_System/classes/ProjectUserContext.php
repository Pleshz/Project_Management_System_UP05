<?php
    require_once __DIR__.'/../models/projectUser.php';
    require_once __DIR__.'/common/connection.php';
    
    class ProjectUserContext extends ProjectUser
    {
        public function __construct(array $data)
        {
            parent::__construct(
                id: $data['id'],
                project_id: $data['project_id'],
                user_id: $data['user_id'],
                role: $data['role'] instanceof Role
                    ? $data['role']
                    : Role::fromString($data['role'])
            );
        }
    
        public static function select(): array
        {
            $all = [];
            $sql = "SELECT * FROM `ProjectUsers`;";
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
            $sql = "INSERT INTO `ProjectUsers` (`project_id`, `user_id`, `is_public`, `role`) VALUES (?, ?, ?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $project_id = $this->Project_Id;
            $user_id = $this->User_Id;
            $role = $this->Role instanceof Role ? $this->Role->name : $this->Role;
            $stmt->bind_param("iis", $project_id, $user_id, $role);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function update(): void
        {
            $sql = "UPDATE `ProjectUsers` SET `project_id` = ?, `user_id` = ?, `is_public` = ?, `role` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $project_id = $this->Project_Id;
            $user_id = $this->User_Id;
            $role = $this->Role instanceof Role ? $this->Role->name : $this->Role;
            $id = $this->Id;
            $stmt->bind_param("iisi", $project_id, $user_id, $role, $id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function delete(): void
        {
            $sql = "DELETE FROM `ProjectUsers` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>