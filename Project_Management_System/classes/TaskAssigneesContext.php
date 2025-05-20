<?php
    require_once __DIR__.'/../models/taskAssignees.php';
    require_once __DIR__.'/common/connection.php';
    
    class TaskAssigneesContext extends TaskAssignees
    {
        public function __construct(array $data)
        {
            parent::__construct(
                id: $data['id'],
                task_id: $data['task_id'],
                user_id: $data['user_id']
            );
        }
    
        public static function select(): array
        {
            $all = [];
            $sql = "SELECT * FROM `TaskAssignees`;";
            $connection = Connection::openConnection();
            $result = Connection::query($sql, $connection);

            while ($row = $result->fetch_assoc()) {
                $all[] = new TaskAssigneesContext($row);
            }
            Connection::closeConnection($connection);
            return $all;
        }
    
        public function add(): void
        {
            $sql = "INSERT INTO `TaskAssignees` (`task_id`, `user_id`) VALUES (?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $task_id = $this->Task_Id;
            $user_id = $this->User_Id;
            $stmt->bind_param("ii", $task_id, $user_id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function update(): void
        {
            $sql = "UPDATE `TaskAssignees` SET `task_id` = ?, `user_id` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $task_id = $this->Task_Id;
            $user_id = $this->User_Id;
            $id = $this->Id;
            $stmt->bind_param("iii", $task_id, $user_id, $id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function delete(): void
        {
            $sql = "DELETE FROM `TaskAssignees` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>