<?php
    require_once __DIR__.'/../models/taskAssignees.php';
    require_once __DIR__.'/common/connection.php';
    
    class TaskAssigneesContext extends TaskAssignees
    {
        public function __construct(array $data)
        {
            parent::__construct(
                $data['id'],
                $data['task_id'],
                $data['user_id']
            );
        }
    
        public static function select(): array
        {
            $all = [];
            $sql = "SELECT * FROM `taskassignees`;";
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
            $sql = "INSERT INTO `taskassignees` (`task_id`, `user_id`) VALUES (?, ?)";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("ii", $this->Task_Id, $this->User_Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function update(): void
        {
            $sql = "UPDATE `taskassignees` SET `task_id` = ?, `user_id` = ? WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("iii", $this->Task_Id, $this->User_Id, $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    
        public function delete(): void
        {
            $sql = "DELETE FROM `taskassignees` WHERE `id` = ?";
            $connection = Connection::openConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("i", $this->Id);
            $stmt->execute();
            $stmt->close();
            Connection::closeConnection($connection);
        }
    }
?>